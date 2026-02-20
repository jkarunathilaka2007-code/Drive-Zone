<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

// POST හරහා එන දත්ත ලබා ගැනීම
$user_id = $_SESSION['user_id'];
$bus_id = $_POST['bus_id'] ?? '';
$route_id = $_POST['route_id'] ?? '';
$travel_date = $_POST['travel_date'] ?? '';
$seats_str = $_POST['selected_seats'] ?? '';
$total_price = $_POST['final_amount'] ?? 0;
$package_name = $_POST['package_name'] ?? 'standard';
$boarding_time = $_POST['boarding_time'] ?? '';
$arrival_time = $_POST['arrival_time'] ?? '';
$from_place = $_POST['from_town'] ?? ''; 
$to_place = $_POST['to_town'] ?? '';
$gender = $_POST['user_gender'] ?? 'male';

// අද්විතීය බුකින් රෙෆරන්ස් එකක් සෑදීම
$booking_ref = "DZ-" . strtoupper(substr(md5(time() . $user_id . $seats_str), 0, 6));
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>DriveZone | Confirm Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { background-color: #080b14; color: #f1f5f9; font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="max-w-md w-full">
    <div class="glass rounded-[45px] overflow-hidden shadow-2xl text-center p-8 border-t border-white/10">
        <div class="w-20 h-20 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-500/20">
            <i class="fas fa-check-circle text-3xl"></i>
        </div>
        
        <h1 class="text-2xl font-black uppercase italic tracking-tighter text-white">Final Confirmation</h1>
        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Please review before payment</p>

        <div class="my-6 p-6 bg-slate-950/50 rounded-[30px] border border-blue-500/10">
            <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-1">Booking Reference</p>
            <h2 class="text-4xl font-black text-white tracking-widest uppercase italic"><?= $booking_ref ?></h2>
        </div>

        <div class="bg-slate-950/30 rounded-3xl p-6 text-left space-y-4 mb-8 border border-white/5">
            <div class="flex justify-between items-center">
                <span class="text-slate-500 text-[10px] font-black uppercase italic">Journey</span>
                <span class="text-white font-bold text-xs"><?= $from_place ?> <i class="fas fa-arrow-right text-blue-500 mx-1"></i> <?= $to_place ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500 text-[10px] font-black uppercase italic">Date & Time</span>
                <span class="text-white font-bold text-xs"><?= $travel_date ?> • <span class="text-blue-400"><?= $boarding_time ?></span></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500 text-[10px] font-black uppercase italic">Seats Selected</span>
                <span class="text-pink-500 font-black text-sm"><?= $seats_str ?></span>
            </div>
            <div class="pt-3 border-t border-white/5 flex justify-between items-center">
                <span class="text-slate-500 text-[10px] font-black uppercase italic">Total Payable</span>
                <span class="text-emerald-400 font-black text-xl italic">LKR <?= number_format((float)$total_price, 2) ?></span>
            </div>
        </div>

        <button id="done-btn" onclick="saveBooking()" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-5 rounded-[25px] font-black uppercase text-xs tracking-widest transition-all shadow-xl shadow-blue-600/20 flex items-center justify-center gap-3 active:scale-95">
            <span id="btn-text">PAY & CONFIRM</span>
            <i id="btn-icon" class="fas fa-arrow-right"></i>
        </button>
        
        <button onclick="window.history.back()" class="mt-4 text-slate-600 text-[10px] font-bold uppercase hover:text-white transition">Go Back</button>
        <p id="error-msg" class="text-red-500 text-[10px] font-black mt-4 uppercase hidden">Error: Could not save booking. Try again.</p>
    </div>
</div>

<script>
function saveBooking() {
    const btn = document.getElementById('done-btn');
    const btnText = document.getElementById('btn-text');
    const btnIcon = document.getElementById('btn-icon');
    
    btn.disabled = true;
    btn.classList.add('opacity-50');
    btnText.innerText = "PROCESSING...";
    btnIcon.className = "fas fa-circle-notch animate-spin";

    $.ajax({
        url: 'process_save_booking.php',
        type: 'POST',
        data: {
            bus_id: '<?= $bus_id ?>',
            route_id: '<?= $route_id ?>',
            travel_date: '<?= $travel_date ?>',
            boarding_time: '<?= $boarding_time ?>',
            arrival_time: '<?= $arrival_time ?>',
            selected_seats: '<?= $seats_str ?>',
            final_amount: '<?= $total_price ?>',
            booking_ref: '<?= $booking_ref ?>',
            package_name: '<?= $package_name ?>',
            pickup_point: '<?= $from_place ?>',
            drop_point: '<?= $to_place ?>',
            gender: '<?= $gender ?>'
        },
        success: function(response) {
            console.log(response); // Debugging සඳහා
            if(response.trim() === "success") {
                window.location.href = 'my_bookings.php?status=success';
            } else {
                showError();
            }
        },
        error: function() {
            showError();
        }
    });
}

function showError() {
    const btn = document.getElementById('done-btn');
    btn.disabled = false;
    btn.classList.remove('opacity-50');
    document.getElementById('btn-text').innerText = "PAY & CONFIRM";
    document.getElementById('btn-icon').className = "fas fa-arrow-right";
    document.getElementById('error-msg').classList.remove('hidden');
}
</script>

</body>
</html>