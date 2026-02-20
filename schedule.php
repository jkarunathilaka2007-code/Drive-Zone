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

// 1. Fetch Bus Info
$bus_res = mysqli_query($conn, "SELECT bus_number FROM buses WHERE id = '$bus_id'");
$bus_data = mysqli_fetch_assoc($bus_res);

// 2. Fetch All Trips for this bus
// කාලය අනුව පිළිවෙලට ගෙන එයි
$trips_res = mysqli_query($conn, "SELECT * FROM routes WHERE bus_id = '$bus_id' ORDER BY start_time ASC");
$all_trips = [];
$route_no = "";

while ($row = mysqli_fetch_assoc($trips_res)) {
    $all_trips[] = $row;
    $route_no = $row['route_number']; // Get route number from records
}

if (empty($all_trips)) {
    die("<div class='h-screen bg-[#0b0f1a] flex items-center justify-center text-white font-black uppercase tracking-widest'>No schedule deployed for this bus yet.</div>");
}

$days_list = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$current_day = date('l'); 
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline | <?= $bus_data['bus_number'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { background-color: #0b0f1a; color: #f8fafc; font-family: 'Outfit', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .day-content { display: none; }
        .day-content.active { display: block; animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .trip-card:hover { border-color: rgba(59, 130, 246, 0.5); transform: translateX(5px); }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-5xl mx-auto">
        <header class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6 glass p-8 rounded-[40px] border-b-4 border-blue-600">
            <div class="flex items-center gap-6">
                <a href="my_fleet.php" class="w-14 h-14 flex items-center justify-center bg-slate-800 rounded-2xl hover:bg-blue-600 transition shadow-2xl">
                    <i class="fas fa-chevron-left text-white"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black uppercase italic tracking-tighter">Live <span class="text-blue-500 text-shadow-glow">Timeline</span></h1>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-1">
                        <i class="fas fa-bus text-blue-500 mr-1"></i> <?= $bus_data['bus_number'] ?> • Route <?= $route_no ?>
                    </p>
                </div>
            </div>
            <div class="hidden md:block text-right">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Local Time</p>
                <p class="text-xl font-black text-white italic"><?= date('h:i A') ?></p>
            </div>
        </header>

        <div class="flex overflow-x-auto gap-3 mb-12 no-scrollbar pb-4">
            <?php foreach($days_list as $day): 
                // පරීක්ෂා කරනවා මේ දවසේ ගමන් වාර තියෙනවද කියලා
                $has_trips = false;
                foreach($all_trips as $t) {
                    if (strpos($t['schedule_days'], $day) !== false) { $has_trips = true; break; }
                }
            ?>
                <button onclick="showDay('<?= $day ?>')" id="btn-<?= $day ?>" 
                    class="day-btn shrink-0 px-10 py-5 rounded-[24px] font-black text-[11px] uppercase tracking-[0.2em] transition-all duration-300 border <?= $has_trips ? 'border-slate-800 text-slate-300' : 'border-red-900/10 text-slate-700 opacity-40 cursor-not-allowed' ?>">
                    <?= $day ?>
                    <?php if($has_trips): ?>
                        <span class="block w-6 h-1 bg-blue-500 rounded-full mx-auto mt-2 shadow-[0_0_10px_rgba(59,130,246,0.8)]"></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div id="schedule-display">
            <?php foreach($days_list as $day): ?>
                <div id="content-<?= $day ?>" class="day-content">
                    <div class="space-y-10">
                        <?php 
                        $found_any = false;
                        foreach($all_trips as $trip): 
                            if (strpos($trip['schedule_days'], $day) === false) continue;
                            $found_any = true;
                            $towns = json_decode($trip['route_path_json'], true) ?: [];
                            $is_forward = ($trip['start_point'] == $all_trips[0]['start_point']);
                        ?>
                            <div class="glass rounded-[45px] overflow-hidden border-l-8 <?= $is_forward ? 'border-blue-600' : 'border-emerald-600' ?> shadow-2xl trip-card transition-all">
                                <div class="p-6 md:p-8 bg-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center <?= $is_forward ? 'bg-blue-600/20 text-blue-500' : 'bg-emerald-600/20 text-emerald-500' ?>">
                                            <i class="fas <?= $is_forward ? 'fa-arrow-up' : 'fa-arrow-down' ?> text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-black uppercase text-sm tracking-widest text-white italic">
                                                <?= $is_forward ? 'Forward Trip' : 'Return Trip' ?>
                                            </h3>
                                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter"><?= $trip['start_point'] ?> <i class="fas fa-long-arrow-alt-right mx-2 opacity-30"></i> <?= $trip['end_point'] ?></p>
                                        </div>
                                    </div>
                                    <div class="flex gap-4">
                                        <div class="text-center bg-slate-900/80 px-6 py-3 rounded-2xl border border-white/5">
                                            <p class="text-[8px] font-black text-slate-500 uppercase mb-1">Departure</p>
                                            <p class="text-sm font-black text-blue-400"><?= date('h:i A', strtotime($trip['start_time'])) ?></p>
                                        </div>
                                        <div class="text-center bg-slate-900/80 px-6 py-3 rounded-2xl border border-white/5">
                                            <p class="text-[8px] font-black text-slate-500 uppercase mb-1">Arrival</p>
                                            <p class="text-sm font-black text-emerald-400"><?= date('h:i A', strtotime($trip['destination_time'])) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-8 md:p-12 relative">
                                    <div class="absolute left-10 md:left-14 top-12 bottom-12 w-1 bg-gradient-to-b from-blue-600/50 via-emerald-600/50 to-transparent rounded-full opacity-20"></div>
                                    
                                    <div class="space-y-12">
                                        <div class="relative flex items-center gap-10">
                                            <div class="z-10 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-blue-600/20 shadow-[0_0_15px_rgba(59,130,246,0.8)]"></div>
                                            <div class="flex-1 flex justify-between items-center border-b border-white/5 pb-2">
                                                <span class="text-xs font-black uppercase text-white tracking-widest italic"><?= $trip['start_point'] ?></span>
                                                <span class="text-[10px] font-black text-blue-400">START</span>
                                            </div>
                                        </div>

                                        <?php 
                                        $calc_time = strtotime($trip['start_time']);
                                        foreach($towns as $town): 
                                            $calc_time = strtotime("+" . $town['duration'] . " minutes", $calc_time);
                                        ?>
                                            <div class="relative flex items-center gap-10">
                                                <div class="z-10 w-3 h-3 rounded-full bg-slate-700 ring-2 ring-slate-800"></div>
                                                <div class="flex-1 flex justify-between items-center border-b border-white/5 pb-2">
                                                    <span class="text-xs font-bold text-slate-400 uppercase"><?= $town['name'] ?></span>
                                                    <span class="text-[10px] font-black text-slate-300 bg-white/5 px-3 py-1 rounded-lg italic">
                                                        <?= date('h:i A', $calc_time) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="relative flex items-center gap-10">
                                            <div class="z-10 w-4 h-4 rounded-full bg-emerald-600 ring-4 ring-emerald-600/20 shadow-[0_0_15px_rgba(16,185,129,0.8)]"></div>
                                            <div class="flex-1 flex justify-between items-center border-b border-white/5 pb-2">
                                                <span class="text-xs font-black uppercase text-white tracking-widest italic"><?= $trip['end_point'] ?></span>
                                                <span class="text-[10px] font-black text-emerald-400">FINISH</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if(!$found_any): ?>
                            <div class="py-32 text-center glass rounded-[40px] opacity-40">
                                <i class="fas fa-bed text-6xl mb-6 text-slate-700"></i>
                                <h4 class="font-black uppercase tracking-[0.3em] text-sm italic">No Operations Today</h4>
                                <p class="text-[10px] mt-2 font-bold text-slate-500 uppercase">This bus is not scheduled for <?= $day ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    

    <script>
        function showDay(dayName) {
            // Check if day is clickable
            const activeBtn = document.getElementById('btn-' + dayName);
            if(activeBtn.classList.contains('cursor-not-allowed')) return;

            // Hide all content
            document.querySelectorAll('.day-content').forEach(c => c.classList.remove('active'));
            const targetContent = document.getElementById('content-' + dayName);
            if(targetContent) targetContent.classList.add('active');

            // Reset Button Styles
            document.querySelectorAll('.day-btn').forEach(b => {
                if(!b.classList.contains('cursor-not-allowed')){
                    b.classList.remove('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-2xl', 'scale-105');
                    b.classList.add('text-slate-300', 'border-slate-800');
                }
            });

            // Set Active Button Style
            activeBtn.classList.add('bg-blue-600', 'text-white', 'border-blue-600', 'shadow-2xl', 'scale-105');
            activeBtn.classList.remove('text-slate-300', 'border-slate-800');

            activeBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }

        window.onload = () => {
            const today = "<?= $current_day ?>";
            // Check if today has trips, else find the first day that has trips
            const activeBtn = document.getElementById('btn-' + today);
            if(activeBtn && !activeBtn.classList.contains('cursor-not-allowed')) {
                showDay(today);
            } else {
                const firstDay = document.querySelector('.day-btn:not(.cursor-not-allowed)');
                if(firstDay) {
                    const id = firstDay.id.replace('btn-', '');
                    showDay(id);
                }
            }
        };
    </script>
</body>
</html>