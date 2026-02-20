<?php
session_start();
require_once 'db_config.php';

// මගියා ලොග් වී ඇත්දැයි බැලීම
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Form එක Submit කළ විට
if (isset($_POST['submit_request'])) {
    $bus_id = mysqli_real_escape_string($conn, $_POST['bus_id']);
    $pickup = mysqli_real_escape_string($conn, $_POST['pickup_location']);
    $destination = mysqli_real_escape_string($conn, $_POST['final_destination']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $passengers = mysqli_real_escape_string($conn, $_POST['passenger_count']);
    $visit_places = mysqli_real_escape_string($conn, $_POST['visit_places']);
    $trip_type = mysqli_real_escape_string($conn, $_POST['trip_type']);
    $extra = mysqli_real_escape_string($conn, $_POST['extra_facilities']);

    $sql = "INSERT INTO trip_bookings (passenger_id, bus_id, pickup_location, final_destination, start_date, end_date, start_time, passenger_count, visit_places, trip_type, extra_facilities, status) 
            VALUES ('$passenger_id', '$bus_id', '$pickup', '$destination', '$start_date', '$end_date', '$start_time', '$passengers', '$visit_places', '$trip_type', '$extra', 'pending')";

    if (mysqli_query($conn, $sql)) {
        $success_msg = "Your hire request has been sent successfully!";
    } else {
        $error_msg = "Error: " . mysqli_error($conn);
    }
}

// පද්ධතියේ ඇති බස් රථ ලැයිස්තුව ලබා ගැනීම
$bus_query = "SELECT id, bus_number, brand, model FROM buses WHERE status = 'active'";
$bus_result = mysqli_query($conn, $bus_query);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Special Trip | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap');
        body { background-color: #0f172a; font-family: 'Outfit', sans-serif; }
        .form-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
        input, select, textarea { background: #1e293b !important; border: 1px solid #334155 !important; color: white !important; }
        input:focus { border-color: #3b82f6 !important; ring: 2px #3b82f6; }
    </style>
</head>
<body class="text-slate-200 p-4 md:p-10">

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-4xl font-black italic tracking-tighter uppercase">Request a <span class="text-blue-500">Hire</span></h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.3em]">Special Journey Planning</p>
            </div>
            <a href="passengersdashboard.php" class="bg-slate-800 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition">
                <i class="fas fa-times mr-2"></i> Cancel
            </a>
        </div>

        <?php if($success_msg): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-6 rounded-[30px] mb-8 flex items-center gap-4 animate-bounce">
                <i class="fas fa-check-circle text-2xl"></i>
                <p class="font-bold uppercase text-xs tracking-widest"><?= $success_msg ?></p>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="form-card p-8 md:p-12 rounded-[50px] shadow-2xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Select Your Preferred Bus</label>
                    <select name="bus_id" required class="w-full p-4 rounded-2xl outline-none transition">
                        <option value="">-- Choose a Bus --</option>
                        <?php while($bus = mysqli_fetch_assoc($bus_result)): ?>
                            <option value="<?= $bus['id'] ?>"><?= $bus['bus_number'] ?> - <?= $bus['brand'] ?> <?= $bus['model'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Pickup Location</label>
                    <input type="text" name="pickup_location" required placeholder="e.g. Kandy Clock Tower" class="w-full p-4 rounded-2xl outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Final Destination</label>
                    <input type="text" name="final_destination" required placeholder="e.g. Kataragama" class="w-full p-4 rounded-2xl outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Start Date</label>
                    <input type="date" name="start_date" required class="w-full p-4 rounded-2xl outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Return Date</label>
                    <input type="date" name="end_date" required class="w-full p-4 rounded-2xl outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Pickup Time</label>
                    <input type="time" name="start_time" required class="w-full p-4 rounded-2xl outline-none">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Passenger Count</label>
                    <input type="number" name="passenger_count" required placeholder="Number of persons" class="w-full p-4 rounded-2xl outline-none">
                </div>

                <div class="md:col-span-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Trip Type</label>
                    <div class="flex gap-4">
                        <label class="flex-1 bg-slate-800 p-4 rounded-2xl border border-white/5 cursor-pointer hover:border-blue-500 transition text-center">
                            <input type="radio" name="trip_type" value="One-way" class="hidden" checked>
                            <span class="text-xs font-bold uppercase tracking-widest">One-way</span>
                        </label>
                        <label class="flex-1 bg-slate-800 p-4 rounded-2xl border border-white/5 cursor-pointer hover:border-blue-500 transition text-center">
                            <input type="radio" name="trip_type" value="Round-trip" class="hidden">
                            <span class="text-xs font-bold uppercase tracking-widest">Round-trip</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Itinerary / Visit Places (Optional)</label>
                    <textarea name="visit_places" rows="3" placeholder="List the places you plan to visit..." class="w-full p-4 rounded-2xl outline-none"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 block tracking-widest">Extra Requirements (A/C, Music, etc.)</label>
                    <input type="text" name="extra_facilities" placeholder="Any special requests?" class="w-full p-4 rounded-2xl outline-none">
                </div>

            </div>

            <button type="submit" name="submit_request" class="w-full mt-10 bg-blue-600 hover:bg-blue-500 py-5 rounded-[25px] text-white font-black uppercase tracking-[0.3em] transition shadow-xl shadow-blue-900/40">
                Send Hire Request <i class="fas fa-paper-plane ml-2"></i>
            </button>
        </form>
    </div>

    <footer class="mt-20 text-center opacity-30">
        <p class="text-[9px] font-black uppercase tracking-widest">&copy; 2026 DriveZone Fleet Management</p>
    </footer>

</body>
</html>