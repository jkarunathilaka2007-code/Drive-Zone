<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_fleet.php");
    exit();
}

$bus_id = mysqli_real_escape_string($conn, $_GET['id']);
$owner_id = $_SESSION['user_id'];

// SQL Query updated to match your system's route structure
$sql = "SELECT b.*, 
        r.route_number, r.start_point, r.end_point, r.start_time, r.destination_time, 
        r.route_path_json, r.schedule_days as route_days,
        d.full_name as driver_name, d.contact_no as driver_phone, d.license_no as driver_license,
        c.full_name as conductor_name, c.mobile_number as conductor_phone, c.conductor_license_no
        FROM buses b
        LEFT JOIN routes r ON b.route_id = r.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        LEFT JOIN conductors c ON b.conductor_id = c.id
        WHERE b.id = '$bus_id' AND b.owner_id = '$owner_id'";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("<div class='text-white text-center mt-20 font-bold uppercase tracking-widest'>Bus not found in your fleet.</div>");
}

// Logic to handle Intermediate Town Timing
$towns = json_decode($data['route_path_json'], true) ?: [];
$start_timestamp = $data['start_time'] ? strtotime($data['start_time']) : null;
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Bus Intelligence | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { background-color: #0b0f1a; font-family: 'Outfit', sans-serif; }
        .card-glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .bus-badge { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    </style>
</head>
<body class="text-slate-300 p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div class="flex items-center space-x-4">
                <a href="my_fleet.php" class="w-12 h-12 flex items-center justify-center bg-slate-800/80 rounded-2xl hover:bg-blue-600 transition shadow-lg border border-white/5">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black italic text-white tracking-tighter uppercase">Bus <span class="text-blue-500">Intelligence</span></h1>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">Operational Overview</p>
                </div>
            </div>
            <div class="bus-badge px-6 py-3 rounded-2xl border border-white/20 flex items-center space-x-3 shadow-xl">
                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                <span class="text-sm font-black text-white uppercase tracking-widest"><?php echo $data['bus_number']; ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 space-y-6">
                <div class="card-glass rounded-[45px] p-3 overflow-hidden shadow-2xl">
                    <img src="<?php echo !empty($data['img_front']) ? $data['img_front'] : 'assets/img/bus_placeholder.jpg'; ?>" class="w-full h-72 object-cover rounded-[40px]">
                    <div class="p-6 text-center">
                        <h3 class="text-2xl font-black text-white"><?php echo $data['brand']; ?> <span class="text-blue-500"><?php echo $data['model']; ?></span></h3>
                        <div class="flex justify-center gap-2 mt-2">
                            <span class="text-[10px] font-bold bg-slate-800 px-3 py-1 rounded-full uppercase text-slate-400 italic"><?php echo $data['gear_type']; ?></span>
                            <span class="text-[10px] font-bold bg-blue-600/20 px-3 py-1 rounded-full uppercase text-blue-400 italic"><?php echo $data['bus_type']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="card-glass p-6 rounded-[30px] text-center border-b-2 border-blue-500/30">
                        <i class="fas fa-couch text-blue-500 mb-2 text-xl"></i>
                        <p class="text-2xl font-black text-white"><?php echo $data['number_of_seats']; ?></p>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Capcity</p>
                    </div>
                    <div class="card-glass p-6 rounded-[30px] text-center border-b-2 border-emerald-500/30">
                        <i class="fas fa-shield-alt text-emerald-500 mb-2 text-xl"></i>
                        <p class="text-xs font-black text-white uppercase">Insured</p>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Status</p>
                    </div>
                </div>

                <div class="card-glass p-8 rounded-[40px] space-y-5">
                    <h4 class="text-[11px] font-black uppercase text-slate-400 tracking-[0.2em] flex items-center">
                        <i class="fas fa-id-card-alt mr-3 text-blue-500"></i> Active Crew
                    </h4>
                    
                    <div class="flex items-center space-x-4 p-4 bg-slate-900/40 rounded-3xl border border-white/5">
                        <div class="w-12 h-12 bg-blue-600/10 rounded-2xl flex items-center justify-center text-blue-500 text-xl shadow-inner"><i class="fas fa-user-circle"></i></div>
                        <div class="flex-1">
                            <p class="text-[10px] text-slate-500 uppercase font-black tracking-tighter">Primary Driver</p>
                            <p class="text-sm font-black text-white"><?php echo $data['driver_name'] ?? 'Vacant'; ?></p>
                        </div>
                        <?php if($data['driver_phone']): ?>
                            <a href="tel:<?= $data['driver_phone'] ?>" class="w-8 h-8 bg-slate-800 rounded-full flex items-center justify-center text-xs hover:bg-emerald-600 transition"><i class="fas fa-phone"></i></a>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center space-x-4 p-4 bg-slate-900/40 rounded-3xl border border-white/5">
                        <div class="w-12 h-12 bg-emerald-600/10 rounded-2xl flex items-center justify-center text-emerald-500 text-xl shadow-inner"><i class="fas fa-user-tag"></i></div>
                        <div class="flex-1">
                            <p class="text-[10px] text-slate-500 uppercase font-black tracking-tighter">Conductor</p>
                            <p class="text-sm font-black text-white"><?php echo $data['conductor_name'] ?? 'Vacant'; ?></p>
                        </div>
                        <?php if($data['conductor_phone']): ?>
                            <a href="tel:<?= $data['conductor_phone'] ?>" class="w-8 h-8 bg-slate-800 rounded-full flex items-center justify-center text-xs hover:bg-emerald-600 transition"><i class="fas fa-phone"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-8">
                
                <div class="card-glass rounded-[50px] p-8 md:p-12 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-10 opacity-5">
                        <i class="fas fa-route text-[150px]"></i>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
                        <div>
                            <span class="bg-blue-600/20 text-blue-400 text-[10px] font-black px-4 py-2 rounded-full uppercase tracking-[0.2em] border border-blue-500/20">Operational Route</span>
                            <h2 class="text-6xl font-black text-white italic mt-4 tracking-tighter"><?php echo $data['route_number'] ?? 'N/A'; ?></h2>
                        </div>
                        <div class="flex flex-wrap gap-2 justify-end max-w-xs">
                            <?php 
                            $active_days = explode(',', $data['route_days'] ?? '');
                            foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d) {
                                $match = false;
                                foreach($active_days as $ad) if(stripos($ad, $d) !== false) $match = true;
                                $class = $match ? "bg-blue-600 text-white border-blue-400 shadow-blue-900/40" : "bg-slate-900/50 text-slate-700 border-slate-800";
                                echo "<span class='px-3 py-2 rounded-xl text-[9px] font-black uppercase border $class transition-all'>$d</span>";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="relative bg-slate-950/50 p-8 md:p-12 rounded-[45px] border border-white/5 shadow-inner">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-12">
                            <div class="text-center md:text-left z-10">
                                <p class="text-5xl font-black text-white tracking-tighter"><?php echo $data['start_time'] ? date("h:i A", strtotime($data['start_time'])) : '--:--'; ?></p>
                                <p class="text-xs font-black text-blue-500 uppercase tracking-[0.3em] mt-3"><?php echo $data['start_point'] ?? 'Not Set'; ?></p>
                            </div>
                            
                            <div class="flex-1 w-full flex flex-col items-center">
                                <div class="w-full h-[3px] bg-slate-800 relative mb-6 hidden md:block">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-emerald-500 to-blue-600"></div>
                                    <div class="absolute -top-[6px] left-0 w-4 h-4 bg-blue-600 rounded-full ring-4 ring-slate-950"></div>
                                    <div class="absolute -top-[6px] right-0 w-4 h-4 bg-blue-600 rounded-full ring-4 ring-slate-950"></div>
                                    <i class="fas fa-bus text-white absolute -top-3 left-1/2 -translate-x-1/2 bg-slate-950 px-3 text-sm"></i>
                                </div>
                                <span class="px-6 py-2 bg-slate-900 rounded-full border border-white/5 text-[9px] font-black uppercase tracking-[0.4em] text-slate-500">Scheduled Flight Path</span>
                            </div>

                            <div class="text-center md:text-right z-10">
                                <p class="text-5xl font-black text-white tracking-tighter"><?php echo $data['destination_time'] ? date("h:i A", strtotime($data['destination_time'])) : '--:--'; ?></p>
                                <p class="text-xs font-black text-emerald-500 uppercase tracking-[0.3em] mt-3"><?php echo $data['end_point'] ?? 'Not Set'; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12">
                        <h4 class="text-[11px] font-black uppercase text-slate-500 tracking-widest mb-6 flex items-center">
                            <i class="fas fa-map-marked-alt mr-3"></i> Intermediate Checkpoints
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php 
                            if(!empty($towns) && $start_timestamp):
                                $current_time = $start_timestamp;
                                foreach($towns as $town):
                                    // Logic: Add duration minutes to current time
                                    $current_time = strtotime("+" . $town['duration'] . " minutes", $current_time);
                            ?>
                            <div class="group flex justify-between items-center p-5 bg-slate-900/30 rounded-3xl border border-white/5 hover:border-blue-500/30 transition-all shadow-sm">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center text-[10px] text-slate-500 group-hover:bg-blue-600 group-hover:text-white transition-all mr-4">
                                        <i class="fas fa-map-pin"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-300"><?php echo $town['name']; ?></span>
                                </div>
                                <span class="text-[10px] font-black text-blue-400 bg-blue-400/10 px-4 py-2 rounded-xl border border-blue-400/10">
                                    <?php echo date("h:i A", $current_time); ?>
                                </span>
                            </div>
                            <?php endforeach; else: ?>
                            <div class="col-span-2 text-center py-12 bg-slate-900/20 rounded-[40px] border border-dashed border-slate-800">
                                <i class="fas fa-route text-slate-700 text-3xl mb-3"></i>
                                <p class="text-[10px] text-slate-600 font-black uppercase tracking-widest">No detailed pathway configured</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-4">
                   <a href="my_fleet.php" class="px-10 py-5 bg-slate-800/80 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-slate-700 transition border border-white/5 text-center">Back to Fleet</a>
                   <a href="edit_bus.php?id=<?php echo $data['id']; ?>" class="px-10 py-5 bg-blue-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-blue-500 transition shadow-2xl shadow-blue-900/40 text-center">Edit Bus Specs</a>
                </div>

            </div>
        </div>
    </div>

</body>
</html>