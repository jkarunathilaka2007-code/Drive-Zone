<?php
session_start();
require_once 'db_config.php';

// Passenger Login පරීක්ෂාව
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

// GET හරහා ලැබෙන දත්ත
$bus_id = $_GET['bus_id'] ?? '';
$from = $_GET['from'] ?? ''; 
$to = $_GET['to'] ?? '';     
$date = $_GET['date'] ?? '';
$fare = $_GET['fare'] ?? 0;
$route_id = $_GET['route_id'] ?? '';
$boarding_time = $_GET['boarding_time'] ?? '';
$arrival_time = $_GET['arrival_time'] ?? '';
$user_id = $_SESSION['user_id'];

// 1. Passenger ගේ Gender එක ලබා ගැනීම (Security වලට අත්‍යවශ්‍යයි)
$user_q = mysqli_query($conn, "SELECT gender FROM passengers WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$current_user_gender = strtolower($user_data['gender'] ?? 'male');

// 2. බස් එකේ සහ රූට් එකේ විස්තර ලබා ගැනීම
$bus_res = mysqli_query($conn, "SELECT b.*, r.route_number FROM buses b 
                                JOIN routes r ON b.id = r.bus_id 
                                WHERE b.id = '$bus_id' AND r.id = '$route_id'");
$bus = mysqli_fetch_assoc($bus_res);

if (!$bus) { die("Bus details not found! Please check if route_id is valid."); }

$total_rows = (int)$bus['total_rows'];
$left_side = (int)$bus['seats_per_column']; 
$right_side = (int)$bus['seats_per_row'];   
$has_conductor = (bool)$bus['has_conductor_seat'];
$last_row_count = (int)$bus['last_row_seats'];

// 3. හරියටම Date, Time සහ Route අනුව බුක් කර ඇති සීට් ලබා ගැනීම (No More Errors)
$booked_seats = [];
$check_bookings = mysqli_query($conn, "SELECT seat_number, gender FROM bookings 
                                       WHERE bus_id = '$bus_id' 
                                       AND route_id = '$route_id'
                                       AND travel_date = '$date' 
                                       AND boarding_time = '$boarding_time'
                                       AND status IN ('booked', 'pending')");

if (!$check_bookings) {
    die("Database Error: " . mysqli_error($conn));
}

while($row = mysqli_fetch_assoc($check_bookings)) {
    $booked_seats[$row['seat_number']] = strtolower($row['gender']);
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>DriveZone | Select Seats</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { background-color: #080b14; color: #f1f5f9; font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
        .seat { transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .seat.male { background-color: #3b82f6 !important; cursor: not-allowed; }
        .seat.female { background-color: #ec4899 !important; cursor: not-allowed; }
        .seat.selected { border: 2px solid white; transform: scale(1.1); box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); }
        .mode-card.active { border-color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
        .steering { transform: rotate(-20deg); }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="p-4 md:p-10">

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <div class="lg:col-span-3 space-y-4">
        <div class="glass p-6 rounded-[35px]">
            <h3 class="text-[10px] font-black uppercase text-slate-500 mb-6 tracking-widest text-center">Select Mode</h3>
            <div class="space-y-3">
                <button onclick="setMode('standard')" id="m-standard" class="mode-card active w-full p-4 rounded-2xl border border-slate-700 text-left transition">
                    <p class="font-black text-xs uppercase">Standard</p>
                    <p class="text-[9px] text-slate-500 italic">Security rules apply (Gender check)</p>
                </button>
                <button onclick="setMode('family')" id="m-family" class="mode-card w-full p-4 rounded-2xl border border-slate-700 text-left transition">
                    <p class="font-black text-xs uppercase text-emerald-400">Family / Couple</p>
                    <p class="text-[9px] text-slate-500 italic">No gender rules for groups</p>
                </button>
            </div>
        </div>

        <div class="glass p-6 rounded-[35px] grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-slate-800 rounded"></div><span class="text-[10px] font-bold">Free</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-blue-500 rounded"></div><span class="text-[10px] font-bold">Male</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-pink-500 rounded"></div><span class="text-[10px] font-bold">Female</span></div>
            <div class="flex items-center gap-2"><div class="w-3 h-3 border border-white rounded"></div><span class="text-[10px] font-bold">Your Selection</span></div>
        </div>
    </div>

    <div class="lg:col-span-5 glass p-6 md:p-10 rounded-[50px] flex flex-col items-center border-t-4 border-blue-600">
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-black uppercase italic leading-none text-white"><?= $bus['bus_number'] ?></h2>
            <p class="text-[10px] font-bold text-blue-500 tracking-widest mt-1 uppercase"><?= $bus['bus_type'] ?> • Route <?= $bus['route_number'] ?></p>
        </div>

        <div class="max-w-md w-full bg-slate-950 p-8 rounded-[60px] border-4 border-slate-800 shadow-2xl">
            <div class="flex justify-between items-center mb-10 pb-6 border-b-2 border-slate-800/50">
                <div class="w-10 h-10 rounded-full border-2 border-slate-700 flex items-center justify-center steering"><i class="fas fa-dharmachakra text-slate-600"></i></div>
                <div class="text-[8px] font-black text-slate-700 uppercase italic">Front Entrance</div>
                <div class="w-8 h-12 bg-slate-800 rounded-lg border border-slate-700"></div>
            </div>

            <div class="space-y-4 max-h-[550px] overflow-y-auto scrollbar-hide">
                <?php 
                $alphabet = range('A', 'Z');
                for ($r = 0; $r < $total_rows; $r++) {
                    $is_last = ($r == $total_rows - 1);
                    
                    if ($is_last) {
                        echo '<div class="pt-6 border-t border-slate-800 flex justify-center gap-2">';
                        for ($s = 1; $s <= $last_row_count; $s++) {
                            renderSeat($alphabet[$r].$s, $booked_seats);
                        }
                        echo '</div>';
                    } else {
                        echo '<div class="flex justify-between items-center gap-4">';
                        // Left Section
                        echo '<div class="flex gap-2">';
                        for ($s = 1; $s <= $left_side; $s++) {
                            $sid = $alphabet[$r].$s;
                            if ($r == 0 && $s == 1 && $has_conductor) {
                                echo "<div class='w-10 h-10 bg-orange-500/10 border border-orange-500/20 rounded-xl text-[7px] font-black text-orange-500 flex items-center justify-center uppercase text-center'>Cond</div>";
                            } else {
                                renderSeat($sid, $booked_seats);
                            }
                        }
                        echo '</div>';

                        // Aisle
                        echo '<div class="text-[7px] font-black text-slate-800 rotate-90 tracking-tighter">AISLE</div>';

                        // Right Section
                        echo '<div class="flex gap-2">';
                        for ($s = ($left_side + 1); $s <= ($left_side + $right_side); $s++) {
                            renderSeat($alphabet[$r].$s, $booked_seats);
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }

                function renderSeat($id, $booked) {
                    $gender = $booked[$id] ?? '';
                    $disabled = $gender ? 'disabled' : '';
                    echo "<button $disabled onclick='selectSeat(\"$id\")' id='seat-$id' class='seat $gender w-10 h-10 bg-slate-900 border border-slate-800 rounded-xl text-[10px] font-bold flex items-center justify-center hover:border-blue-500 transition-all'>$id</button>";
                }
                ?>
            </div>
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="glass p-8 rounded-[40px] sticky top-10 border-t border-white/10">
            <h3 class="text-xl font-black italic uppercase mb-6 tracking-tighter">Booking <span class="text-blue-500">Details</span></h3>
            
            <div class="bg-slate-950/50 p-6 rounded-3xl border border-white/5 space-y-4 mb-8 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Trip:</span>
                    <span class="text-white font-bold"><?= $from ?> <i class="fas fa-arrow-right mx-1 text-blue-600"></i> <?= $to ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Travel Date:</span>
                    <span class="text-white font-bold"><?= $date ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Departure:</span>
                    <span class="text-blue-500 font-black italic"><?= $boarding_time ?></span>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-800">
                    <span class="text-slate-500">Selected Seats:</span>
                    <span id="lbl-seats" class="font-black text-blue-400">---</span>
                </div>
                <div class="flex justify-between text-2xl font-black italic pt-4">
                    <span>Total:</span>
                    <span id="lbl-total" class="text-emerald-500">LKR 0</span>
                </div>
            </div>

            <form action="confirm_booking.php" method="POST" onsubmit="return checkPolicy()">
                <input type="hidden" name="bus_id" value="<?= $bus_id ?>">
                <input type="hidden" name="route_id" value="<?= $route_id ?>">
                <input type="hidden" name="travel_date" value="<?= $date ?>">
                <input type="hidden" name="from_town" value="<?= $from ?>">
                <input type="hidden" name="to_town" value="<?= $to ?>">
                <input type="hidden" name="boarding_time" value="<?= $boarding_time ?>">
                <input type="hidden" name="arrival_time" value="<?= $arrival_time ?>">
                <input type="hidden" name="package_name" id="in-mode" value="standard">
                <input type="hidden" name="selected_seats" id="in-seats">
                <input type="hidden" name="final_amount" id="in-total">
                <input type="hidden" name="user_gender" value="<?= $current_user_gender ?>">

                <button type="submit" id="btn-pay" disabled class="w-full bg-blue-600 hover:bg-blue-500 text-white py-5 rounded-[25px] font-black uppercase text-xs tracking-widest transition-all shadow-xl shadow-blue-500/20 disabled:opacity-20 active:scale-95">
                    Confirm & Reserve
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let mode = 'standard';
let selected = [];
const fare = <?= (float)$fare ?>;
const userGender = '<?= $current_user_gender ?>';
const booked = <?= json_encode($booked_seats) ?>;

function setMode(m) {
    mode = m; selected = [];
    document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('active'));
    document.getElementById('m-' + m).classList.add('active');
    document.querySelectorAll('.seat').forEach(s => { 
        if(!s.disabled) s.className = 'seat w-10 h-10 bg-slate-900 border border-slate-800 rounded-xl text-[10px] font-bold flex items-center justify-center hover:border-blue-500'; 
    });
    updateUI();
}

function selectSeat(id) {
    // SECURITY: Standard Mode Gender Check
    if(mode === 'standard' && !isSafe(id)) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Security Alert', 
            text: 'For safety reasons, you cannot book a seat next to the opposite gender in Standard Mode.',
            background: '#080b14', color: '#fff' 
        });
        return;
    }

    const idx = selected.indexOf(id);
    const btn = document.getElementById('seat-' + id);

    if(idx > -1) {
        selected.splice(idx, 1);
        btn.classList.remove('selected', 'bg-blue-500', 'bg-pink-500');
    } else {
        selected.push(id);
        btn.classList.add('selected');
        btn.classList.add(userGender === 'male' ? 'bg-blue-500' : 'bg-pink-500');
    }
    updateUI();
}

function isSafe(id) {
    const row = id.charAt(0);
    const col = parseInt(id.substring(1));
    const oppositeGender = (userGender === 'male') ? 'female' : 'male';
    
    // Logic for 2x2 or 2x3 layout adjacent seats
    let neighborCol = (col % 2 === 0) ? col - 1 : col + 1;
    let neighborId = row + neighborCol;

    if(booked[neighborId] && booked[neighborId] === oppositeGender) {
        return false;
    }
    return true;
}

function updateUI() {
    let total = selected.length * fare;
    document.getElementById('lbl-seats').innerText = selected.length ? selected.join(', ') : '---';
    document.getElementById('lbl-total').innerText = 'LKR ' + total.toLocaleString();
    
    document.getElementById('in-seats').value = selected.join(',');
    document.getElementById('in-total').value = total;
    document.getElementById('in-mode').value = mode;
    
    document.getElementById('btn-pay').disabled = selected.length === 0;
}

function checkPolicy() {
    if(selected.length === 0) return false;
    return true;
}
</script>
</body>
</html>