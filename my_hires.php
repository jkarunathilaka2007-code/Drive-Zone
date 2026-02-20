<?php
session_start();
require_once 'db_config.php';

// 1. මගියා ලොග් වී ඇත්දැයි බැලීම
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$success_msg = "";

// 2. Payment එකක් සිදු කළ විට Status Update කිරීම
if (isset($_POST['pay_now'])) {
    $hire_id = mysqli_real_escape_string($conn, $_POST['hire_id']);
    // මූල්‍ය ගනුදෙනුව සාර්ථක යැයි උපකල්පනය කර Status එක 'paid' කරයි
    $update_sql = "UPDATE trip_bookings SET status = 'paid' WHERE id = '$hire_id' AND passenger_id = '$passenger_id'";
    if (mysqli_query($conn, $update_sql)) {
        $success_msg = "Payment successful! Your trip is now confirmed.";
    }
}

// 3. Hire Requests ලබා ගැනීම (බස් රථයේ විස්තරද සමඟ)
// සටහන: total_amount තවම database එකේ නැත්නම් query එක error වෙන්න පුළුවන් නිසා 
// මුලින්ම ALTER TABLE එක run කරලා ඉන්න.
$sql = "SELECT t.*, b.bus_number, b.brand, b.model 
        FROM trip_bookings t
        JOIN buses b ON t.bus_id = b.id
        WHERE t.passenger_id = '$passenger_id' 
        ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Hire Requests | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap');
        body { background-color: #0f172a; font-family: 'Outfit', sans-serif; }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="text-slate-300 p-4 md:p-10">

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-4xl font-black italic tracking-tighter uppercase">My <span class="text-amber-500">Hire</span> Requests</h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.3em]">Track your special journeys and payments</p>
            </div>
            <a href="passengerdashboard.php" class="px-6 py-3 bg-slate-800 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <?php if($success_msg): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-6 rounded-[30px] mb-8 flex items-center gap-4">
                <i class="fas fa-check-circle text-2xl"></i>
                <p class="font-bold uppercase text-xs tracking-widest"><?= $success_msg ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-6">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    // Status Handling
                    $status = $row['status'] ?? 'pending';
                    $amount = $row['total_amount'] ?? 0;
                    
                    $status_class = "bg-slate-800 text-slate-400";
                    if($status == 'approved') $status_class = "bg-amber-500/20 text-amber-500 border border-amber-500/20";
                    if($status == 'paid') $status_class = "bg-emerald-500/20 text-emerald-400 border border-emerald-500/20";
                    if($status == 'rejected') $status_class = "bg-red-500/20 text-red-500 border border-red-500/20";
                ?>
                <div class="glass-card rounded-[40px] p-8 overflow-hidden relative group transition-all hover:border-white/10">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-center">
                        
                        <div class="lg:col-span-1 border-r border-white/5 pr-4">
                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Route</span>
                            <h3 class="text-xl font-black text-white italic leading-tight mt-1">
                                <?= htmlspecialchars($row['pickup_location']) ?> <br>
                                <span class="text-blue-500">To</span> <?= htmlspecialchars($row['final_destination']) ?>
                            </h3>
                            <div class="mt-4 p-3 bg-slate-900/50 rounded-2xl border border-white/5 inline-block">
                                <p class="text-[9px] font-bold text-blue-400 uppercase tracking-tighter">
                                    <i class="fas fa-bus mr-1"></i> <?= htmlspecialchars($row['bus_number']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="lg:col-span-1 space-y-4">
                            <div>
                                <p class="text-[8px] text-slate-500 uppercase font-black mb-1">Trip Duration</p>
                                <p class="text-xs font-bold text-slate-300">
                                    <i class="far fa-calendar-alt text-blue-500 mr-2"></i>
                                    <?= $row['start_date'] ?> <span class="text-slate-600 px-1">/</span> <?= $row['end_date'] ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-[8px] text-slate-500 uppercase font-black mb-1">Group Details</p>
                                <p class="text-xs font-bold text-slate-300">
                                    <i class="fas fa-users text-blue-500 mr-2"></i>
                                    <?= $row['passenger_count'] ?> Members
                                </p>
                            </div>
                        </div>

                        <div class="lg:col-span-1">
                            <div class="inline-block px-4 py-1 rounded-full text-[9px] font-black uppercase <?= $status_class ?> mb-3">
                                <?= strtoupper($status) ?>
                            </div>
                            <p class="text-[8px] text-slate-500 uppercase font-black mb-1 tracking-widest">Total Estimated Fare</p>
                            <h4 class="text-2xl font-black text-white italic">
                                <?php if ($amount > 0): ?>
                                    <span class="text-blue-500">LKR</span> <?= number_format($amount) ?>
                                <?php else: ?>
                                    <span class="text-slate-600 italic">Calculating...</span>
                                <?php endif; ?>
                            </h4>
                        </div>

                        <div class="lg:col-span-1">
                            <?php if($status == 'approved' && $amount > 0): ?>
                                <form method="POST">
                                    <input type="hidden" name="hire_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="pay_now" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-2xl text-[10px] uppercase tracking-[0.2em] transition-all transform hover:scale-105 shadow-xl shadow-blue-900/40">
                                        Pay & Confirm <i class="fas fa-credit-card ml-2"></i>
                                    </button>
                                </form>
                            <?php elseif($status == 'paid'): ?>
                                <div class="bg-emerald-500/10 text-emerald-400 py-4 rounded-2xl text-[10px] font-black uppercase text-center border border-emerald-500/20">
                                    Booking Confirmed <i class="fas fa-check-double ml-2"></i>
                                </div>
                            <?php elseif($status == 'rejected'): ?>
                                <div class="bg-red-500/10 text-red-500 py-4 rounded-2xl text-[10px] font-black uppercase text-center border border-red-500/20">
                                    Request Rejected
                                </div>
                            <?php else: ?>
                                <div class="bg-slate-800/50 text-slate-500 py-4 rounded-2xl text-[10px] font-black uppercase text-center border border-white/5 italic">
                                    Review Pending
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                    
                    <div class="absolute bottom-0 left-0 h-1 bg-slate-800 w-full">
                        <?php 
                            $progress = "10%";
                            if($status == 'approved') $progress = "60%";
                            if($status == 'paid') $progress = "100%";
                        ?>
                        <div class="h-full bg-blue-600 transition-all duration-1000" style="width: <?= $progress ?>"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-20 glass-card rounded-[50px] border-dashed border-2 border-white/5">
                    <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-folder-open text-3xl text-slate-600"></i>
                    </div>
                    <h3 class="text-xl font-black text-white uppercase italic">No Hires Found</h3>
                    <p class="text-slate-500 text-xs mt-2 uppercase tracking-widest font-bold">You haven't requested any special trips yet.</p>
                    <a href="trip_requests.php" class="mt-8 inline-block bg-blue-600/10 text-blue-500 px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all">
                        Create New Request
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>