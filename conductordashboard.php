<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'conductor') {
    header("Location: login.php");
    exit();
}

$conductor_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$msg = "";

// --- 1. Trip Control Logic ---
if (isset($_POST['start_trip'])) {
    $_SESSION['active_trip_route'] = $_POST['route_id'];
    $_SESSION['trip_started'] = true;
}

if (isset($_POST['end_trip'])) {
    unset($_SESSION['active_trip_route']);
    unset($_SESSION['trip_started']);
}

// --- 2. Verification Logic ---
if (isset($_POST['verify_booking'])) {
    $input_ref = mysqli_real_escape_string($conn, $_POST['ref_input']);
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);

    $check_q = mysqli_query($conn, "SELECT id FROM bookings WHERE id = '$booking_id' AND booking_ref = '$input_ref'");
    if ($check_q && mysqli_num_rows($check_q) > 0) {
        mysqli_query($conn, "UPDATE bookings SET is_verified = 1 WHERE id = '$booking_id'");
        $msg = "Success: Passenger Verified!";
    } else {
        $msg = "Error: Invalid Reference Code!";
    }
}

// 3. Conductor ගේ බස් එකේ සහ රූට් වල විස්තර ගැනීම
$bus_q = mysqli_query($conn, "SELECT id, bus_number FROM buses WHERE conductor_id = '$conductor_id' LIMIT 1");
$bus_data = mysqli_fetch_assoc($bus_q);
$bus_id = $bus_data['id'] ?? 0;

$routes_q = mysqli_query($conn, "SELECT id, route_number, start_point, end_point FROM routes");
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conductor Terminal | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0b0f1a; color: #cbd5e1; font-family: 'Inter', sans-serif; }
        .glass-card { background: rgba(23, 32, 51, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .trip-active { border: 2px solid #3b82f6; box-shadow: 0 0 20px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="p-4 pb-32">

    <div class="max-w-md mx-auto">
        <header class="flex justify-between items-center mb-8 bg-slate-900/50 p-4 rounded-3xl border border-slate-800">
            <div>
                <h1 class="text-xl font-black text-white italic">DRIVE<span class="text-blue-500">ZONE</span></h1>
                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Conductor Mode</p>
            </div>
            <div class="text-right">
                <span class="block text-xs font-black text-blue-500"><?= $bus_data['bus_number'] ?? 'N/A' ?></span>
                <span class="text-[8px] text-slate-500 font-bold uppercase"><?= date('H:i') ?> | <?= $today ?></span>
            </div>
        </header>

        <?php if (!isset($_SESSION['trip_started'])): ?>
            <div class="glass-card p-8 rounded-[40px] text-center border-dashed border-2 border-slate-800">
                <div class="w-20 h-20 bg-blue-600/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-blue-500/20">
                    <i class="fas fa-play text-2xl text-blue-500 ml-1"></i>
                </div>
                <h2 class="text-lg font-black text-white uppercase mb-2 tracking-tight">Ready for Duty?</h2>
                <p class="text-xs text-slate-500 mb-8 font-medium">Please select your route to start seeing passenger bookings for the current trip.</p>
                
                <form method="POST" class="space-y-4">
                    <select name="route_id" required class="w-full bg-slate-900 border border-slate-800 rounded-2xl p-4 text-xs font-bold text-white outline-none focus:border-blue-500">
                        <option value="">Choose Route</option>
                        <?php while($r = mysqli_fetch_assoc($routes_q)): ?>
                            <option value="<?= $r['id'] ?>"><?= $r['route_number'] ?> | <?= $r['start_point'] ?> - <?= $r['end_point'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" name="start_trip" class="w-full bg-blue-600 hover:bg-blue-500 text-white p-4 rounded-2xl font-black uppercase text-xs shadow-lg shadow-blue-600/20 transition active:scale-95">
                        Start Trip Now
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="glass-card p-4 rounded-[30px] mb-6 flex items-center justify-between trip-active">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-500/20 rounded-full flex items-center justify-center text-emerald-500 animate-pulse">
                        <i class="fas fa-bus text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Trip is Active</p>
                        <h3 class="text-[10px] font-bold text-white">Route ID: #<?= $_SESSION['active_trip_route'] ?></h3>
                    </div>
                </div>
                <form method="POST">
                    <button type="submit" name="end_trip" class="bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-2 rounded-xl text-[9px] font-black uppercase hover:bg-red-500 hover:text-white transition">End Trip</button>
                </form>
            </div>

            <?php if($msg): ?>
                <div class="mb-6 p-4 rounded-2xl text-xs font-bold text-center border <?= strpos($msg, 'Success') !== false ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-red-500/10 text-red-400 border-red-500/30' ?>">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <?php
                // Filter bookings for the bus and today
                $route_id = $_SESSION['active_trip_route'];
                $bookings_res = mysqli_query($conn, "SELECT b.*, p.full_name FROM bookings b JOIN passengers p ON b.passenger_id = p.id WHERE b.bus_id = '$bus_id' AND b.travel_date = '$today' AND b.status = 'booked' ORDER BY b.seat_number ASC");
                
                if (mysqli_num_rows($bookings_res) > 0): 
                    while($row = mysqli_fetch_assoc($bookings_res)): ?>
                        <div class="glass-card p-5 rounded-[30px] border border-slate-800 shadow-xl">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-slate-900 rounded-2xl flex flex-col items-center justify-center border border-slate-700">
                                        <span class="text-[7px] text-slate-500 font-black uppercase">Seat</span>
                                        <span class="text-lg font-black text-blue-500 leading-none"><?= $row['seat_number'] ?></span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-white uppercase italic"><?= htmlspecialchars($row['full_name']) ?></h4>
                                        <p class="text-[9px] text-slate-500 font-bold uppercase">REF: #<?= $row['booking_ref'] ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <?php if($row['is_verified']): ?>
                                        <span class="text-emerald-500 text-lg"><i class="fas fa-check-circle"></i></span>
                                    <?php else: ?>
                                        <span class="text-slate-700 text-lg"><i class="fas fa-clock"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if(!$row['is_verified']): ?>
                                <form method="POST" class="flex gap-2">
                                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="ref_input" placeholder="Referral Code" required class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs font-bold text-white outline-none focus:border-blue-500 transition-all">
                                    <button type="submit" name="verify_booking" class="bg-blue-600 text-white px-5 rounded-xl text-[10px] font-black uppercase transition active:scale-95">Verify</button>
                                </form>
                            <?php else: ?>
                                <div class="bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-2xl flex items-center justify-center gap-2">
                                    <span class="text-[9px] font-black text-emerald-400 uppercase italic tracking-widest italic">Boarded</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; 
                else: ?>
                    <div class="py-20 text-center opacity-30 italic text-xs">No bookings found for this trip.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <nav class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[90%] max-w-xs bg-slate-900/90 backdrop-blur-2xl border border-slate-800 p-2 rounded-[30px] flex justify-around shadow-2xl items-center">
            <a href="conductordashboard.php" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/40"><i class="fas fa-home"></i></a>
            <a href="logout.php" class="w-12 h-12 flex items-center justify-center rounded-2xl text-red-500/50"><i class="fas fa-power-off"></i></a>
        </nav>
    </div>

</body>
</html>