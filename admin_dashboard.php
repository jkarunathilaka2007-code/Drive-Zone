<?php
session_start();
require_once 'db_config.php';

// Check if Admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Stats Fetch kirima
$total_passengers = $conn->query("SELECT id FROM passengers")->num_rows;
$total_owners = $conn->query("SELECT id FROM bus_owners WHERE status='approved'")->num_rows;
$pending_approvals = $conn->query("SELECT id FROM bus_owners WHERE status='pending'")->num_rows;
// Danata buses table eka nathnam meka 0 widiyata thiyamu
$total_buses = 0; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
</head>
<body class="bg-gray-100 dark:bg-slate-900 text-gray-800 dark:text-gray-100 transition-colors duration-300">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-800 text-white hidden md:flex flex-col">
            <div class="p-6 text-2xl font-bold italic tracking-tighter border-b border-slate-700">
                Drive<span class="text-primary text-blue-400">Zone</span>
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl bg-blue-600">
                    <i class="fas fa-th-large"></i> <span>Overview</span>
                </a>
                <a href="admin_approve_owners.php" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-700 transition">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-user-check"></i> <span>Approvals</span>
                    </div>
                    <?php if($pending_approvals > 0): ?>
                        <span class="bg-red-500 text-xs px-2 py-1 rounded-full"><?php echo $pending_approvals; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-700 transition">
                    <i class="fas fa-users"></i> <span>Passengers</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-700 transition">
                    <i class="fas fa-bus"></i> <span>Manage Buses</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-slate-700 transition">
                    <i class="fas fa-route"></i> <span>Schedules</span>
                </a>
            </nav>
            <div class="p-4 border-t border-slate-700">
                <a href="logout.php" class="flex items-center space-x-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </aside>

        <main class="flex-1 flex flex-col">
            <header class="bg-white dark:bg-slate-800 shadow-sm p-4 flex justify-between items-center px-8">
                <button class="md:hidden text-2xl"><i class="fas fa-bars"></i></button>
                <div class="flex items-center space-x-4">
                    <span class="font-bold hidden sm:inline-block">System Administrator</span>
                    <img src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" class="w-10 h-10 rounded-full border-2 border-primary">
                </div>
            </header>

            <div class="p-8 space-y-8">
                <div class="flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-bold">Dashboard Overview</h2>
                        <p class="text-gray-500 dark:text-gray-400">Welcome back, Admin! Here is what's happening today.</p>
                    </div>
                    <div class="text-sm font-bold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 px-4 py-2 rounded-lg">
                        <i class="far fa-calendar-alt mr-2"></i> <?php echo date('F d, Y'); ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm font-bold uppercase tracking-wider">Passengers</p>
                            <h3 class="text-3xl font-black mt-1"><?php echo $total_passengers; ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm font-bold uppercase tracking-wider">Bus Owners</p>
                            <h3 class="text-3xl font-black mt-1"><?php echo $total_owners; ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-green-50 dark:bg-green-900/30 text-green-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm font-bold uppercase tracking-wider">Pending</p>
                            <h3 class="text-3xl font-black mt-1 text-orange-500"><?php echo $pending_approvals; ?></h3>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 dark:bg-orange-900/30 text-orange-500 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm font-bold uppercase tracking-wider">Revenue</p>
                            <h3 class="text-3xl font-black mt-1 text-purple-600">Rs. 0</h3>
                        </div>
                        <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/30 text-purple-600 rounded-2xl flex items-center justify-center text-xl">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700">
                        <h4 class="text-xl font-bold mb-4">Quick Actions</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="admin_approve_owners.php" class="p-4 bg-gray-50 dark:bg-slate-700 rounded-2xl hover:bg-blue-600 hover:text-white transition flex flex-col items-center justify-center space-y-2 group">
                                <i class="fas fa-user-check text-2xl text-blue-500 group-hover:text-white"></i>
                                <span class="font-bold text-sm">Approve Owners</span>
                            </a>
                            <button class="p-4 bg-gray-50 dark:bg-slate-700 rounded-2xl hover:bg-blue-600 hover:text-white transition flex flex-col items-center justify-center space-y-2 group">
                                <i class="fas fa-bullhorn text-2xl text-orange-500 group-hover:text-white"></i>
                                <span class="font-bold text-sm">Announcements</span>
                            </button>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-8 rounded-3xl shadow-lg text-white flex flex-col justify-center relative overflow-hidden">
                        <i class="fas fa-shield-alt absolute -right-10 -bottom-10 text-[200px] opacity-10"></i>
                        <h4 class="text-2xl font-bold mb-2">System Health</h4>
                        <p class="text-blue-100 mb-6">Everything is running smoothly. Last backup was taken 2 hours ago.</p>
                        <button class="bg-white text-blue-600 font-bold py-2 px-6 rounded-xl w-fit hover:bg-blue-50 transition">Run Diagnostics</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>