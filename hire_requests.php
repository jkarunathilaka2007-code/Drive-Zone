<?php
session_start();
require_once 'db_config.php';

// 1. Owner ලොග් වී ඇත්දැයි බැලීම
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

// 2. Status සහ Price Update කිරීම (Approve කිරීමේදී)
if (isset($_POST['update_status'])) {
    $req_id = mysqli_real_escape_string($conn, $_POST['req_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $price = isset($_POST['est_price']) ? floatval($_POST['est_price']) : 0;

    // total_amount column එකට කෙලින්ම මිල ඇතුළත් කිරීම
    $sql = "UPDATE trip_bookings SET status = '$status', total_amount = '$price' WHERE id = '$req_id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: hire_requests.php?success=1");
        exit();
    } else {
        $error_msg = "Error updating record: " . mysqli_error($conn);
    }
}

// 3. Hire Requests සහ අදාළ Bus/Staff/Passenger විස්තර ලබා ගැනීම
$sql = "SELECT tb.*, 
        b.bus_number, b.bus_type, b.model, b.brand,
        p.full_name as passenger_name, p.contact_number as passenger_phone,
        d.full_name as driver_name, d.contact_no as driver_phone,
        c.full_name as conductor_name, c.mobile_number as conductor_phone
        FROM trip_bookings tb
        JOIN buses b ON tb.bus_id = b.id
        JOIN passengers p ON tb.passenger_id = p.id
        LEFT JOIN drivers d ON b.id = d.bus_id
        LEFT JOIN conductors c ON b.id = c.bus_id
        WHERE b.owner_id = '$owner_id'
        ORDER BY tb.created_at DESC";

$requests = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hire Management | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&display=swap');
        body { background-color: #0f172a; font-family: 'Outfit', sans-serif; }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="text-slate-200 p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black italic tracking-tighter uppercase">Hire <span class="text-amber-500">Requests</span></h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Manage Special Bookings & Payments</p>
            </div>
            <a href="bus_owner_dashboard.php" class="bg-slate-800 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition">
                <i class="fas fa-chevron-left mr-2"></i> Dashboard
            </a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-2xl mb-6 text-xs font-bold uppercase tracking-widest flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i> Hire updated successfully!
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-6">
            <?php if(mysqli_num_rows($requests) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($requests)): 
                    $status = $row['status'];
                    $badge_color = "bg-slate-700 text-slate-400";
                    if($status == 'approved') $badge_color = "bg-amber-500/20 text-amber-500 border border-amber-500/10";
                    if($status == 'paid') $badge_color = "bg-emerald-500/20 text-emerald-500 border border-emerald-500/10";
                ?>
                <div class="glass-card rounded-[40px] p-6 md:p-10 transition hover:border-blue-500/30">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                        
                        <div class="space-y-4">
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-600 px-3 py-1 rounded-lg text-[10px] font-black uppercase"><?= $row['bus_number'] ?></span>
                                <span class="text-slate-500 text-[10px] font-bold uppercase"><?= $row['brand'] ?> <?= $row['model'] ?></span>
                            </div>
                            <h2 class="text-2xl font-black italic uppercase leading-tight">
                                <?= $row['pickup_location'] ?> <br>
                                <i class="fas fa-arrow-down text-amber-500 text-sm my-2 block"></i> 
                                <?= $row['final_destination'] ?>
                            </h2>
                            <div class="space-y-2 text-[11px] text-slate-400 font-bold uppercase tracking-wider">
                                <p><i class="fas fa-calendar w-5 text-blue-500"></i> <?= $row['start_date'] ?> to <?= $row['end_date'] ?></p>
                                <p><i class="fas fa-clock w-5 text-blue-500"></i> Start Time: <?= $row['start_time'] ?></p>
                                <p><i class="fas fa-users w-5 text-blue-500"></i> <?= $row['passenger_count'] ?> Passengers</p>
                            </div>
                        </div>

                        <div class="bg-slate-900/50 p-6 rounded-[30px] border border-white/5 flex flex-col justify-between">
                            <div>
                                <p class="text-[9px] font-black text-slate-500 uppercase mb-2 tracking-widest">Customer Information</p>
                                <p class="font-black text-white text-lg italic"><?= $row['passenger_name'] ?></p>
                                <p class="text-blue-400 text-sm font-bold tracking-[0.1em] mb-4"><?= $row['passenger_phone'] ?></p>
                                
                                <p class="text-[9px] font-black text-slate-500 uppercase mb-1 tracking-widest">Planned Route/Places</p>
                                <p class="text-xs text-slate-400 leading-relaxed italic"><?= !empty($row['visit_places']) ? $row['visit_places'] : 'No specific stops mentioned.' ?></p>
                            </div>
                            
                            <?php if($row['total_amount'] > 0): ?>
                            <div class="mt-6 pt-4 border-t border-white/5">
                                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Quoted Price</p>
                                <p class="text-xl font-black text-emerald-400">LKR <?= number_format($row['total_amount']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-col justify-center space-y-3">
                            <button onclick="showStaffModal('<?= $row['bus_number'] ?>', '<?= $row['bus_type'] ?>', '<?= addslashes($row['driver_name'] ?? 'Not Assigned') ?>', '<?= $row['driver_phone'] ?? '-' ?>', '<?= addslashes($row['conductor_name'] ?? 'Not Assigned') ?>', '<?= $row['conductor_phone'] ?? '-' ?>')" 
                                    class="w-full bg-slate-800 hover:bg-slate-700 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition">
                                <i class="fas fa-id-card-alt mr-2"></i> View Assigned Staff
                            </button>

                            <?php if($status == 'pending'): ?>
                                <button onclick="openPriceModal(<?= $row['id'] ?>)" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition shadow-lg shadow-blue-900/40">
                                    Review & Approve
                                </button>
                            <?php else: ?>
                                <div class="w-full py-4 rounded-2xl border border-white/10 bg-slate-900/50 text-center">
                                    <span class="text-[10px] font-black uppercase tracking-widest <?= $status == 'paid' ? 'text-emerald-500' : 'text-amber-500' ?>">
                                        <i class="fas <?= $status == 'paid' ? 'fa-check-double' : 'fa-clock' ?> mr-2"></i>Status: <?= $status ?>
                                    </span>
                                </div>
                                <?php if($status == 'paid'): ?>
                                    <p class="text-[8px] text-center text-emerald-500/50 font-bold uppercase tracking-widest">Payment Received</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-20 glass-card rounded-[40px]">
                    <p class="text-slate-500 font-bold uppercase tracking-widest">No hire requests found for your fleet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="staffModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
        <div class="bg-slate-900 border border-white/10 p-8 rounded-[40px] max-w-md w-full shadow-2xl">
            <h3 class="text-2xl font-black italic mb-6 uppercase text-blue-500" id="mBusNumber">Bus Info</h3>
            <div class="space-y-4">
                <div class="bg-slate-800/50 p-5 rounded-3xl border border-white/5 flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-500/20 text-blue-500 rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-user-tie"></i></div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Driver</p>
                        <p class="font-bold text-white" id="mDriverName">-</p>
                        <p class="text-xs text-blue-400 font-mono" id="mDriverPhone">-</p>
                    </div>
                </div>
                <div class="bg-slate-800/50 p-5 rounded-3xl border border-white/5 flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-500/20 text-emerald-500 rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-id-badge"></i></div>
                    <div>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Conductor</p>
                        <p class="font-bold text-white" id="mConductorName">-</p>
                        <p class="text-xs text-emerald-400 font-mono" id="mConductorPhone">-</p>
                    </div>
                </div>
            </div>
            <button onclick="closeStaffModal()" class="w-full mt-8 py-4 bg-slate-800 hover:bg-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-widest transition">Close</button>
        </div>
    </div>

    <div id="priceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-slate-900 p-8 rounded-[40px] max-w-sm w-full border border-white/10 shadow-2xl">
            <h3 class="text-xl font-black italic mb-2 uppercase">Estimate <span class="text-blue-500">Fare</span></h3>
            <p class="text-[9px] text-slate-500 font-bold uppercase mb-6 tracking-widest">Set total price for this journey</p>
            
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="req_id" id="modal_req_id">
                <input type="hidden" name="status" value="approved">
                
                <div class="relative">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 font-black text-xs uppercase italic">LKR</span>
                    <input type="number" name="est_price" required placeholder="0.00" class="w-full bg-slate-800 border-none py-4 pl-14 pr-6 rounded-2xl text-white font-black outline-none focus:ring-2 ring-blue-500/50">
                </div>
                
                <button type="submit" name="update_status" class="w-full bg-blue-600 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition hover:bg-blue-500 shadow-xl shadow-blue-900/30">Send to Passenger</button>
                <button type="button" onclick="document.getElementById('priceModal').classList.add('hidden')" class="w-full text-slate-500 text-[9px] font-black uppercase py-2 tracking-widest hover:text-white transition">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function showStaffModal(bus, type, dName, dPhone, cName, cPhone) {
            document.getElementById('mBusNumber').innerText = bus + " [" + type + "]";
            document.getElementById('mDriverName').innerText = dName;
            document.getElementById('mDriverPhone').innerText = dPhone;
            document.getElementById('mConductorName').innerText = cName;
            document.getElementById('mConductorPhone').innerText = cPhone;
            document.getElementById('staffModal').classList.remove('hidden');
        }
        function closeStaffModal() {
            document.getElementById('staffModal').classList.add('hidden');
        }
        function openPriceModal(id) {
            document.getElementById('modal_req_id').value = id;
            document.getElementById('priceModal').classList.remove('hidden');
        }
    </script>
</body>
</html>