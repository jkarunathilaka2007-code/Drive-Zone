<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

// 1. නගර ලැයිස්තුව ලබා ගැනීම
$all_towns = [];
$town_q = mysqli_query($conn, "SELECT start_point, end_point, route_path_json FROM routes");
while ($r = mysqli_fetch_assoc($town_q)) {
    $all_towns[] = trim($r['start_point']);
    $all_towns[] = trim($r['end_point']);
    $path = json_decode($r['route_path_json'], true);
    if ($path) foreach ($path as $p) $all_towns[] = trim($p['name']);
}
$all_towns = array_unique(array_filter($all_towns));
sort($all_towns);

$from = $_POST['from_town'] ?? '';
$to = $_POST['to_town'] ?? '';
$travel_date = $_POST['travel_date'] ?? date('Y-m-d');
$is_searching = isset($_POST['search_bus']) && !empty($from) && !empty($to);

$day_of_week = date('l', strtotime($travel_date));
$bus_groups = [];

// 2. නව SQL Query එක (route_number එක පාවිච්චි කර JOIN කර ඇත)
$sql = "SELECT r.*, b.bus_number, b.bus_type, b.img_front, bo.company_name, bo.company_logo,
               rp.start_point AS pack_start, rp.end_point AS pack_end
        FROM routes r
        INNER JOIN buses b ON r.bus_id = b.id
        LEFT JOIN bus_owners bo ON b.owner_id = bo.id
        LEFT JOIN route_packs rp ON r.route_number = rp.route_number
        WHERE b.status = 'active'";

$res = mysqli_query($conn, $sql);

if (!$res) {
    die("Database Error: " . mysqli_error($conn));
}

while ($trip = mysqli_fetch_assoc($res)) {
    $b_id = $trip['bus_id'];
    $r_id = $trip['id'];

    if ($is_searching) {
        $active_days = array_map('trim', explode(',', $trip['schedule_days']));
        if (!in_array($day_of_week, $active_days)) continue;

        $stops = [['name' => strtolower(trim($trip['start_point'])), 'dur' => 0]];
        $mid = json_decode($trip['route_path_json'], true) ?: [];
        foreach($mid as $m) $stops[] = ['name' => strtolower(trim($m['name'])), 'dur' => (int)$m['duration']];
        $stops[] = ['name' => strtolower(trim($trip['end_point'])), 'dur' => 0];

        $found_f = false; $found_t = false;
        $b_off = 0; $a_off = 0; $t_time = 0;

        foreach ($stops as $s) {
            $t_time += $s['dur'];
            if ($s['name'] == strtolower(trim($from))) { $found_f = true; $b_off = $t_time; }
            if ($s['name'] == strtolower(trim($to)) && $found_f) { $found_t = true; $a_off = $t_time; break; }
        }

        if ($found_f && $found_t) {
            $clean_from = mysqli_real_escape_string($conn, trim($from));
            $clean_to = mysqli_real_escape_string($conn, trim($to));

            $p_query = "SELECT tp.price FROM ticket_prices tp
                        JOIN routes rt ON tp.route_id = rt.id
                        WHERE rt.bus_id = '$b_id' 
                        AND (
                            (LOWER(tp.from_town) = LOWER('$clean_from') AND LOWER(tp.to_town) = LOWER('$clean_to'))
                            OR (LOWER(tp.from_town) = LOWER('$clean_to') AND LOWER(tp.to_town) = LOWER('$clean_from'))
                        )
                        ORDER BY (tp.route_id = '$r_id') DESC LIMIT 1";

            $p_res = mysqli_query($conn, $p_query);
            $p_row = mysqli_fetch_assoc($p_res);
            $fare = $p_row ? $p_row['price'] : 0;

            if (!isset($bus_groups[$b_id])) $bus_groups[$b_id] = ['info' => $trip, 'trips' => []];
            $bus_groups[$b_id]['trips'][] = [
                'route_id' => $r_id,
                'boarding' => date('h:i A', strtotime($trip['start_time'] . " + $b_off minutes")),
                'arrival' => date('h:i A', strtotime($trip['start_time'] . " + $a_off minutes")),
                'fare' => $fare
            ];
        }
    } else {
        if (!isset($bus_groups[$b_id])) $bus_groups[$b_id] = ['info' => $trip, 'trips' => []];
        $bus_groups[$b_id]['trips'][] = [
            'route_id' => $r_id,
            'boarding' => date('h:i A', strtotime($trip['start_time'])),
            'arrival' => date('h:i A', strtotime($trip['destination_time'])),
            'fare' => 'Search for Price'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DriveZone | Ticket Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        body { background: #080b14; font-family: 'Plus Jakarta Sans', sans-serif; color: #cbd5e1; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .select2-container--default .select2-selection--single { background: #111827 !important; border: 1px solid #1e293b !important; height: 50px !important; border-radius: 12px !important; display: flex; align-items: center; }
        .select2-selection__rendered { color: white !important; padding-left: 15px !important; }
        .select2-dropdown { background: #111827 !important; border: 1px solid #1e293b !important; color: white !important; }
        input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); opacity: 0.5; }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-6xl mx-auto">
        <div class="glass p-8 rounded-[35px] shadow-2xl mb-12">
            <h2 class="text-xl font-extrabold text-white mb-6 italic uppercase tracking-tighter">Reserve <span class="text-blue-500">Your Seat</span></h2>
            <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <select name="from_town" class="js-select w-full" required>
                    <option value="">Origin</option>
                    <?php foreach($all_towns as $t) echo "<option value='$t' ".($from==$t?'selected':'').">$t</option>"; ?>
                </select>
                <select name="to_town" class="js-select w-full" required>
                    <option value="">Destination</option>
                    <?php foreach($all_towns as $t) echo "<option value='$t' ".($to==$t?'selected':'').">$t</option>"; ?>
                </select>
                <input type="date" name="travel_date" value="<?= $travel_date ?>" class="w-full bg-[#111827] border border-[#1e293b] text-white p-3 rounded-xl font-bold h-[50px] outline-none">
                <button type="submit" name="search_bus" class="bg-blue-600 hover:bg-blue-500 text-white font-black rounded-xl transition-all shadow-lg uppercase text-[10px] tracking-widest">Search</button>
            </form>
        </div>

        <div class="space-y-6">
            <?php if (!empty($bus_groups)): ?>
                <?php foreach ($bus_groups as $bus_id => $data): $info = $data['info']; ?>
                <div class="glass p-6 rounded-[35px] flex flex-col lg:flex-row items-center gap-8 border-l-4 border-blue-600">
                    <div class="w-full lg:w-52 h-36 rounded-2xl overflow-hidden shadow-2xl">
                        <img src="<?= $info['img_front'] ?>" class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1 w-full">
                        <div class="flex items-center gap-4 mb-3">
                            <img src="<?= $info['company_logo'] ?>" class="w-10 h-10 rounded-lg bg-white p-1">
                            <div>
                                <h4 class="text-xl font-black text-white italic leading-none uppercase"><?= $info['company_name'] ?></h4>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="text-[9px] bg-blue-600 text-white px-2 py-0.5 rounded font-bold uppercase tracking-widest">Route <?= $info['route_number'] ?></span>
                                    <?php if(!empty($info['pack_start'])): ?>
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter"><?= $info['pack_start'] ?> — <?= $info['pack_end'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <p class="text-[10px] font-bold text-blue-500 tracking-widest uppercase mb-4"><?= $info['bus_number'] ?> • <?= $info['bus_type'] ?></p>

                        <div class="bg-slate-900/60 p-4 rounded-2xl border border-white/5">
                            <label class="text-[9px] font-black text-slate-500 uppercase block mb-1 tracking-tighter">Available Times</label>
                            <select id="time_<?= $bus_id ?>" class="w-full bg-transparent text-white font-bold outline-none border-none cursor-pointer">
                                <option value="" class="bg-[#080b14]">-- Choose Departure --</option>
                                <?php foreach ($data['trips'] as $trip): ?>
                                    <option value="<?= $trip['boarding'] ?>" 
                                            data-route="<?= $trip['route_id'] ?>" 
                                            data-fare="<?= $trip['fare'] ?>"
                                            data-arrival="<?= $trip['arrival'] ?>"
                                            class="bg-[#080b14]">
                                        <?= $trip['boarding'] ?> (Arrival: <?= $trip['arrival'] ?>) — <?= is_numeric($trip['fare']) ? 'Rs. '.number_format($trip['fare'], 2) : $trip['fare'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="w-full lg:w-44">
                        <button onclick="handleBooking(<?= $bus_id ?>, '<?= $from ?>', '<?= $to ?>', '<?= $travel_date ?>', <?= $is_searching ? 1 : 0 ?>)" 
                                class="w-full bg-white text-black font-black py-5 rounded-2xl hover:bg-blue-600 hover:text-white transition-all uppercase text-[10px] tracking-widest">
                            Select Seats
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-20 opacity-20 italic">No buses found for this route.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        $(document).ready(function() { $('.js-select').select2(); });
        function handleBooking(bid, f, t, d, isS) {
            if (isS === 0) { Swal.fire({ icon: 'info', title: 'Search First', text: 'Please select origin and destination first.', background: '#111827', color: '#fff' }); return; }
            const sel = document.getElementById('time_' + bid);
            const opt = sel.options[sel.selectedIndex];
            if (!opt.value) { Swal.fire({ icon: 'warning', title: 'Pick Time', text: 'Please select a departure time.', background: '#111827', color: '#fff' }); return; }
            window.location.href = `book2.php?bus_id=${bid}&from=${encodeURIComponent(f)}&to=${encodeURIComponent(t)}&date=${d}&fare=${opt.getAttribute('data-fare')}&route_id=${opt.getAttribute('data-route')}&boarding_time=${opt.value}&arrival_time=${opt.getAttribute('data-arrival')}`;
        }
    </script>
</body>
</html>