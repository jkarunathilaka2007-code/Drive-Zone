<?php
session_start();
require_once 'db_config.php';

// 1. මගියා ලොග් වී ඇත්දැයි බැලීම
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];

// 2. මගියාගේ මූලික දත්ත ලබා ගැනීම
$sql = "SELECT * FROM passengers WHERE id = '$passenger_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User session error. Please log in again.");
}

// --- [Hire Status Analytics] ---
// Owner විසින් Approve කරන ලද නමුත් මගියා තවම බැලුවේ නැති hires ගණන
$hire_stats_sql = "SELECT COUNT(*) as approved_hires FROM trip_bookings WHERE passenger_id = '$passenger_id' AND status = 'approved'";
$hire_stats_res = mysqli_query($conn, $hire_stats_sql);
$approved_count = mysqli_fetch_assoc($hire_stats_res)['approved_hires'];

// 3. අලුත්ම ටිකට් එකේ දත්ත ලබා ගැනීම (Ordinary Bus Booking)
$booking_sql = "SELECT booking_ref, is_verified, pickup_point, drop_point 
                FROM bookings 
                WHERE passenger_id = '$passenger_id' 
                ORDER BY created_at DESC LIMIT 1";
$booking_result = mysqli_query($conn, $booking_sql);

$latest_ref = "No Active Ticket";
$status_color = "border-blue-500/20 text-white";
$latest_trip = "";

if ($booking_result && mysqli_num_rows($booking_result) > 0) {
    $b_data = mysqli_fetch_assoc($booking_result);
    $latest_ref = $b_data['booking_ref'];
    $latest_trip = (!empty($b_data['pickup_point'])) ? $b_data['pickup_point'] . " - " . $b_data['drop_point'] : "";
    
    if($b_data['is_verified'] == 1) {
        $status_color = "border-emerald-500/40 text-emerald-400";
    }
}

// 4. Travel Stats
$stats_sql = "SELECT COUNT(DISTINCT booking_ref) as total_trips, SUM(final_amount) as total_spent 
              FROM (
                  SELECT booking_ref, MAX(final_amount) as final_amount 
                  FROM bookings 
                  WHERE passenger_id = '$passenger_id' 
                  GROUP BY booking_ref
              ) as subq";

$stats_res = mysqli_query($conn, $stats_sql);
$stats = ['total_trips' => 0, 'total_spent' => 0];

if ($stats_res) {
    $temp_stats = mysqli_fetch_assoc($stats_res);
    if ($temp_stats) { $stats = $temp_stats; }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Dashboard | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap');
        body { background-color: #0f172a; font-family: 'Outfit', sans-serif; }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .profile-border { border: 4px solid #3b82f6; }
        .nav-glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="text-slate-300 antialiased">

    <nav class="p-6 border-b border-white/5 nav-glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-900/20">
                    <i class="fas fa-bus text-xl"></i>
                </div>
                <h2 class="text-2xl font-black italic tracking-tighter text-white uppercase">Drive<span class="text-blue-500">Zone</span></h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <?php if($approved_count > 0): ?>
                <a href="my_hires.php" class="relative p-2 text-amber-500 bg-amber-500/10 rounded-xl animate-pulse group">
                    <i class="fas fa-bell"></i>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[8px] flex items-center justify-center rounded-full font-bold"><?= $approved_count ?></span>
                </a>
                <?php endif; ?>

                <a href="logout.php" class="w-10 h-10 flex items-center justify-center bg-red-500/10 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all border border-red-500/20">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 md:p-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            
            <div class="lg:col-span-4 space-y-6">
                <div class="glass-card rounded-[45px] p-10 text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-b from-blue-600/20 to-transparent"></div>
                    
                    <div class="relative inline-block mb-6 mt-4">
                        <img src="<?php echo (!empty($user['profile_image'])) ? $user['profile_image'] : 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']).'&background=3b82f6&color=fff'; ?>" 
                             class="w-32 h-32 rounded-[35px] object-cover profile-border shadow-2xl mx-auto bg-slate-800">
                    </div>
                    
                    <h3 class="text-xl font-black text-white"><?php echo $user['full_name']; ?></h3>
                    <p class="text-blue-500 font-bold text-[9px] uppercase tracking-[0.2em] mt-1">Verified Passenger</p>
                    
                    <div class="mt-8 pt-8 border-t border-white/5">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-3 italic">Last Ordinary Ticket</p>
                        <div class="bg-slate-900/50 p-4 rounded-2xl border <?php echo $status_color; ?>">
                            <span class="text-2xl font-black italic tracking-widest uppercase"><?php echo $latest_ref; ?></span>
                            <?php if($latest_trip): ?>
                                <p class="text-[9px] mt-2 text-slate-400 font-bold uppercase"><?= $latest_trip ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-500/20 to-orange-600/5 p-8 rounded-[40px] border border-amber-500/20">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-amber-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-amber-900/40 text-xl">
                            <i class="fas fa-star"></i>
                        </div>
                        <?php if($approved_count > 0): ?>
                            <span class="bg-red-500 text-white text-[8px] px-3 py-1 rounded-full font-black animate-bounce uppercase">Action Required</span>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-white font-black italic text-lg tracking-tight uppercase">Special Hire Status</h4>
                    <p class="text-slate-400 text-xs mt-1 italic">Check if owners have approved your hire requests.</p>
                    <a href="my_hires.php" class="inline-block mt-6 px-6 py-3 bg-white/5 hover:bg-white/10 rounded-xl text-[10px] font-black uppercase text-amber-500 tracking-widest transition-all">
                        Manage Hires <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-8 space-y-8">
                
                <div class="relative p-10 rounded-[50px] overflow-hidden bg-slate-800 border border-white/5 shadow-2xl">
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <h1 class="text-5xl font-black text-white italic tracking-tighter leading-tight uppercase">Welcome, <br><span class="text-blue-500"><?php echo explode(' ', $user['full_name'])[0]; ?>!</span></h1>
                        <p class="text-slate-400 mt-4 text-lg font-medium max-w-sm italic">Where would you like to go today?</p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-12 relative z-10">
                        <a href="booking.php" class="bg-blue-600 p-6 rounded-3xl text-center hover:scale-105 transition-all group shadow-lg shadow-blue-900/40">
                            <i class="fas fa-ticket-alt text-white text-xl mb-2 block"></i>
                            <span class="text-[8px] font-black text-white uppercase tracking-widest">Book Ticket</span>
                        </a>
                        <a href="tripbook.php" class="bg-amber-500 p-6 rounded-3xl text-center hover:scale-105 transition-all shadow-lg shadow-amber-900/30 group">
                            <i class="fas fa-bus-alt text-white text-xl mb-2 block"></i>
                            <span class="text-[8px] font-black text-white uppercase tracking-widest">Request Hire</span>
                        </a>
                        <a href="my_bookings.php" class="glass-card p-6 rounded-3xl text-center hover:border-blue-500 transition-all group">
                            <i class="fas fa-history text-blue-500 text-xl mb-2 block"></i>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Travel Log</span>
                        </a>
                        <a href="edit_profile.php" class="glass-card p-6 rounded-3xl text-center hover:border-emerald-500 transition-all group">
                            <i class="fas fa-user-cog text-emerald-500 text-xl mb-2 block"></i>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Settings</span>
                        </a>
                        <a href="sos.php" class="bg-red-600/10 border border-red-500/20 p-6 rounded-3xl text-center hover:bg-red-600 group transition-all">
                            <i class="fas fa-shield-alt text-red-500 group-hover:text-white text-xl mb-2 block"></i>
                            <span class="text-[8px] font-black text-red-500 group-hover:text-white uppercase tracking-widest">SOS</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="glass-card p-8 rounded-[40px]">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest italic">Recent Travel</h4>
                            <span class="text-[8px] bg-slate-700 px-3 py-1 rounded-full text-slate-300 uppercase font-bold"><?= $stats['total_trips'] ?> Trips</span>
                        </div>
                        <div class="space-y-6">
                            <?php
                            $recent_sql = "SELECT DISTINCT booking_ref, travel_date, pickup_point, drop_point FROM bookings WHERE passenger_id = '$passenger_id' ORDER BY created_at DESC LIMIT 3";
                            $recent_res = mysqli_query($conn, $recent_sql);
                            if($recent_res && mysqli_num_rows($recent_res) > 0):
                                while($row = mysqli_fetch_assoc($recent_res)): ?>
                                <div class="flex items-center justify-between group">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-10 h-10 rounded-2xl bg-slate-800 flex items-center justify-center text-blue-500 text-xs border border-white/5 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                            <i class="fas fa-route"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black text-white uppercase"><?= $row['pickup_point'] ?> → <?= $row['drop_point'] ?></p>
                                            <p class="text-[8px] text-slate-500 font-bold italic"><?= date("M d, Y", strtotime($row['travel_date'])) ?></p>
                                        </div>
                                    </div>
                                    <span class="text-[8px] font-bold text-slate-600 tracking-tighter">#<?= $row['booking_ref'] ?></span>
                                </div>
                            <?php endwhile; else: ?>
                                <p class="text-[10px] italic text-slate-600 text-center py-4 uppercase font-bold tracking-widest">No travel records yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="glass-card p-8 rounded-[40px] flex flex-col justify-center text-center relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 opacity-5 text-7xl text-blue-500">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-[0.3em] mb-1">Total Travel Spending</p>
                        <h4 class="text-4xl font-black text-white italic tracking-tighter">LKR <?= number_format($stats['total_spent']); ?></h4>
                        <div class="mt-6 flex justify-center gap-2">
                             <div class="w-8 h-1 bg-blue-500 rounded-full"></div>
                             <div class="w-4 h-1 bg-blue-500/40 rounded-full"></div>
                             <div class="w-2 h-1 bg-blue-500/10 rounded-full"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="p-10 text-center">
        <p class="text-[9px] font-black text-slate-600 uppercase tracking-[0.5em]">&copy; 2026 DriveZone System • Sri Lanka</p>
    </footer>

</body>
</html>