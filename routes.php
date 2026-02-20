<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$sql = "SELECT * FROM routes WHERE owner_id = $owner_id ORDER BY created_at DESC";
$result = $conn->query($sql);

// සතියේ දවස් 7 පිළිවෙලට Array එකක් හදාගන්නවා
$week_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Routes | DriveZone Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="text-slate-100 p-4 md:p-10">

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-4">
            <div>
                <h1 class="text-4xl font-black italic uppercase tracking-tighter">My <span class="text-indigo-500">Routes</span></h1>
                <p class="text-slate-500 font-bold text-xs uppercase tracking-widest mt-1 italic">Weekly Schedule Overview</p>
            </div>
            <div class="flex gap-3">
                <a href="add_route.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest transition shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-plus-circle mr-2"></i> Add Route
                </a>
                <a href="bus_owner_dashboard.php" class="glass px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    // DB එකේ තියෙන දවස් ටික array එකක් කරගන්නවා (උදා: ["Monday", "Wednesday"])
                    $active_days = array_map('trim', explode(',', $row['schedule_days']));
                ?>
                    <div class="glass p-8 rounded-[40px] hover:border-indigo-500/50 transition duration-500 group relative">
                        <div class="flex flex-col lg:flex-row justify-between gap-8">
                            
                            <div class="flex-1 space-y-6">
                                <div class="flex items-center gap-4">
                                    <span class="bg-indigo-500 text-white px-4 py-1 rounded-xl font-black text-xl italic shadow-lg shadow-indigo-500/20">
                                        #<?php echo htmlspecialchars($row['route_number']); ?>
                                    </span>
                                    <h2 class="text-2xl font-black uppercase tracking-tight">
                                        <?php echo htmlspecialchars($row['start_point']); ?> 
                                        <i class="fas fa-exchange-alt text-indigo-500 mx-2 text-sm"></i> 
                                        <?php echo htmlspecialchars($row['end_point']); ?>
                                    </h2>
                                </div>

                                <div>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Weekly Operational Days</p>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach($week_days as $day): 
                                            $is_active = in_array($day, $active_days);
                                        ?>
                                            <div class="flex flex-col items-center">
                                                <div class="w-10 h-12 md:w-14 md:h-16 rounded-xl border flex flex-col items-center justify-center transition-all duration-300 <?php echo $is_active ? 'bg-indigo-600 border-indigo-400 shadow-lg shadow-indigo-500/20 scale-105' : 'bg-slate-800/50 border-slate-700 opacity-40'; ?>">
                                                    <span class="text-[9px] font-black uppercase <?php echo $is_active ? 'text-indigo-100' : 'text-slate-500'; ?>">
                                                        <?php echo substr($day, 0, 3); ?>
                                                    </span>
                                                    <?php if($is_active): ?>
                                                        <i class="fas fa-check-circle text-[10px] mt-1 text-white"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle text-[10px] mt-1 text-slate-600"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col justify-between items-end gap-6 border-l border-slate-800/50 pl-8">
                                <div class="text-right space-y-4">
                                    <div class="flex items-center gap-4 justify-end">
                                        <div class="text-right">
                                            <p class="text-[9px] font-bold text-slate-500 uppercase">Start</p>
                                            <p class="text-lg font-black text-emerald-400"><?php echo date('h:i A', strtotime($row['start_time'])); ?></p>
                                        </div>
                                        <i class="fas fa-clock text-slate-700"></i>
                                        <div class="text-right">
                                            <p class="text-[9px] font-bold text-slate-500 uppercase">End</p>
                                            <p class="text-lg font-black text-blue-400"><?php echo date('h:i A', strtotime($row['destination_time'])); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <a href="edit_route.php?id=<?php echo $row['id']; ?>" class="p-3 rounded-xl bg-slate-800 hover:bg-indigo-600 transition text-slate-400 hover:text-white">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?php echo (int)$row['id']; ?>)" class="p-3 rounded-xl bg-slate-800 hover:bg-red-600 transition text-slate-400 hover:text-white">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="glass p-20 rounded-[50px] text-center border-2 border-dashed border-slate-800">
                    <i class="fas fa-calendar-alt text-4xl text-slate-700 mb-4"></i>
                    <h3 class="text-xl font-black uppercase">No Routes Found</h3>
                    <a href="add_route.php" class="inline-block mt-6 bg-indigo-600 text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest">Add First Route</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if(confirm('Delete this route schedule?')) {
                window.location.href = 'delete_route.php?id=' + id;
            }
        }
    </script>
</body>
</html>