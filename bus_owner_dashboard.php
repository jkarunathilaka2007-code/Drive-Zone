<?php
session_start();
require_once 'db_config.php';

// Owner ලොග් වෙලාද බලන්න
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$owner_name = $_SESSION['user_name'];

// --- Dashboard Stats Fetching ---
// 1. Buses
$total_buses = $conn->query("SELECT COUNT(*) as total FROM buses WHERE owner_id = $owner_id")->fetch_assoc()['total'];

// 2. Drivers Stats
$active_drivers = $conn->query("SELECT COUNT(*) as total FROM drivers WHERE owner_id = $owner_id AND status = 'approved' AND job_status = 'active'")->fetch_assoc()['total'];
$idle_drivers = $conn->query("SELECT COUNT(*) as total FROM drivers WHERE owner_id = $owner_id AND status = 'approved' AND job_status = 'deactive'")->fetch_assoc()['total'];
$pending_drivers = $conn->query("SELECT COUNT(*) as total FROM drivers WHERE owner_id = $owner_id AND status = 'pending'")->fetch_assoc()['total'];

// 3. Conductors Stats
$active_conductors = $conn->query("SELECT COUNT(*) as total FROM conductors WHERE owner_id = $owner_id AND status = 'approved' AND job_status = 'active'")->fetch_assoc()['total'];
$idle_conductors = $conn->query("SELECT COUNT(*) as total FROM conductors WHERE owner_id = $owner_id AND status = 'approved' AND job_status = 'deactive'")->fetch_assoc()['total'];
$pending_conductors = $conn->query("SELECT COUNT(*) as total FROM conductors WHERE owner_id = $owner_id AND status = 'pending'")->fetch_assoc()['total'];

$total_active_staff = $active_drivers + $active_conductors;
$total_idle_staff = $idle_drivers + $idle_conductors;

// 4. New Hire Requests
$new_hire_requests = $conn->query("SELECT COUNT(*) as total FROM trip_bookings tb JOIN buses b ON tb.bus_id = b.id WHERE b.owner_id = $owner_id AND tb.status = 'pending'")->fetch_assoc()['total'];

// 5. Total Routes Count (Unique routes for this owner)
$total_routes = $conn->query("SELECT COUNT(DISTINCT route_number) as total FROM buses WHERE owner_id = $owner_id AND route_number IS NOT NULL")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard | DriveZone Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen flex transition-colors duration-300 font-sans">

    <aside class="w-72 bg-white dark:bg-slate-800 border-r border-gray-100 dark:border-slate-700 hidden lg:flex flex-col fixed h-full z-20">
        <div class="p-8">
            <h1 class="text-2xl font-black italic tracking-tighter uppercase">Drive<span class="text-blue-600">Zone</span></h1>
            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mt-1 italic">Owner Administration</p>
        </div>

        <nav class="flex-1 px-4 space-y-2">
            <a href="bus_owner_dashboard.php" class="flex items-center space-x-3 p-4 bg-blue-600 text-white rounded-[20px] shadow-lg shadow-blue-200 dark:shadow-none">
                <i class="fas fa-chart-line"></i>
                <span class="font-bold">Overview</span>
            </a>

            <a href="hire_requests.php" class="flex items-center justify-between p-4 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-[20px] transition group">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-hand-holding-usd group-hover:text-amber-500"></i>
                    <span class="font-bold">Hire Requests</span>
                </div>
                <?php if($new_hire_requests > 0): ?>
                    <span class="bg-amber-500 text-white text-[10px] px-2 py-0.5 rounded-full font-black animate-bounce"><?php echo $new_hire_requests; ?></span>
                <?php endif; ?>
            </a>

            <a href="my_fleet.php" class="flex items-center space-x-3 p-4 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-[20px] transition group">
                <i class="fas fa-bus group-hover:text-blue-600"></i>
                <span class="font-bold">My Fleet</span>
            </a>

            <a href="routes.php" class="flex items-center space-x-3 p-4 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-[20px] transition group">
                <i class="fas fa-route group-hover:text-indigo-500"></i>
                <span class="font-bold">My Routes</span>
            </a>

            <a href="manage_drivers.php" class="flex items-center justify-between p-4 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-[20px] transition group">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-user-steering group-hover:text-blue-600"></i>
                    <span class="font-bold">Drivers</span>
                </div>
                <?php if($pending_drivers > 0): ?>
                    <span class="bg-blue-500 text-white text-[10px] px-2 py-0.5 rounded-full font-black animate-pulse"><?php echo $pending_drivers; ?></span>
                <?php endif; ?>
            </a>
            <a href="manage_conductors.php" class="flex items-center justify-between p-4 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-[20px] transition group">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-id-badge group-hover:text-emerald-500"></i>
                    <span class="font-bold">Conductors</span>
                </div>
                <?php if($pending_conductors > 0): ?>
                    <span class="bg-emerald-500 text-white text-[10px] px-2 py-0.5 rounded-full font-black animate-pulse"><?php echo $pending_conductors; ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="p-6 border-t border-gray-50 dark:border-slate-700">
            <button id="theme-toggle" class="w-full flex items-center justify-center space-x-2 p-3 mb-4 rounded-xl border border-gray-100 dark:border-slate-600 text-gray-500 dark:text-slate-400 transition-all active:scale-95">
                <i id="theme-toggle-dark-icon" class="hidden fas fa-moon"></i>
                <i id="theme-toggle-light-icon" class="hidden fas fa-sun"></i>
                <span class="text-xs font-black uppercase tracking-widest">Theme Mode</span>
            </button>
            <a href="logout.php" class="flex items-center space-x-3 p-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-xl transition font-black text-sm">
                <i class="fas fa-power-off"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <main class="flex-1 lg:ml-72 p-6 md:p-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div>
                <h2 class="text-4xl font-black italic">Welcome, <span class="text-blue-600"><?php echo htmlspecialchars($owner_name); ?></span>.</h2>
                <p class="text-gray-400 dark:text-slate-500 font-bold text-sm tracking-wide mt-1 italic uppercase">Manage your transport company's performance</p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-2 rounded-2xl flex items-center space-x-3 border dark:border-slate-700 pr-5 shadow-sm">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg text-xl">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-gray-400 block">Owner Account</span>
                    <span class="text-sm font-black"><?php echo htmlspecialchars($owner_name); ?></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
            <div class="bg-white dark:bg-slate-800 p-8 rounded-[40px] shadow-sm border dark:border-slate-700 group hover:border-amber-500 transition-all duration-300">
                <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-2xl flex items-center justify-center mb-4 text-xl"><i class="fas fa-handshake"></i></div>
                <h3 class="text-3xl font-black italic text-amber-500"><?php echo $new_hire_requests; ?></h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Hire Requests</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-8 rounded-[40px] shadow-sm border dark:border-slate-700 group hover:border-blue-500 transition-all duration-300">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-2xl flex items-center justify-center mb-4 text-xl"><i class="fas fa-bus"></i></div>
                <h3 class="text-3xl font-black italic"><?php echo $total_buses; ?></h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Fleet Strength</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-8 rounded-[40px] shadow-sm border dark:border-slate-700 group hover:border-emerald-500 transition-all duration-300">
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 rounded-2xl flex items-center justify-center mb-4 text-xl"><i class="fas fa-user-check"></i></div>
                <h3 class="text-3xl font-black italic text-emerald-500"><?php echo $total_active_staff; ?></h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Staff On Duty</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-8 rounded-[40px] shadow-sm border dark:border-slate-700 group hover:border-orange-500 transition-all duration-300">
                <div class="w-12 h-12 bg-orange-50 dark:bg-orange-900/20 text-orange-600 rounded-2xl flex items-center justify-center mb-4 text-xl"><i class="fas fa-user-clock"></i></div>
                <h3 class="text-3xl font-black italic text-orange-500"><?php echo $total_idle_staff; ?></h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Idle (Available)</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-8 rounded-[40px] shadow-sm border dark:border-slate-700 group hover:border-red-500 transition-all duration-300">
                <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 text-red-600 rounded-2xl flex items-center justify-center mb-4 text-xl"><i class="fas fa-user-plus"></i></div>
                <h3 class="text-3xl font-black italic"><?php echo $pending_drivers + $pending_conductors; ?></h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Pending Staff</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-slate-900 text-white p-8 rounded-[50px] flex items-center justify-between shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-5 text-white group-hover:scale-110 transition-transform duration-700">
                    <i class="fas fa-steering-wheel text-9xl"></i>
                </div>
                <div class="flex items-center space-x-6 relative z-10">
                    <div class="p-5 bg-blue-600/20 rounded-[25px] text-blue-500 border border-blue-500/20"><i class="fas fa-user-steering text-3xl"></i></div>
                    <div>
                        <h4 class="font-black italic text-2xl uppercase tracking-tight">Driver <span class="text-blue-500">Status</span></h4>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Fleet Operations</p>
                    </div>
                </div>
                <div class="flex space-x-10 pr-6 relative z-10">
                    <div class="text-center">
                        <p class="text-emerald-500 font-black text-3xl"><?php echo $active_drivers; ?></p>
                        <p class="text-[9px] uppercase font-bold text-slate-500 tracking-tighter">Active</p>
                    </div>
                    <div class="text-center">
                        <p class="text-orange-500 font-black text-3xl"><?php echo $idle_drivers; ?></p>
                        <p class="text-[9px] uppercase font-bold text-slate-500 tracking-tighter">Idle</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 text-white p-8 rounded-[50px] flex items-center justify-between shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-5 text-white group-hover:scale-110 transition-transform duration-700">
                    <i class="fas fa-id-badge text-9xl"></i>
                </div>
                <div class="flex items-center space-x-6 relative z-10">
                    <div class="p-5 bg-emerald-600/20 rounded-[25px] text-emerald-500 border border-emerald-500/20"><i class="fas fa-id-badge text-3xl"></i></div>
                    <div>
                        <h4 class="font-black italic text-2xl uppercase tracking-tight">Conductor <span class="text-emerald-500">Status</span></h4>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Fleet Operations</p>
                    </div>
                </div>
                <div class="flex space-x-10 pr-6 relative z-10">
                    <div class="text-center">
                        <p class="text-emerald-500 font-black text-3xl"><?php echo $active_conductors; ?></p>
                        <p class="text-[9px] uppercase font-bold text-slate-500 tracking-tighter">Active</p>
                    </div>
                    <div class="text-center">
                        <p class="text-orange-500 font-black text-3xl"><?php echo $idle_conductors; ?></p>
                        <p class="text-[9px] uppercase font-bold text-slate-500 tracking-tighter">Idle</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-white dark:bg-slate-800 p-10 rounded-[50px] shadow-sm border dark:border-slate-700 relative overflow-hidden">
                <h3 class="text-2xl font-black mb-2 italic">Driver <span class="text-blue-600">Recruitment</span></h3>
                <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-2xl border-2 border-dashed dark:border-slate-700 mb-4 truncate text-[11px] font-mono text-blue-600" id="driverLinkText">
                    http://localhost/drivezone/driver_register.php?ref=<?php echo $owner_id; ?>
                </div>
                <button onclick="copyLink('driverLinkText')" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition shadow-lg">
                    <i class="fas fa-copy mr-2"></i> Copy Driver Link
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 p-10 rounded-[50px] shadow-sm border dark:border-slate-700 relative overflow-hidden">
                <h3 class="text-2xl font-black mb-2 italic">Conductor <span class="text-emerald-600">Recruitment</span></h3>
                <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-2xl border-2 border-dashed dark:border-slate-700 mb-4 truncate text-[11px] font-mono text-emerald-600" id="conLinkText">
                    http://localhost/drivezone/conductor_register.php?ref=<?php echo $owner_id; ?>
                </div>
                <button onclick="copyLink('conLinkText')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition shadow-lg">
                    <i class="fas fa-copy mr-2"></i> Copy Conductor Link
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
            <a href="hire_requests.php" class="bg-amber-500 p-8 rounded-[40px] text-white hover:translate-y-[-5px] transition duration-300 shadow-xl group">
                <i class="fas fa-calendar-check text-2xl mb-4 group-hover:scale-110 transition"></i>
                <h4 class="font-black text-xl italic">Hire Requests</h4>
                <p class="text-amber-100 text-[10px] font-bold uppercase mt-2">View & Approve</p>
            </a>

            <a href="add_bus.php" class="bg-slate-900 p-8 rounded-[40px] text-white hover:translate-y-[-5px] transition duration-300 shadow-xl group">
                <i class="fas fa-plus-circle text-2xl mb-4 group-hover:text-blue-500 transition"></i>
                <h4 class="font-black text-xl italic">Add New Bus</h4>
                <p class="text-slate-500 text-[10px] font-bold uppercase mt-2">Expansion</p>
            </a>

            <a href="routes.php" class="bg-indigo-600 p-8 rounded-[40px] text-white hover:translate-y-[-5px] transition duration-300 shadow-xl group">
                <i class="fas fa-map-location-dot text-2xl mb-4 group-hover:scale-110 transition"></i>
                <h4 class="font-black text-xl italic">My Routes</h4>
                <p class="text-indigo-200 text-[10px] font-bold uppercase mt-2">Network Hub</p>
            </a>

            <a href="manage_drivers.php" class="bg-white dark:bg-slate-800 border dark:border-slate-700 p-8 rounded-[40px] hover:translate-y-[-5px] transition duration-300 shadow-sm group">
                <i class="fas fa-user-cog text-2xl mb-4 text-blue-600 group-hover:scale-110 transition"></i>
                <h4 class="font-black text-xl italic">Review Drivers</h4>
                <p class="text-gray-400 text-[10px] font-bold uppercase mt-2">HR Management</p>
            </a>
            
            <a href="manage_conductors.php" class="bg-white dark:bg-slate-800 border dark:border-slate-700 p-8 rounded-[40px] hover:translate-y-[-5px] transition duration-300 shadow-sm group">
                <i class="fas fa-users-gear text-2xl mb-4 text-emerald-600 group-hover:scale-110 transition"></i>
                <h4 class="font-black text-xl italic">Review Conductors</h4>
                <p class="text-gray-400 text-[10px] font-bold uppercase mt-2">HR Management</p>
            </a>
        </div>
    </main>

    <script>
        function copyLink(elementId) {
            const linkText = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(linkText).then(() => {
                alert("Link Copied to Clipboard!");
            });
        }

        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        document.getElementById('theme-toggle').addEventListener('click', function() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');
            if (localStorage.getItem('color-theme') === 'light') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            }
        });
    </script>
</body>
</html>