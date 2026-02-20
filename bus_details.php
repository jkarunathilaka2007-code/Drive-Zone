<?php
session_start();
require_once 'db_config.php';

if (!isset($_GET['bus_id'])) {
    header("Location: tripbook.php");
    exit();
}

$bus_id = mysqli_real_escape_string($conn, $_GET['bus_id']);

$sql = "SELECT b.*, r.start_point, r.end_point, r.start_time, r.destination_time, r.route_number as r_num, 
               o.owner_name, o.company_name, o.contact_number as owner_phone, o.company_logo
        FROM buses b 
        LEFT JOIN routes r ON b.route_id = r.id 
        LEFT JOIN bus_owners o ON b.owner_id = o.id
        WHERE b.id = '$bus_id'";

$result = mysqli_query($conn, $sql);
$bus = mysqli_fetch_assoc($result);

if (!$bus) { die("Vehicle Records Not Found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $bus['bus_number'] ?> | DriveZone Exclusive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap');
        body { background: #020617; color: #f8fafc; font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .modal-blur { background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(15px); }
        
        /* Premium Seat Styling */
        .seat-box { 
            width: 42px; height: 42px; border-radius: 12px; 
            background: linear-gradient(145deg, #1e293b, #0f172a); 
            border: 1px solid #334155; position: relative;
            transition: all 0.3s;
        }
        .seat-box:hover { border-color: #3b82f6; transform: translateY(-2px); }
        .seat-num { font-size: 10px; font-weight: 900; color: #64748b; }
        .seat-box.driver-spot { background: #3b82f6; border: none; color: white; }
        
        .aisle { width: 40px; }
        .gradient-text { background: linear-gradient(to right, #60a5fa, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="antialiased">

    <div id="layoutModal" class="hidden fixed inset-0 z-[100] modal-blur flex items-center justify-center p-4">
        <div class="glass max-w-md w-full rounded-[60px] p-10 border-blue-500/20 shadow-2xl overflow-y-auto max-h-[90vh]">
            <div class="flex justify-between items-center mb-10">
                <h3 class="text-2xl font-black uppercase italic tracking-tighter">Seat <span class="text-blue-500">Plan</span></h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full glass flex items-center justify-center hover:bg-red-500/20 hover:text-red-500 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="bg-slate-900 border-[6px] border-slate-800 rounded-[50px] p-8 shadow-inner">
                <div class="flex justify-between items-center mb-10 pb-6 border-b border-white/5">
                    <div class="seat-box driver-spot flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <i class="fas fa-steering-wheel"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Door</p>
                        <div class="h-1 w-8 bg-emerald-500 rounded-full mt-1 ml-auto"></div>
                    </div>
                </div>

                <div class="space-y-4">
                    <?php 
                    $rows = $bus['total_rows'] ?? 10;
                    $per_row = $bus['seats_per_row'] ?? 4;
                    $left_side = floor($per_row / 2);
                    $seat_counter = 1;

                    for($i=1; $i<=$rows; $i++): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex gap-3">
                                <?php for($j=1; $j<=$left_side; $j++): ?>
                                    <div class="seat-box flex items-center justify-center">
                                        <span class="seat-num"><?= $seat_counter++ ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <div class="aisle flex justify-center">
                                <div class="w-[1px] h-full bg-slate-800"></div>
                            </div>

                            <div class="flex gap-3">
                                <?php for($j=1; $j<=($per_row - $left_side); $j++): ?>
                                    <div class="seat-box flex items-center justify-center">
                                        <span class="seat-num"><?= $seat_counter++ ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <div class="flex gap-3 justify-center pt-4 border-t border-white/5">
                        <?php for($k=1; $k<=$bus['last_row_seats']; $k++): ?>
                            <div class="seat-box flex items-center justify-center">
                                <span class="seat-num"><?= $seat_counter++ ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex items-center justify-center gap-6 opacity-50">
                <div class="flex items-center gap-2 text-[9px] font-bold uppercase"><div class="w-3 h-3 rounded bg-blue-500"></div> Driver</div>
                <div class="flex items-center gap-2 text-[9px] font-bold uppercase"><div class="w-3 h-3 rounded bg-slate-800 border border-slate-700"></div> Passenger</div>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto p-6 md:p-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div class="space-y-2">
                <a href="tripbook.php" class="text-blue-500 text-[10px] font-black uppercase tracking-widest hover:text-white transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Trip Booking
                </a>
                <h1 class="text-5xl font-black italic tracking-tighter uppercase leading-none">
                    <?= $bus['brand'] ?> <span class="gradient-text"><?= $bus['model'] ?></span>
                </h1>
                <div class="flex items-center gap-4 text-slate-500">
                    <span class="text-xs font-bold uppercase">Reg No: <?= $bus['bus_number'] ?></span>
                    <span class="w-1 h-1 bg-slate-700 rounded-full"></span>
                    <span class="text-xs font-bold uppercase"><?= $bus['bus_type'] ?> Class</span>
                </div>
            </div>
            
            <div class="glass p-4 rounded-[30px] flex items-center gap-4">
                <img src="<?= !empty($bus['company_logo']) ? $bus['company_logo'] : 'https://ui-avatars.com/api/?name='.urlencode($bus['company_name']).'&background=3b82f6&color=fff' ?>" class="w-12 h-12 rounded-2xl object-cover">
                <div>
                    <p class="text-[8px] font-black text-slate-500 uppercase">Provider</p>
                    <p class="text-sm font-black italic text-white"><?= $bus['company_name'] ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <div class="lg:col-span-7 space-y-8">
                <div class="relative group">
                    <div class="rounded-[50px] overflow-hidden aspect-video glass border border-white/10 relative">
                        <img id="activeImg" src="<?= $bus['img_front'] ?>" class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    </div>
                    
                    <div class="flex gap-4 mt-6 overflow-x-auto pb-2">
                        <?php $images = ['img_front', 'img_interior', 'img_back', 'img_left', 'img_right'];
                        foreach($images as $img): if(!empty($bus[$img])): ?>
                            <button onclick="document.getElementById('activeImg').src='<?= $bus[$img] ?>'" class="w-24 h-24 rounded-3xl overflow-hidden glass border-2 border-transparent hover:border-blue-500 transition-all flex-shrink-0">
                                <img src="<?= $bus[$img] ?>" class="w-full h-full object-cover">
                            </button>
                        <?php endif; endforeach; ?>
                    </div>
                </div>

                <div class="glass rounded-[45px] p-10">
                    <h4 class="text-xs font-black uppercase tracking-[0.3em] mb-8 text-slate-500 italic">Premium Amenities</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <?php $facs = explode(',', $bus['facilities']);
                        foreach($facs as $f): if(!empty($f)): ?>
                            <div class="text-center group">
                                <div class="w-14 h-14 rounded-2xl glass flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-600 transition-all shadow-lg">
                                    <i class="fas fa-check text-blue-500 group-hover:text-white"></i>
                                </div>
                                <p class="text-[9px] font-black uppercase text-slate-400"><?= trim($f) ?></p>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-8">
                <div class="glass rounded-[45px] p-10 border-t-4 border-blue-500 shadow-2xl">
                    <div class="flex justify-between items-center mb-10">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Main Route #<?= $bus['r_num'] ?></span>
                        <div class="px-3 py-1 bg-emerald-500/10 text-emerald-400 text-[8px] font-black rounded-full uppercase">Available Now</div>
                    </div>

                    <div class="space-y-10 relative">
                        <div class="absolute left-[15px] top-4 bottom-4 w-[1px] bg-slate-800 dashed"></div>
                        
                        <div class="flex gap-6 relative z-10">
                            <div class="w-8 h-8 rounded-full bg-blue-600 border-4 border-slate-900 flex items-center justify-center flex-shrink-0"></div>
                            <div>
                                <p class="text-[9px] font-black text-slate-500 uppercase">Departure Point</p>
                                <h5 class="text-xl font-black italic"><?= $bus['start_point'] ?></h5>
                                <p class="text-xs font-bold text-blue-400"><?= date("g:i A", strtotime($bus['start_time'] ?? '08:00')) ?></p>
                            </div>
                        </div>

                        <div class="flex gap-6 relative z-10">
                            <div class="w-8 h-8 rounded-full bg-amber-500 border-4 border-slate-900 flex items-center justify-center flex-shrink-0"></div>
                            <div>
                                <p class="text-[9px] font-black text-slate-500 uppercase">Destination</p>
                                <h5 class="text-xl font-black italic"><?= $bus['end_point'] ?></h5>
                                <p class="text-xs font-bold text-amber-400"><?= date("g:i A", strtotime($bus['destination_time'] ?? '16:00')) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-12 pt-10 border-t border-white/5">
                        <div class="text-center">
                            <p class="text-[8px] font-black text-slate-500 uppercase mb-1">Max Capacity</p>
                            <p class="text-2xl font-black italic tracking-tighter"><?= $bus['number_of_seats'] ?> <span class="text-xs text-slate-600">PAX</span></p>
                        </div>
                        <button onclick="openModal()" class="glass rounded-3xl p-4 hover:bg-white/5 transition border-blue-500/20 group">
                            <p class="text-[8px] font-black text-slate-500 uppercase mb-1">Seating Plan</p>
                            <p class="text-xs font-black text-blue-500 group-hover:text-white">VIEW LAYOUT <i class="fas fa-expand-alt ml-1"></i></p>
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <a href="confirm_trip.php?bus_id=<?= $bus['id'] ?>" class="block w-full bg-blue-600 hover:bg-blue-500 text-white py-6 rounded-[30px] text-center font-black uppercase text-xs tracking-[0.3em] shadow-2xl shadow-blue-900/40 transition active:scale-95">
                        Book for Your Trip
                    </a>
                    <div class="flex gap-4">
                        <a href="tel:<?= $bus['owner_phone'] ?>" class="flex-1 glass py-4 rounded-[20px] text-center text-[9px] font-black uppercase tracking-widest hover:text-blue-500 transition">
                            <i class="fas fa-phone-alt mr-2"></i> Call Support
                        </a>
                        <button class="w-14 h-14 glass rounded-[20px] flex items-center justify-center text-slate-500 hover:text-white transition">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function openModal() {
            document.getElementById('layoutModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('layoutModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>