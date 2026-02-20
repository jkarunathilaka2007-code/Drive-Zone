<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

// පද්ධතියේ ඇති සියලුම Active බස් රථ ලබා ගැනීම
$sql = "SELECT * FROM buses WHERE status = 'active' ORDER BY created_at DESC"; 
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hire a Bus | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: #f1f5f9; }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .bus-badge { background: linear-gradient(90deg, #f59e0b, #d97706); }
        /* Smooth scale animation on hover */
        .bus-item { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div>
                <h1 class="text-4xl font-black italic tracking-tighter uppercase">Hire Your <span class="text-amber-500">Trip Partner</span></h1>
                <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mt-2 flex items-center">
                    <span class="w-8 h-[2px] bg-amber-500 mr-3"></span> Premium Fleet for Special Journeys
                </p>
            </div>
            <a href="passengerdashboard.php" class="px-8 py-4 glass-card rounded-[20px] text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all flex items-center border border-white/10">
                <i class="fas fa-arrow-left mr-3 text-amber-500"></i> Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while($bus = mysqli_fetch_assoc($result)): ?>
                    <div class="bus-item glass-card rounded-[45px] overflow-hidden group hover:scale-[1.03] hover:shadow-2xl hover:shadow-blue-900/20 border border-white/5">
                        
                        <div class="h-60 bg-slate-800 relative overflow-hidden">
                            <img src="<?= !empty($bus['img_front']) ? $bus['img_front'] : 'https://images.unsplash.com/photo-1570125909232-eb263c188f7e?q=80&w=600' ?>" 
                                 class="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-110 transition duration-700">
                            
                            <div class="absolute top-6 left-6 bus-badge text-black text-[9px] font-black px-4 py-2 rounded-xl uppercase tracking-widest shadow-lg">
                                <?= $bus['bus_type'] ?>
                            </div>

                            <div class="absolute bottom-0 left-0 w-full p-8 bg-gradient-to-t from-slate-900 via-slate-900/80 to-transparent">
                                <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1">Standard Route</p>
                                <div class="flex items-center text-sm font-black text-white italic">
                                    <?= $bus['route_start'] ?> 
                                    <i class="fas fa-arrow-right mx-3 text-amber-500 text-[10px]"></i> 
                                    <?= $bus['route_end'] ?>
                                </div>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-2xl font-black text-white tracking-tight uppercase italic"><?= $bus['bus_number'] ?></h3>
                                    <p class="text-[10px] text-slate-500 font-bold mt-1 uppercase tracking-wider"><?= $bus['brand'] ?> <?= $bus['model'] ?> • <?= $bus['gear_type'] ?></p>
                                </div>
                                <div class="bg-blue-600/10 border border-blue-500/20 px-4 py-2 rounded-2xl text-center">
                                    <p class="text-[8px] font-black text-blue-400 uppercase">Seats</p>
                                    <p class="text-lg font-black text-white"><?= $bus['number_of_seats'] ?></p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-10 min-h-[60px]">
                                <?php 
                                    $facs = explode(',', $bus['facilities']);
                                    $count = 0;
                                    foreach($facs as $f): 
                                        if(!empty($f) && $count < 4): 
                                ?>
                                    <span class="bg-slate-800/80 text-slate-400 text-[8px] font-black uppercase px-3 py-1.5 rounded-lg border border-white/5 italic">
                                        <i class="fas fa-circle text-[4px] text-amber-500 mr-2"></i> <?= trim($f) ?>
                                    </span>
                                <?php 
                                    $count++;
                                    endif; endforeach; 
                                ?>
                            </div>

                            <div class="flex items-center gap-4">
                                <a href="confirm_trip.php?bus_id=<?= $bus['id'] ?>" class="flex-1 bg-white text-slate-900 py-4 rounded-[20px] text-center text-[10px] font-black uppercase tracking-[0.2em] hover:bg-amber-500 transition-all shadow-xl shadow-white/5 active:scale-95">
                                    Request for Hire
                                </a>
                                
                                <a href="bus_details.php?bus_id=<?= $bus['id'] ?>" class="w-14 h-14 rounded-[20px] border border-white/10 flex items-center justify-center hover:bg-white hover:text-black hover:border-white transition-all duration-300 group/icon shadow-xl" title="Detailed Specifications">
                                    <i class="fas fa-info text-sm group-hover/icon:scale-110"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-24 text-center glass-card rounded-[60px] border-dashed border-white/10">
                    <div class="w-24 h-24 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-8 border border-white/5">
                        <i class="fas fa-bus-alt text-4xl text-slate-700"></i>
                    </div>
                    <h3 class="text-2xl font-black text-white uppercase tracking-widest italic">No Buses Available</h3>
                    <p class="text-slate-500 text-[10px] mt-3 font-black uppercase tracking-[0.3em]">We are currently updating our premium fleet records.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="py-10 text-center">
        <p class="text-[8px] font-black text-slate-700 uppercase tracking-[0.5em]">DriveZone Fleet Management System v2.0</p>
    </div>

</body>
</html>