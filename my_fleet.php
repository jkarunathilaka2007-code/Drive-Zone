<?php
session_start();
require_once 'db_config.php';

// Owner log welada balanna
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

// Search Logic
$search_query = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_query = " AND (bus_number LIKE '%$search%' OR brand LIKE '%$search%')";
}

// Fetch all buses for this owner
$sql = "SELECT * FROM buses WHERE owner_id = $owner_id $search_query ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fleet | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-glass { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(8px); }
        body { background-color: #0f172a; color: #f8fafc; }
        .bus-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .bus-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="min-h-screen">

    <div class="bg-slate-800/60 shadow-xl sticky top-0 z-30 backdrop-blur-lg border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="bus_owner_dashboard.php" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-700/50 text-gray-400 hover:text-blue-500 transition shadow-inner">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-black italic uppercase tracking-tighter">My <span class="text-blue-600">Fleet</span></h1>
            </div>
            <a href="add_bus.php" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-lg shadow-blue-900/40 hover:bg-blue-500 transition-all active:scale-95">
                <i class="fas fa-plus mr-2"></i> Add New Bus
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-10">
        
        <div class="mb-12 flex flex-col md:flex-row gap-4 items-center justify-between">
            <form action="" method="GET" class="w-full md:w-96 relative">
                <input type="text" name="search" placeholder="Search by Bus No or Brand..." 
                value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
                class="w-full pl-12 pr-4 py-4 rounded-[22px] bg-slate-800/40 border border-slate-700 outline-none focus:border-blue-500 transition text-sm font-bold shadow-inner">
                <i class="fas fa-search absolute left-5 top-5 text-slate-500"></i>
            </form>
            <div class="text-right hidden md:block">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.3em] mb-1">Fleet Statistics</p>
                <p class="text-xs font-bold text-slate-300 italic">Managing <span class="text-blue-500 font-black"><?php echo $result->num_rows; ?></span> active units</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($result->num_rows > 0): ?>
                <?php while($bus = $result->fetch_assoc()): ?>
                <div class="bus-card bg-slate-800/40 rounded-[45px] border border-white/5 overflow-hidden flex flex-col hover:shadow-2xl hover:shadow-blue-900/20 hover:border-blue-500/30">
                    
                    <div class="relative h-60 overflow-hidden">
                        <img src="<?php echo $bus['img_front']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-1000">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-80"></div>
                        <div class="absolute top-5 left-5 bg-blue-600 text-white px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest shadow-xl">
                            <?php echo $bus['bus_type']; ?>
                        </div>
                    </div>

                    <div class="p-8 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-2xl font-black tracking-tighter uppercase text-white"><?php echo $bus['bus_number']; ?></h3>
                                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mt-1"><?php echo $bus['brand']; ?> • <?php echo $bus['model']; ?></p>
                            </div>
                            <div class="bg-blue-600/10 p-3 rounded-2xl text-center min-w-[75px] border border-blue-600/20">
                                <span class="block text-blue-500 font-black text-2xl leading-none"><?php echo $bus['number_of_seats']; ?></span>
                                <span class="text-[8px] text-blue-400 font-black uppercase tracking-tighter">Seats</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-8">
                            <div class="bg-slate-900/60 p-4 rounded-3xl border border-white/5">
                                <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Status</p>
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <p class="text-[10px] font-bold text-emerald-500 uppercase"><?php echo $bus['status']; ?></p>
                                </div>
                            </div>
                            <div class="bg-slate-900/60 p-4 rounded-3xl border border-white/5">
                                <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Transmission</p>
                                <p class="text-[10px] font-bold uppercase text-slate-300"><?php echo $bus['gear_type']; ?></p>
                            </div>
                        </div>

                        <div class="space-y-4 mt-auto">
                            <div class="flex gap-2">
                                <a href="edit_bus.php?id=<?php echo $bus['id']; ?>" class="flex-1 bg-slate-700/50 text-slate-300 text-center py-4 rounded-2xl font-black text-[9px] uppercase tracking-widest hover:bg-slate-700 hover:text-white transition shadow-sm border border-white/5">
                                    <i class="fas fa-th mr-1 text-blue-500"></i> Layout
                                </a>

                                <?php if (!empty($bus['route_id'])): ?>
                                <a href="schedule.php?bus_id=<?php echo $bus['id']; ?>&route_id=<?php echo $bus['route_id']; ?>" class="w-12 h-14 flex items-center justify-center bg-blue-500/10 text-blue-500 rounded-2xl hover:bg-blue-500 hover:text-white transition-all border border-blue-500/20" title="Weekly Schedule">
                                    <i class="fas fa-calendar-alt text-base"></i>
                                </a>

                                <a href="route_prices.php?bus_id=<?php echo $bus['id']; ?>&route_id=<?php echo $bus['route_id']; ?>" class="w-12 h-14 flex items-center justify-center bg-amber-500/10 text-amber-500 rounded-2xl hover:bg-amber-500 hover:text-white transition-all border border-amber-500/20" title="Ticket Fares">
                                    <i class="fas fa-ticket-alt text-base"></i>
                                </a>
                                <?php endif; ?>

                                <a href="bus_info.php?id=<?php echo $bus['id']; ?>" class="w-12 h-14 flex items-center justify-center bg-emerald-500/10 text-emerald-500 rounded-2xl hover:bg-emerald-500 hover:text-white transition-all border border-emerald-500/20" title="Full Info">
                                    <i class="fas fa-info-circle text-base"></i>
                                </a>
                            </div>

                            <?php if (!empty($bus['route_id'])): ?>
                                <a href="edit_route.php?bus_id=<?php echo $bus['id']; ?>&route_id=<?php echo $bus['route_id']; ?>" class="w-full bg-emerald-600/10 text-emerald-500 text-center py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all border border-emerald-600/20 flex items-center justify-center">
                                    <i class="fas fa-route mr-2"></i> Update Route Details
                                </a>
                            <?php else: ?>
                                <a href="add_route.php?bus_id=<?php echo $bus['id']; ?>" class="w-full bg-blue-600 text-white text-center py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-900/30 flex items-center justify-center">
                                    <i class="fas fa-plus-circle mr-2"></i> Setup Route Schedule
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-24 text-center bg-slate-800/20 rounded-[50px] border-2 border-dashed border-slate-700/50">
                    <i class="fas fa-bus text-5xl text-slate-700 mb-6"></i>
                    <h3 class="text-xl font-bold text-slate-400 italic">No units in your fleet</h3>
                    <p class="text-slate-600 text-sm mt-2 max-w-xs mx-auto">Ready to expand? Register your first bus to start managing routes and fares.</p>
                    <a href="add_bus.php" class="mt-8 inline-block bg-blue-600 text-white px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-blue-500 transition-all shadow-lg shadow-blue-900/40">Register Unit</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>