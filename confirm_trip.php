<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['bus_id'])) {
    header("Location: tripbook.php");
    exit();
}

$bus_id = mysqli_real_escape_string($conn, $_GET['bus_id']);

// බස් එකේ සහ අයිතිකරුගේ මූලික විස්තර ගනිමු
$sql = "SELECT b.*, o.company_name 
        FROM buses b 
        JOIN bus_owners o ON b.owner_id = o.id 
        WHERE b.id = '$bus_id'";
$result = mysqli_query($conn, $sql);
$bus = mysqli_fetch_assoc($result);

if (!$bus) { die("Invalid Vehicle Selection."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap');
        body { background: #020617; color: #f8fafc; font-family: 'Outfit', sans-serif; }
        .glass-input { 
            background: rgba(30, 41, 59, 0.3); 
            border: 1px solid rgba(255,255,255,0.1); 
            border-radius: 15px;
            padding: 12px 15px;
            color: white;
            width: 100%;
            transition: 0.3s;
        }
        .glass-input:focus { border-color: #3b82f6; outline: none; background: rgba(30, 41, 59, 0.5); }
        .section-card { background: rgba(30, 41, 59, 0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 35px; padding: 30px; }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-4xl mx-auto">
        <div class="mb-10 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black italic uppercase tracking-tighter">Plan Your <span class="text-blue-500">Journey</span></h1>
                <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest mt-1">Booking for: <?= $bus['bus_number'] ?> (<?= $bus['company_name'] ?>)</p>
            </div>
            <a href="bus_details.php?bus_id=<?= $bus_id ?>" class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center hover:bg-slate-700 transition">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <form action="process_booking.php" method="POST" class="space-y-8">
            <input type="hidden" name="bus_id" value="<?= $bus_id ?>">

            <div class="section-card">
                <h3 class="text-xs font-black uppercase text-blue-500 mb-6 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i> Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Trip Type</label>
                        <select name="trip_type" class="glass-input">
                            <option value="Family">Family Trip</option>
                            <option value="Friends">Friends Outing</option>
                            <option value="School/University">School / University</option>
                            <option value="Office/Work">Office / Work</option>
                            <option value="Wedding">Wedding / Function</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Expected Passenger Count</label>
                        <input type="number" name="passenger_count" max="<?= $bus['number_of_seats'] ?>" placeholder="Max: <?= $bus['number_of_seats'] ?>" class="glass-input" required>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h3 class="text-xs font-black uppercase text-amber-500 mb-6 flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i> Schedule
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Start Date</label>
                        <input type="date" name="start_date" class="glass-input" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">End Date</label>
                        <input type="date" name="end_date" class="glass-input" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Start Time</label>
                        <input type="time" name="start_time" class="glass-input" required>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h3 class="text-xs font-black uppercase text-emerald-500 mb-6 flex items-center">
                    <i class="fas fa-map-marked-alt mr-2"></i> Route & Destinations
                </h3>
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Pick-up Location</label>
                            <input type="text" name="pickup_location" placeholder="Where should the bus start?" class="glass-input" required>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Final Destination</label>
                            <input type="text" name="final_destination" placeholder="Main destination" class="glass-input" required>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Places to Visit (Atharamaga yanna oni than)</label>
                        <textarea name="visit_places" rows="3" placeholder="List the places you want to visit during the trip..." class="glass-input"></textarea>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h3 class="text-xs font-black uppercase text-purple-500 mb-6 flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i> Extra Requirements
                </h3>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase mb-2 block">Special Requests / Facilities</label>
                    <textarea name="extra_facilities" rows="2" placeholder="e.g. Need more space for luggage, Cooler box required, etc." class="glass-input"></textarea>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-4 pt-6">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white py-5 rounded-3xl font-black uppercase text-xs tracking-[0.2em] transition-all shadow-xl shadow-blue-900/20">
                    Send Hire Request
                </button>
                <button type="reset" class="px-10 py-5 rounded-3xl border border-white/10 text-[10px] font-black uppercase tracking-widest hover:bg-white/5 transition">
                    Clear Form
                </button>
            </div>
        </form>

        <p class="text-center text-[9px] text-slate-600 font-black uppercase tracking-widest mt-12 pb-10">
            DriveZone • Your safety is our priority
        </p>
    </div>

</body>
</html>