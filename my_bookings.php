<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];

// Query එක: බුකින්ස් ලබා ගැනීම
$query = "SELECT b.*, bus.bus_number, bus.bus_type 
          FROM bookings b
          JOIN buses bus ON b.bus_id = bus.id
          WHERE b.passenger_id = '$passenger_id'
          ORDER BY b.travel_date DESC, b.boarding_time ASC";

$result = mysqli_query($conn, $query);

$pending_bookings = [];
$verified_bookings = [];

// මෙතනදී අපි එකම Reference එක යටතේ ඇති සීට් ගෲප් කරනවා
while($row = mysqli_fetch_assoc($result)) {
    $ref = $row['booking_ref'];
    $is_verified = $row['is_verified'];

    if($is_verified == 1) {
        if(!isset($verified_bookings[$ref])) {
            $verified_bookings[$ref] = $row;
            $verified_bookings[$ref]['seats'] = [$row['seat_number']];
        } else {
            $verified_bookings[$ref]['seats'][] = $row['seat_number'];
        }
    } else {
        if(!isset($pending_bookings[$ref])) {
            $pending_bookings[$ref] = $row;
            $pending_bookings[$ref]['seats'] = [$row['seat_number']];
        } else {
            $pending_bookings[$ref]['seats'][] = $row['seat_number'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tickets | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { background-color: #080b14; color: #cbd5e1; font-family: 'Outfit', sans-serif; }
        .glass-card { background: rgba(17, 24, 39, 0.6); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
        .tab-active { background: #3b82f6 !important; color: white !important; }
        .ticket-cut { width: 30px; height: 30px; background: #080b14; border-radius: 50%; position: absolute; top: 60%; transform: translateY(-50%); }
    </style>
</head>
<body class="min-h-screen pb-10">

    <nav class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-xl border-b border-white/5 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="passengerdashboard.php" class="text-slate-500 hover:text-white text-[10px] font-black uppercase tracking-widest"><i class="fas fa-arrow-left"></i> Dashboard</a>
            <h1 class="text-sm font-black uppercase tracking-[5px] italic">MY <span class="text-blue-500">TICKETS</span></h1>
            <div class="w-10"></div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 mt-6">
        <div class="flex justify-center mb-10">
            <div class="flex bg-slate-900/50 rounded-2xl p-1 border border-white/5 w-full max-w-sm">
                <button onclick="switchTab('pending')" id="btn-pending" class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest rounded-xl tab-active">Upcoming</button>
                <button onclick="switchTab('verified')" id="btn-verified" class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest rounded-xl text-slate-500">Verified</button>
            </div>
        </div>

        <div id="pending-section" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if(empty($pending_bookings)) echo "<p class='col-span-full text-center opacity-20 py-20 uppercase font-black tracking-widest'>No upcoming trips</p>"; ?>
            <?php foreach($pending_bookings as $b) renderTicket($b, 'blue'); ?>
        </div>

        <div id="verified-section" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($verified_bookings as $b) renderTicket($b, 'emerald'); ?>
        </div>
    </main>

    <script>
        function switchTab(tab) {
            document.getElementById('pending-section').classList.toggle('hidden', tab !== 'pending');
            document.getElementById('verified-section').classList.toggle('hidden', tab !== 'verified');
            document.getElementById('btn-pending').classList.toggle('tab-active', tab === 'pending');
            document.getElementById('btn-verified').classList.toggle('tab-active', tab === 'verified');
        }

        function cancelBooking(ref) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to cancel this booking ("+ref+")?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#1e293b',
                confirmButtonText: 'Yes, Cancel it!',
                background: '#0f172a',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cancel_booking.php?ref=' + ref;
                }
            });
        }
    </script>
</body>
</html>

<?php
function renderTicket($b, $color) {
    $date = date('M d, Y', strtotime($b['travel_date']));
    $seats = implode(', ', $b['seats']); // සීට් ටික එකට පෙන්වීම
?>
    <div class="glass-card rounded-[40px] overflow-hidden relative border border-white/5">
        <div class="bg-<?= $color ?>-500/10 px-8 py-3 flex justify-between items-center border-b border-white/5">
            <span class="text-[9px] font-black text-white italic tracking-widest uppercase"><?= $b['package_name'] ?></span>
            <span class="text-[9px] font-bold text-slate-500 uppercase"><?= $b['bus_number'] ?></span>
        </div>

        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-black text-white uppercase"><?= $b['pickup_point'] ?></h2>
                    <p class="text-[10px] font-bold text-<?= $color ?>-500"><?= $b['boarding_time'] ?></p>
                </div>
                <i class="fas fa-arrow-right text-slate-700"></i>
                <div class="text-right">
                    <h2 class="text-xl font-black text-white uppercase"><?= $b['drop_point'] ?></h2>
                    <p class="text-[10px] font-bold text-slate-400"><?= $b['arrival_time'] ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 py-4 border-t border-white/5">
                <div>
                    <p class="text-[8px] font-black text-slate-600 uppercase">Seats</p>
                    <p class="text-sm font-black text-blue-500"><?= $seats ?></p>
                </div>
                <div class="text-right">
                    <p class="text-[8px] font-black text-slate-600 uppercase">Date</p>
                    <p class="text-[10px] font-black text-slate-300 uppercase italic"><?= $date ?></p>
                </div>
            </div>

            <?php if($color === 'blue'): // Pending නම් පමණක් Cancel Button එක පෙන්වන්න ?>
            <button onclick="cancelBooking('<?= $b['booking_ref'] ?>')" class="mt-4 w-full bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                Cancel Booking
            </button>
            <?php endif; ?>
        </div>

        <div class="ticket-cut -left-4"></div>
        <div class="ticket-cut -right-4"></div>
        
        <div class="bg-slate-950/80 p-5 flex justify-between items-center">
            <div>
                <p class="text-[7px] font-black text-slate-600 uppercase">Reference</p>
                <p class="text-[10px] font-mono font-bold text-<?= $color ?>-400"><?= $b['booking_ref'] ?></p>
            </div>
            <div class="text-right">
                <p class="text-[7px] font-black text-slate-600 uppercase">Total Fare</p>
                <p class="text-[11px] font-black text-emerald-500">LKR <?= number_format($b['fare'], 2) ?></p>
            </div>
        </div>
    </div>
<?php } ?>