<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['bus_id'])) {
    header("Location: my_fleet.php");
    exit();
}

$bus_id = mysqli_real_escape_string($conn, $_GET['bus_id']);
$owner_id = $_SESSION['user_id'];

// 1. Fetch Bus & Crew Info
$bus_res = mysqli_query($conn, "SELECT * FROM buses WHERE id = '$bus_id' AND owner_id = '$owner_id'");
$bus_data = mysqli_fetch_assoc($bus_res);

if (!$bus_data) die("Bus not found.");

// 2. Fetch Existing Route/Trips for this bus
$trips_res = mysqli_query($conn, "SELECT * FROM routes WHERE bus_id = '$bus_id' ORDER BY start_time ASC");
$existing_trips = [];
$first_trip = null;

while ($row = mysqli_fetch_assoc($trips_res)) {
    $existing_trips[] = $row;
    if (!$first_trip) $first_trip = $row; // To get route number, towns etc.
}

// 3. Crew fetching
$drivers = mysqli_query($conn, "SELECT id, full_name FROM drivers WHERE owner_id = '$owner_id' AND status = 'approved' AND (bus_id IS NULL OR bus_id = '0' OR bus_id = '$bus_id')");
$conductors = mysqli_query($conn, "SELECT id, full_name FROM conductors WHERE owner_id = '$owner_id' AND status = 'approved' AND (bus_id IS NULL OR bus_id = '0' OR bus_id = '$bus_id')");

$msg = ""; $error_msg = "";

// 4. Update Logic
if (isset($_POST['update_full_route'])) {
    $d_id = mysqli_real_escape_string($conn, $_POST['driver_id']);
    $c_id = mysqli_real_escape_string($conn, $_POST['conductor_id']);
    $route_no = mysqli_real_escape_string($conn, $_POST['route_no']);
    $start_p = mysqli_real_escape_string($conn, $_POST['start_point']);
    $end_p = mysqli_real_escape_string($conn, $_POST['end_point']);

    // Process Towns
    $town_data = [];
    if(isset($_POST['town_names'])){
        foreach($_POST['town_names'] as $idx => $name){
            if(!empty($name)) $town_data[] = ['name' => mysqli_real_escape_string($conn, $name), 'duration' => (int)$_POST['town_durations'][$idx]];
        }
    }
    $towns_json = json_encode($town_data);
    $reverse_json = json_encode(array_reverse($town_data));

    mysqli_begin_transaction($conn);
    try {
        // A. Route Pack Auto-save
        $check_pack = mysqli_query($conn, "SELECT id FROM route_packs WHERE route_number = '$route_no' LIMIT 1");
        if (mysqli_num_rows($check_pack) == 0) {
            mysqli_query($conn, "INSERT INTO route_packs (route_number, start_point, end_point, route_path_json) VALUES ('$route_no', '$start_p', '$end_p', '$towns_json')");
        }

        // B. Clear old trips for this bus
        mysqli_query($conn, "DELETE FROM routes WHERE bus_id = '$bus_id'");

        $apply_all = isset($_POST['apply_all_days']);
        $days_list = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $last_route_id = 0;

        if ($apply_all) {
            $active_days = isset($_POST['active_days']) ? implode(',', $_POST['active_days']) : implode(',', $days_list);
            if(isset($_POST['trips_all'])){
                foreach($_POST['trips_all'] as $t){
                    $path = ($t['type'] == 'forward') ? $towns_json : $reverse_json;
                    $sp = ($t['type'] == 'forward') ? $start_p : $end_p;
                    $ep = ($t['type'] == 'forward') ? $end_p : $start_p;
                    mysqli_query($conn, "INSERT INTO routes (owner_id, bus_id, route_number, start_point, end_point, start_time, destination_time, route_path_json, schedule_days) VALUES ('$owner_id', '$bus_id', '$route_no', '$sp', '$ep', '{$t['start']}', '{$t['end']}', '$path', '$active_days')");
                    $last_route_id = mysqli_insert_id($conn);
                }
            }
        } else {
            foreach ($days_list as $day) {
                if(isset($_POST['trips_day'][$day])){
                    foreach($_POST['trips_day'][$day] as $t){
                        $path = ($t['type'] == 'forward') ? $towns_json : $reverse_json;
                        $sp = ($t['type'] == 'forward') ? $start_p : $end_p;
                        $ep = ($t['type'] == 'forward') ? $end_p : $start_p;
                        mysqli_query($conn, "INSERT INTO routes (owner_id, bus_id, route_number, start_point, end_point, start_time, destination_time, route_path_json, schedule_days) VALUES ('$owner_id', '$bus_id', '$route_no', '$sp', '$ep', '{$t['start']}', '{$t['end']}', '$path', '$day')");
                        $last_route_id = mysqli_insert_id($conn);
                    }
                }
            }
        }

        // C. Crew Allocation
        mysqli_query($conn, "UPDATE drivers SET bus_id = NULL WHERE bus_id = '$bus_id'");
        mysqli_query($conn, "UPDATE conductors SET bus_id = NULL WHERE bus_id = '$bus_id'");
        mysqli_query($conn, "UPDATE drivers SET bus_id = '$bus_id' WHERE id = '$d_id'");
        mysqli_query($conn, "UPDATE conductors SET bus_id = '$bus_id' WHERE id = '$c_id'");
        mysqli_query($conn, "UPDATE buses SET route_id = '$last_route_id', driver_id = '$d_id', conductor_id = '$c_id', status = 'active' WHERE id = '$bus_id'");

        mysqli_commit($conn);
        $msg = "Fleet Schedule Updated Successfully!";
        header("Refresh:2; url=my_fleet.php");
    } catch (Exception $e) { mysqli_rollback($conn); $error_msg = $e->getMessage(); }
}

// Prepare data for UI
$towns = $first_trip ? json_decode($first_trip['route_path_json'], true) : [];
$is_manual = $first_trip && !strpos($first_trip['schedule_days'], ',');
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Edit Fleet Schedule | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { background-color: #0b0f1a; font-family: 'Outfit', sans-serif; color: #cbd5e1; }
        .glass-card { background: rgba(23, 32, 51, 0.6); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <form action="" method="POST">
            <header class="sticky top-0 z-50 glass-card p-6 rounded-[32px] mb-10 flex flex-col md:flex-row justify-between items-center gap-6 border-b border-blue-500/20">
                <div class="flex items-center gap-6">
                    <a href="my_fleet.php" class="w-12 h-12 flex items-center justify-center bg-slate-800 rounded-2xl hover:bg-blue-600 transition"><i class="fas fa-arrow-left text-white"></i></a>
                    <div>
                        <h1 class="text-2xl font-black uppercase text-white italic tracking-widest">Edit <span class="text-blue-500">Fleet</span></h1>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bus: <span class="text-blue-400"><?= $bus_data['bus_number'] ?></span></p>
                    </div>
                </div>
                <button type="submit" name="update_full_route" class="bg-emerald-600 hover:bg-emerald-500 text-white px-10 py-4 rounded-2xl font-black uppercase text-[11px] tracking-widest transition shadow-xl">Update Records</button>
            </header>

            <?php if($msg): ?>
                <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-2xl text-center font-bold text-xs uppercase tracking-widest italic">
                    <i class="fas fa-check-circle mr-2"></i> <?= $msg ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-4 space-y-6">
                    <div class="glass-card p-8 rounded-[40px]">
                        <h2 class="text-xs font-black uppercase text-blue-500 mb-6 italic tracking-widest underline underline-offset-8">1. Route Info</h2>
                        <div class="space-y-4">
                            <input type="text" name="route_no" value="<?= $first_trip['route_number'] ?? '' ?>" required placeholder="Route No" class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold border border-slate-700 outline-none">
                            <input type="text" name="start_point" value="<?= $first_trip['start_point'] ?? '' ?>" required placeholder="Point A" class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold border border-slate-700 outline-none">
                            <input type="text" name="end_point" value="<?= $first_trip['end_point'] ?? ($first_trip['start_point'] ? 'Loading...' : '') ?>" required placeholder="Point B" class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold border border-slate-700 outline-none">
                            
                            <select name="driver_id" required class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold text-slate-400 border border-slate-700 outline-none">
                                <option value="">Driver</option>
                                <?php while($d = mysqli_fetch_assoc($drivers)) echo "<option value='{$d['id']}' ".($d['id']==$bus_data['driver_id']?'selected':'').">{$d['full_name']}</option>"; ?>
                            </select>
                            
                            <select name="conductor_id" required class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold text-slate-400 border border-slate-700 outline-none">
                                <option value="">Conductor</option>
                                <?php while($c = mysqli_fetch_assoc($conductors)) echo "<option value='{$c['id']}' ".($c['id']==$bus_data['conductor_id']?'selected':'').">{$c['full_name']}</option>"; ?>
                            </select>
                        </div>
                    </div>

                    <div class="glass-card p-8 rounded-[40px]">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xs font-black uppercase text-emerald-500 italic">2. Stops</h2>
                            <button type="button" onclick="addStop()" class="text-emerald-500 text-xl"><i class="fas fa-plus-circle"></i></button>
                        </div>
                        <div id="stop-list" class="space-y-3">
                            <?php if($towns) foreach($towns as $t) echo "<script>window.addEventListener('load', () => addStop('{$t['name']}', '{$t['duration']}'));</script>"; ?>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="glass-card p-8 rounded-[40px]">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-slate-800 pb-6 gap-4">
                            <h2 class="text-xs font-black uppercase text-blue-500 italic">3. Scheduled Trips</h2>
                            <label class="flex items-center gap-3 bg-blue-500/10 px-6 py-3 rounded-2xl border border-blue-500/20 cursor-pointer">
                                <input type="checkbox" name="apply_all_days" id="toggle_mode" <?= !$is_manual ? 'checked' : '' ?> onchange="toggleUI()" class="w-5 h-5 rounded text-blue-600 bg-slate-800">
                                <span class="text-[10px] font-black uppercase text-blue-400">Quick Edit Mode (Daily)</span>
                            </label>
                        </div>

                        <div id="quick_ui" class="<?= $is_manual ? 'hidden' : '' ?>">
                            <div id="quick_container" class="space-y-4">
                                <?php if(!$is_manual) foreach($existing_trips as $index => $t): ?>
                                    <div class="flex gap-4 bg-slate-800/30 p-4 rounded-2xl items-center border border-white/5">
                                        <select name="trips_all[<?= $index ?>][type]" class="bg-slate-900 p-2 rounded-xl text-[10px] font-black uppercase text-white outline-none">
                                            <option value="forward" <?= $t['start_point']==$first_trip['start_point']?'selected':'' ?>>Forward</option>
                                            <option value="return" <?= $t['start_point']!=$first_trip['start_point']?'selected':'' ?>>Return</option>
                                        </select>
                                        <input type="time" name="trips_all[<?= $index ?>][start]" value="<?= $t['start_time'] ?>" class="bg-slate-900 p-2 rounded-xl text-xs text-blue-400 font-bold">
                                        <input type="time" name="trips_all[<?= $index ?>][end]" value="<?= $t['destination_time'] ?>" class="bg-slate-900 p-2 rounded-xl text-xs text-emerald-400 font-bold">
                                        <button type="button" onclick="this.parentElement.remove()" class="text-slate-600"><i class="fas fa-trash"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" onclick="addTripRow('quick')" class="w-full mt-6 py-4 border-2 border-dashed border-slate-800 rounded-3xl text-[10px] font-black uppercase text-slate-500 hover:border-blue-500 transition">+ Add Trip Row</button>
                        </div>

                        <div id="manual_ui" class="<?= !$is_manual ? 'hidden' : '' ?> space-y-6">
                            <?php foreach($days_list as $day): ?>
                                <div class="bg-slate-900/30 p-6 rounded-[32px] border border-slate-800">
                                    <h4 class="text-[10px] font-black text-blue-500 uppercase mb-4"><?= $day ?></h4>
                                    <div id="manual_container_<?= $day ?>" class="space-y-3">
                                        <?php 
                                        if($is_manual) {
                                            foreach($existing_trips as $index => $t) {
                                                if($t['schedule_days'] == $day) {
                                                    echo "<div class='flex gap-2 bg-slate-800/50 p-2 rounded-xl items-center'>
                                                        <input type='time' name='trips_day[$day][$index][start]' value='{$t['start_time']}' class='bg-slate-900 p-1 rounded text-xs text-blue-400'>
                                                        <input type='time' name='trips_day[$day][$index][end]' value='{$t['destination_time']}' class='bg-slate-900 p-1 rounded text-xs text-emerald-400'>
                                                        <button type='button' onclick='this.parentElement.remove()' class='text-red-900 text-xs'><i class='fas fa-times'></i></button>
                                                    </div>";
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                    <button type="button" onclick="addTripRow('<?= $day ?>')" class="mt-4 text-[9px] font-bold text-slate-600 hover:text-white">+ Add Specific Trip</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let tripIdx = 100; // Start high to avoid overlap with existing
        function toggleUI() {
            const isQuick = document.getElementById('toggle_mode').checked;
            document.getElementById('quick_ui').classList.toggle('hidden', !isQuick);
            document.getElementById('manual_ui').classList.toggle('hidden', isQuick);
        }

        function addTripRow(target) {
            const container = (target === 'quick') ? document.getElementById('quick_container') : document.getElementById(`manual_container_${target}`);
            const fieldName = (target === 'quick') ? `trips_all[${tripIdx}]` : `trips_day[${target}][${tripIdx}]`;
            
            const div = document.createElement('div');
            div.className = "flex gap-4 bg-slate-800/30 p-4 rounded-2xl items-center border border-white/5";
            div.innerHTML = `
                <select name="${fieldName}[type]" class="bg-slate-900 p-2 rounded-xl text-[10px] font-black uppercase text-white outline-none">
                    <option value="forward">Forward</option><option value="return">Return</option>
                </select>
                <input type="time" name="${fieldName}[start]" value="06:00" class="bg-slate-900 p-2 rounded-xl text-xs text-blue-400 font-bold">
                <input type="time" name="${fieldName}[end]" value="08:00" class="bg-slate-900 p-2 rounded-xl text-xs text-emerald-400 font-bold">
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-600 hover:text-red-500"><i class="fas fa-trash"></i></button>
            `;
            container.appendChild(div);
            tripIdx++;
        }

        function addStop(name = '', mins = '') {
            const div = document.createElement('div');
            div.className = "flex items-center gap-2 bg-slate-800/40 p-3 rounded-xl border border-white/5";
            div.innerHTML = `
                <input type="text" name="town_names[]" value="${name}" placeholder="Town" class="flex-1 bg-transparent text-xs font-bold outline-none text-white">
                <input type="number" name="town_durations[]" value="${mins}" placeholder="Min" class="w-14 bg-slate-900 p-2 rounded-lg text-center text-xs text-emerald-500 font-black outline-none border-none">
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-500"><i class="fas fa-times"></i></button>
            `;
            document.getElementById('stop-list').appendChild(div);
        }
    </script>
</body>
</html>