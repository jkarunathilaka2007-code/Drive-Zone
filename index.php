<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userInitials = "";
$dashboardUrl = "#";

if ($isLoggedIn) {
    // නමේ මුල් අකුරු දෙක ලබා ගැනීම (උදා: "Amal Perera" -> "AP")
    $name = $_SESSION['user_name'] ?? 'User';
    $words = explode(" ", $name);
    if (count($words) >= 2) {
        $userInitials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        $userInitials = strtoupper(substr($name, 0, 2));
    }

    // Role එක අනුව Dashboard URL එක තීරණය කිරීම
    $role = $_SESSION['user_role'] ?? 'passenger';
    if ($role == 'passenger') $dashboardUrl = "passengerdashboard.php";
    else if ($role == 'bus_owner') $dashboardUrl = "bus_owner_dashboard.php";
    else if ($role == 'conductor') $dashboardUrl = "conductordashboard.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveZone | Modern Bus Ticket Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { primary: '#3b82f6' } } }
        }

        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        }

        function openRegisterModal() {
            document.getElementById('regModal').classList.remove('hidden');
            document.getElementById('regModal').classList.add('flex');
        }

        function closeRegisterModal() {
            document.getElementById('regModal').classList.add('hidden');
            document.getElementById('regModal').classList.remove('flex');
        }

        function handleSearch(event) {
            const isLoggedIn = <?= json_encode($isLoggedIn) ?>;
            if (!isLoggedIn) {
                event.preventDefault();
                alert("Please login to search and book tickets!");
                window.location.href = "login.php";
            }
        }

        window.onload = () => {
            if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
        };
    </script>
    <style>
        .glass-effect { background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); }
        .dark .glass-effect { background: rgba(15, 23, 42, 0.8); }
        .modal-bg { background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(5px); }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-slate-900 dark:text-gray-100 transition-colors duration-300">

    <nav class="fixed w-full z-40 glass-effect py-4 px-6 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-4">
            <?php if ($isLoggedIn): ?>
                <a href="<?= $dashboardUrl ?>" class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-black text-xs border-2 border-white dark:border-slate-700 shadow-lg hover:scale-110 transition cursor-pointer">
                    <?= $userInitials ?>
                </a>
            <?php else: ?>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-bus-alt text-3xl text-primary"></i>
                    <span class="text-2xl font-bold tracking-wider">Drive<span class="text-primary">Zone</span></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="flex items-center space-x-4">
            <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-slate-700 transition">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
            </button>
            
            <?php if (!$isLoggedIn): ?>
                <a href="login.php" class="hidden sm:block hover:text-primary font-semibold">Login</a>
                <button onclick="openRegisterModal()" class="bg-primary text-white px-6 py-2 rounded-full font-bold hover:scale-105 transition shadow-lg">Register</button>
            <?php else: ?>
                <a href="logout.php" class="text-red-500 font-bold text-sm hover:underline">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="relative min-h-screen flex items-center justify-center pt-20 px-4 bg-cover bg-center" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2069');">
        <div class="max-w-4xl text-center text-white">
            <h1 class="text-5xl md:text-6xl font-extrabold mb-6 italic">Your Journey Starts Here</h1>
            
            <form onsubmit="handleSearch(event)" class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-2xl flex flex-col md:flex-row gap-4 items-end text-gray-800 dark:text-white mt-10">
                <div class="w-full text-left">
                    <label class="block text-xs font-bold uppercase mb-1">From</label>
                    <input type="text" required placeholder="Departure" class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="w-full text-left">
                    <label class="block text-xs font-bold uppercase mb-1">To</label>
                    <input type="text" required placeholder="Destination" class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                </div>
                <button type="submit" class="w-full bg-primary text-white font-bold py-2 px-8 rounded-lg hover:bg-blue-700 transition h-[42px] uppercase text-xs tracking-widest">
                    Search Buses
                </button>
            </form>
        </div>
    </section>

    <div id="regModal" class="fixed inset-0 z-50 hidden items-center justify-center modal-bg p-4">
        </div>

</body>
</html>