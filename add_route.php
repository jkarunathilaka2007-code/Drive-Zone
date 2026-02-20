<?php
session_start();
require_once 'db_config.php';

// 1. Owner Security Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['bus_id'])) {
    header("Location: my_fleet.php");
    exit();
}

$bus_id = mysqli_real_escape_string($conn, $_GET['bus_id']);
$owner_id = $_SESSION['user_id'];

// 2. Fetch Available Crew (දැනට වෙනත් බස් වලට assign වී නැති අය පමණි)
$drivers_query = "SELECT id, full_name FROM drivers 
                  WHERE owner_id = '$owner_id' AND status = 'approved' 
                  AND (bus_id IS NULL OR bus_id = '0' OR bus_id = '$bus_id')";
$drivers = mysqli_query($conn, $drivers_query);

$conductors_query = "SELECT id, full_name FROM conductors 
                     WHERE owner_id = '$owner_id' AND status = 'approved' 
                     AND (bus_id IS NULL OR bus_id = '0' OR bus_id = '$bus_id')";
$conductors = mysqli_query($conn, $conductors_query);

$msg = ""; $error_msg = "";

// 3. FULL SAVING PROCESS
if (isset($_POST['save_full_route'])) {
    $d_id = mysqli_real_escape_string($conn, $_POST['driver_id']);
    $c_id = mysqli_real_escape_string($conn, $_POST['conductor_id']);
    $route_no = mysqli_real_escape_string($conn, $_POST['route_no']);
    $start_p = mysqli_real_escape_string($conn, $_POST['start_point']);
    $end_p = mysqli_real_escape_string($conn, $_POST['end_point']);

    // Town JSON logic
    $town_data = [];
    if(isset($_POST['town_names'])){
        foreach($_POST['town_names'] as $idx => $name){
            if(!empty($name)) {
                $town_data[] = [
                    'name' => mysqli_real_escape_string($conn, $name), 
                    'duration' => (int)$_POST['town_durations'][$idx]
                ];
            }
        }
    }
    $towns_json = json_encode($town_data);
    $reverse_json = json_encode(array_reverse($town_data));

    mysqli_begin_transaction($conn);
    try {
        // --- STEP A: ROUTE PACK AUTO-SAVE LOGIC ---
        $check_pack = mysqli_query($conn, "SELECT id FROM route_packs WHERE route_number = '$route_no' LIMIT 1");
        if (mysqli_num_rows($check_pack) == 0) {
            // Master Table එකේ නැතිනම් අලුතින් සේව් කිරීම
            mysqli_query($conn, "INSERT INTO route_packs (route_number, start_point, end_point, route_path_json) 
                                 VALUES ('$route_no', '$start_p', '$end_p', '$towns_json')");
        }

        // --- STEP B: CLEAR OLD RECORDS ---
        mysqli_query($conn, "DELETE FROM routes WHERE bus_id = '$bus_id'");

        // --- STEP C: PROCESS TIMETABLE (QUICK / MANUAL) ---
        $apply_all = isset($_POST['apply_all_days']);
        $days_list = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $last_route_id = 0;

        if ($apply_all) {
            $active_days = isset($_POST['active_days']) ? implode(',', $_POST['active_days']) : implode(',', $days_list);
            if(isset($_POST['trips_all'])){
                foreach($_POST['trips_all'] as $t){
                    $path = ($t['type'] == 'forward') ? $towns_json : $reverse_json;
                    $sp = ($t['type'] == 'forward') ? $start_p : $end_p;
                    $ep = ($t['type'] == 'forward') ? $end_p : $start_p;
                    mysqli_query($conn, "INSERT INTO routes (owner_id, bus_id, route_number, start_point, end_point, start_time, destination_time, route_path_json, schedule_days) 
                                        VALUES ('$owner_id', '$bus_id', '$route_no', '$sp', '$ep', '{$t['start']}', '{$t['end']}', '$path', '$active_days')");
                    $last_route_id = mysqli_insert_id($conn);
                }
            }
        } else {
            foreach ($days_list as $day) {
                if(isset($_POST['trips_day'][$day])){
                    foreach($_POST['trips_day'][$day] as $t){
                        $path = ($t['type'] == 'forward') ? $towns_json : $reverse_json;
                        $sp = ($t['type'] == 'forward') ? $start_p : $end_p;
                        $ep = ($t['type'] == 'forward') ? $end_p : $start_p;
                        mysqli_query($conn, "INSERT INTO routes (owner_id, bus_id, route_number, start_point, end_point, start_time, destination_time, route_path_json, schedule_days) 
                                            VALUES ('$owner_id', '$bus_id', '$route_no', '$sp', '$ep', '{$t['start']}', '{$t['end']}', '$path', '$day')");
                        $last_route_id = mysqli_insert_id($conn);
                    }
                }
            }
        }

        // --- STEP D: CREW ALLOCATION & BUS UPDATE ---
        // පරණ හිටපු අයව reset කිරීම
        mysqli_query($conn, "UPDATE drivers SET bus_id = NULL WHERE bus_id = '$bus_id'");
        mysqli_query($conn, "UPDATE conductors SET bus_id = NULL WHERE bus_id = '$bus_id'");
        
        // අලුත් අයට bus_id එක අසයින් කිරීම
        mysqli_query($conn, "UPDATE drivers SET bus_id = '$bus_id' WHERE id = '$d_id'");
        mysqli_query($conn, "UPDATE conductors SET bus_id = '$bus_id' WHERE id = '$c_id'");
        
        // බස් රථයේ තොරතුරු යාවත්කාලීන කිරීම
        mysqli_query($conn, "UPDATE buses SET route_id = '$last_route_id', driver_id = '$d_id', conductor_id = '$c_id', status = 'active' WHERE id = '$bus_id'");

        mysqli_commit($conn);
        $msg = "Success: Route Deployed and Master Record Updated!";
        header("Refresh:2; url=my_fleet.php");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}
$bus_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT bus_number FROM buses WHERE id = '$bus_id'"));
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Deployment | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { background-color: #0b0f1a; font-family: 'Outfit', sans-serif; color: #cbd5e1; }
        .glass-card { background: rgba(23, 32, 51, 0.6); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        <form action="" method="POST">
            <header class="sticky top-0 z-50 glass-card p-6 rounded-[32px] mb-10 flex flex-col md:flex-row justify-between items-center gap-6 border-b border-blue-500/20">
                <div class="flex items-center gap-6">
                    <a href="my_fleet.php" class="w-12 h-12 flex items-center justify-center bg-slate-800 rounded-2xl hover:bg-blue-600 transition"><i class="fas fa-arrow-left text-white"></i></a>
                    <div>
                        <h1 class="text-2xl font-black uppercase text-white italic tracking-tighter tracking-widest">Assign <span class="text-blue-500">Route</span></h1>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bus: <span class="text-blue-400"><?= $bus_info['bus_number'] ?></span></p>
                    </div>
                </div>
                <button type="submit" name="save_full_route" class="bg-blue-600 hover:bg-blue-500 text-white px-10 py-4 rounded-2xl font-black uppercase text-[11px] tracking-widest transition shadow-xl">Deploy Everywhere</button>
            </header>

            <?php if($msg): ?>
                <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-2xl text-center font-bold text-xs uppercase tracking-widest">
                    <i class="fas fa-check-circle mr-2"></i> <?= $msg ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-4 space-y-6">
                    <div class="glass-card p-6 rounded-[32px] border-l-4 border-blue-600">
                        <label class="text-[10px] font-black text-blue-400 uppercase mb-2 block">Search Master Packs</label>
                        <div class="flex gap-2">
                            <input type="text" id="pack_search" placeholder="Route No (e.g. 138)" class="w-full bg-slate-900 border border-slate-700 p-3 rounded-xl text-white outline-none focus:border-blue-500">
                            <button type="button" onclick="searchPack()" class="bg-blue-600 px-4 rounded-xl hover:bg-blue-500"><i class="fas fa-search"></i></button>
                        </div>
                        <p id="search_feedback" class="mt-2 text-[10px] font-bold hidden italic"></p>
                    </div>

                    <div class="glass-card p-8 rounded-[40px]">
                        <h2 class="text-xs font-black uppercase text-blue-500 mb-6 italic tracking-widest">1. Master Data</h2>
                        <div class="space-y-4">
                            <input type="text" name="route_no" id="rn_f" required placeholder="Route Number" class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold border border-slate-700">
                            <input type="text" name="start_point" id="sp_f" required placeholder="Starting Town" class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold border border-slate-700">
                            <input type="text" name="end_point" id="ep_f" required placeholder="Destination" class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold border border-slate-700">
                            
                            <select name="driver_id" required class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold text-slate-400 border border-slate-700 outline-none">
                                <option value="">Select Driver</option>
                                <?php mysqli_data_seek($drivers,0); while($d = mysqli_fetch_assoc($drivers)) echo "<option value='{$d['id']}'>{$d['full_name']}</option>"; ?>
                            </select>
                            
                            <select name="conductor_id" required class="w-full bg-slate-800/50 p-4 rounded-2xl font-bold text-slate-400 border border-slate-700 outline-none">
                                <option value="">Select Conductor</option>
                                <?php mysqli_data_seek($conductors,0); while($c = mysqli_fetch_assoc($conductors)) echo "<option value='{$c['id']}'>{$c['full_name']}</option>"; ?>
                            </select>
                        </div>
                    </div>

                    <div class="glass-card p-8 rounded-[40px]">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xs font-black uppercase text-emerald-500 italic">2. Intermediate Stops</h2>
                            <button type="button" onclick="addStop()" class="text-emerald-500 text-xl"><i class="fas fa-plus-circle"></i></button>
                        </div>
                        <div id="stop-list" class="space-y-3"></div>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="glass-card p-8 rounded-[40px]">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-slate-800 pb-6 gap-4">
                            <h2 class="text-xs font-black uppercase text-blue-500 italic">3. Trip Timetable</h2>
                            <label class="flex items-center gap-3 bg-blue-500/10 px-6 py-3 rounded-2xl border border-blue-500/20 cursor-pointer">
                                <input type="checkbox" name="apply_all_days" id="toggle_mode" checked onchange="toggleUI()" class="w-5 h-5 rounded text-blue-600 bg-slate-800">
                                <span class="text-[10px] font-black uppercase text-blue-400">Daily Consistent Mode</span>
                            </label>
                        </div>

                        <div id="quick_ui">
                            <div class="flex flex-wrap gap-3 mb-8 bg-slate-900/40 p-4 rounded-2xl border border-slate-800/50">
                                <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                                    <label class="flex items-center gap-2 text-[9px] font-black uppercase bg-slate-800/50 px-3 py-2 rounded-xl">
                                        <input type="checkbox" name="active_days[]" value="<?= $day ?>" checked> <?= substr($day,0,3) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div id="quick_container" class="space-y-4"></div>
                            <button type="button" onclick="addTrip('quick')" class="w-full mt-6 py-5 border-2 border-dashed border-slate-800 rounded-3xl text-[10px] font-black uppercase text-slate-500 hover:border-blue-500/50 transition">+ Add Global Trip</button>
                        </div>

                        <div id="manual_ui" class="hidden space-y-8">
                            <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                                <div class="bg-slate-900/30 p-6 rounded-[32px] border border-slate-800">
                                    <h4 class="text-[10px] font-black text-blue-500 uppercase mb-4"><?= $day ?> Schedule</h4>
                                    <div id="manual_container_<?= $day ?>" class="space-y-3"></div>
                                    <button type="button" onclick="addTrip('<?= $day ?>')" class="mt-4 text-[9px] font-bold text-slate-600 hover:text-white transition">+ Add Trip for <?= $day ?></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    

    <script>
        let tripCounter = 0;

        function toggleUI() {
            const isQuick = document.getElementById('toggle_mode').checked;
            document.getElementById('quick_ui').classList.toggle('hidden', !isQuick);
            document.getElementById('manual_ui').classList.toggle('hidden', isQuick);
        }

        function addTrip(target) {
            const container = (target === 'quick') ? document.getElementById('quick_container') : document.getElementById(`manual_container_${target}`);
            const fieldPrefix = (target === 'quick') ? `trips_all[${tripCounter}]` : `trips_day[${target}][${tripCounter}]`;
            
            const div = document.createElement('div');
            div.className = "flex flex-col md:flex-row gap-4 bg-slate-800/30 p-4 rounded-2xl items-center border border-white/5 animate-in fade-in";
            div.innerHTML = `
                <select name="${fieldPrefix}[type]" class="bg-slate-900 p-2 rounded-xl text-[10px] font-black uppercase text-white outline-none">
                    <option value="forward">Forward</option><option value="return">Return</option>
                </select>
                <div class="flex items-center gap-2 flex-1 w-full">
                    <span class="text-[8px] font-black text-slate-600 uppercase">Depart</span>
                    <input type="time" name="${fieldPrefix}[start]" value="06:00" class="flex-1 bg-slate-900 p-2 rounded-xl text-xs text-blue-400 font-bold outline-none">
                </div>
                <div class="flex items-center gap-2 flex-1 w-full">
                    <span class="text-[8px] font-black text-slate-600 uppercase">Arrive</span>
                    <input type="time" name="${fieldPrefix}[end]" value="08:00" class="flex-1 bg-slate-900 p-2 rounded-xl text-xs text-emerald-400 font-bold outline-none">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-600 hover:text-red-500"><i class="fas fa-trash"></i></button>
            `;
            container.appendChild(div);
            tripCounter++;
        }

        function addStop(name = '', mins = '') {
            const div = document.createElement('div');
            div.className = "flex items-center gap-2 bg-slate-800/40 p-3 rounded-xl border border-white/5";
            div.innerHTML = `
                <input type="text" name="town_names[]" value="${name}" placeholder="Town Name" class="flex-1 bg-transparent text-xs font-bold outline-none text-white">
                <input type="number" name="town_durations[]" value="${mins}" placeholder="Mins" class="w-14 bg-slate-900 p-2 rounded-lg text-center text-xs text-emerald-500 font-black outline-none border-none">
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-500"><i class="fas fa-times"></i></button>
            `;
            document.getElementById('stop-list').appendChild(div);
        }

        async function searchPack() {
            const val = document.getElementById('pack_search').value;
            const fb = document.getElementById('search_feedback');
            if(!val) return;
            fb.innerText = "Searching..."; fb.className = "text-blue-400 block mt-2 text-[10px]"; fb.classList.remove('hidden');

            try {
                const response = await fetch(`api_get_route_pack.php?route_no=${val}`);
                const data = await response.json();
                if(data.success) {
                    fb.innerText = "Master Data Loaded!"; fb.className = "text-emerald-500 block mt-2 text-[10px]";
                    document.getElementById('rn_f').value = data.route_number;
                    document.getElementById('sp_f').value = data.start_point;
                    document.getElementById('ep_f').value = data.end_point;
                    document.getElementById('stop-list').innerHTML = '';
                    JSON.parse(data.route_path_json).forEach(t => addStop(t.name, t.duration));
                } else {
                    fb.innerText = "Route not found in Master!"; fb.className = "text-red-500 block mt-2 text-[10px]";
                }
            } catch (e) { fb.innerText = "Error fetching data"; }
        }

        window.onload = () => { addTrip('quick'); addStop(); };
    </script>
</body>
</html>