<?php
session_start();
require_once 'db_config.php';

// බස් ඕනර් කෙනෙක්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$bus_id = mysqli_real_escape_string($conn, $_GET['bus_id']);
$route_id = mysqli_real_escape_string($conn, $_GET['route_id']);

// 1. අදාළ Route සහ Bus දත්ත ලබා ගැනීම
$route_res = mysqli_query($conn, "SELECT * FROM routes WHERE id = '$route_id'");
$route = mysqli_fetch_assoc($route_res);

$bus_res = mysqli_query($conn, "SELECT bus_number FROM buses WHERE id = '$bus_id'");
$bus_data = mysqli_fetch_assoc($bus_res);

// 2. ගමන් මාර්ගයේ සියලුම නැවතුම් පොළවල් ලිස්ට් එකක් ලෙස සකස් කිරීම
$main_towns = json_decode($route['route_path_json'], true) ?: [];
$all_locations = [];
$all_locations[] = trim($route['start_point']);
foreach($main_towns as $t) { $all_locations[] = trim($t['name']); }
$all_locations[] = trim($route['end_point']);

// 3. මිල ගණන් සේව් කිරීම (මෙය අදාළ Route ID එකට පමණක් බලපායි)
if (isset($_POST['save_prices'])) {
    foreach ($_POST['fare'] as $key => $price) {
        if ($price === '') continue; 

        $parts = explode('_to_', $key);
        $from_t = str_replace('_', ' ', $parts[0]);
        $to_t = str_replace('_', ' ', $parts[1]);
        $price = (float)$price;

        // දැනටමත් මිලක් තිබේදැයි බලා Update හෝ Insert කිරීම
        $check = mysqli_query($conn, "SELECT id FROM ticket_prices WHERE route_id = '$route_id' AND from_town = '$from_t' AND to_town = '$to_t'");
        
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE ticket_prices SET price = '$price' WHERE route_id = '$route_id' AND from_town = '$from_t' AND to_town = '$to_t'");
        } else {
            mysqli_query($conn, "INSERT INTO ticket_prices (route_id, from_town, to_town, price) VALUES ('$route_id', '$from_t', '$to_t', '$price')");
        }
    }
    $success_msg = "Route pricing updated successfully!";
}

// 4. දැනට Database එකේ ඇති මිල ගණන් Load කිරීම
$existing_prices = [];
$price_res = mysqli_query($conn, "SELECT * FROM ticket_prices WHERE route_id = '$route_id'");
while ($row = mysqli_fetch_assoc($price_res)) {
    $key = str_replace(' ', '_', $row['from_town']) . "_to_" . str_replace(' ', '_', $row['to_town']);
    $existing_prices[$key] = $row['price'];
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Fare Management | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap');
        body { background-color: #080b14; font-family: 'Inter', sans-serif; color: #e2e8f0; }
        .glass { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="p-4 md:p-10">
    <div class="max-w-4xl mx-auto">
        
        <header class="glass p-8 rounded-[40px] flex justify-between items-center mb-10 border-t border-white/5 shadow-2xl">
            <div class="flex items-center gap-5">
                <a href="my_fleet.php" class="w-12 h-12 flex items-center justify-center bg-slate-900 rounded-2xl hover:bg-blue-600 transition-all border border-white/5">
                    <i class="fas fa-arrow-left text-white"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black uppercase italic tracking-tighter text-white">Fare <span class="text-blue-500">Config</span></h1>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bus: <?= $bus_data['bus_number'] ?> | Time: <?= date('h:i A', strtotime($route['start_time'])) ?></p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-4 py-1.5 bg-blue-600/10 border border-blue-500/20 rounded-full text-[10px] font-bold text-blue-400 uppercase tracking-widest">Route ID: #<?= $route_id ?></span>
            </div>
        </header>

        <?php if(isset($success_msg)): ?>
            <div class="mb-8 p-5 glass border-emerald-500/30 text-emerald-400 rounded-3xl text-center font-bold text-sm italic">
                <i class="fas fa-check-double mr-2"></i> <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="space-y-10">
                <?php for ($i = 0; $i < count($all_locations) - 1; $i++): $from_city = $all_locations[$i]; ?>
                <div class="glass rounded-[35px] overflow-hidden border border-white/5 shadow-xl">
                    <div class="px-8 py-5 bg-slate-900/50 border-b border-white/5 flex items-center">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mr-3"></div>
                        <h2 class="text-xs font-black uppercase tracking-widest text-slate-400">Departing From: <span class="text-white"><?= $from_city ?></span></h2>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php for ($j = $i + 1; $j < count($all_locations); $j++): $to_city = $all_locations[$j]; 
                              $key_name = str_replace(' ', '_', $from_city) . "_to_" . str_replace(' ', '_', $to_city); ?>
                        <div class="flex items-center justify-between bg-slate-800/30 p-4 rounded-2xl border border-white/5 hover:border-blue-500/20 transition-all group">
                            <span class="text-[11px] font-bold text-slate-400 uppercase"><?= $to_city ?></span>
                            
                            <div class="relative w-32">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[8px] font-black text-slate-600">LKR</span>
                                <input type="number" step="0.01" name="fare[<?= $key_name ?>]" 
                                       value="<?= $existing_prices[$key_name] ?? '' ?>" 
                                       placeholder="0.00"
                                       class="w-full bg-[#080b14] border border-slate-800 rounded-xl py-2.5 pl-9 pr-3 text-right text-sm font-bold text-blue-500 outline-none focus:border-blue-500 transition">
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endfor; ?>

                <div class="sticky bottom-8">
                    <button type="submit" name="save_prices" class="w-full bg-blue-600 hover:bg-white hover:text-blue-600 text-white font-black py-5 rounded-3xl uppercase tracking-[0.3em] shadow-2xl transition-all transform active:scale-95 text-xs">
                        <i class="fas fa-save mr-2"></i> Save Pricing Table
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="h-10"></div>
</body>
</html>