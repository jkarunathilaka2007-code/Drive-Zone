<?php
session_start();
require_once 'db_config.php';

// 1. Access Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$logged_in_driver_id = $_SESSION['user_id'];

// 2. Fetch Data (මෙතන Query එක ඔයාගේ කලින් එකටම සමානයි, මම job_status එකත් ගත්තා)
$sql = "SELECT d.*, o.owner_name 
        FROM drivers d 
        LEFT JOIN bus_owners o ON d.owner_id = o.id 
        WHERE d.id = '$logged_in_driver_id' LIMIT 1";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $d = mysqli_fetch_assoc($result);
    
    // ඔයාගේ මුල් Variables ටික එහෙම්මම තියෙනවා
    $name       = $d['full_name'];
    $email      = $d['email'];
    $phone      = $d['contact_no'];
    $nic        = $d['nic_number'];
    $address    = $d['address'];
    $license    = $d['license_no'];
    $exp        = $d['experience_years'];
    $status     = $d['status'];
    $dob        = $d['dob'];
    $e_contact  = $d['emergency_contact'];
    $owner_name = $d['owner_name'] ?? 'Not Assigned';
    
    // අලුතින් එක් කළ Job Status variable එක
    $job_status = $d['job_status'] ?? 'deactive'; 
    
    $profile_img = $d['driver_photo']; 
    $license_img = $d['license_photo'];

    // දැනට මේ driver වැඩ කරන බස් එකේ අංකය හොයාගැනීමේ අමතර query එකක්
    $bus_check = mysqli_query($conn, "SELECT bus_number FROM buses WHERE driver_id = '$logged_in_driver_id' LIMIT 1");
    $bus_data = mysqli_fetch_assoc($bus_check);
    $assigned_bus = $bus_data['bus_number'] ?? 'Not Assigned to a Bus';

} else {
    echo "<div style='text-align:center; padding:50px;'><h2>Driver Record Not Found! (ID: $logged_in_driver_id)</h2><a href='logout.php'>Logout</a></div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans antialiased">

    <div class="flex flex-col md:flex-row min-h-screen">
        <aside class="w-full md:w-72 bg-slate-900 text-white p-8 flex flex-col">
            <h1 class="text-2xl font-black italic tracking-tighter uppercase mb-12">Drive<span class="text-blue-500">Zone</span></h1>
            <nav class="space-y-3 flex-1">
                <a href="#" class="flex items-center space-x-4 p-4 bg-blue-600 rounded-2xl font-bold shadow-lg shadow-blue-900/20">
                    <i class="fas fa-user-circle"></i> <span>My Profile</span>
                </a>
                <a href="logout.php" class="flex items-center space-x-4 p-4 text-red-400 hover:bg-red-500/10 rounded-2xl font-bold transition mt-auto">
                    <i class="fas fa-power-off"></i> <span>Sign Out</span>
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-6 md:p-12">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight italic">Driver <span class="text-blue-600">Portal</span></h2>
                    <p class="text-slate-400 font-bold text-[10px] uppercase tracking-[0.2em] mt-1">Official Identification</p>
                </div>
                
                <div class="flex items-center space-x-3 bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                    <div class="text-right">
                        <p class="text-[9px] font-black text-slate-400 uppercase">Current Job Status</p>
                        <p class="font-bold text-xs <?php echo ($job_status == 'active') ? 'text-emerald-500' : 'text-orange-500'; ?>">
                            <?php echo ($job_status == 'active') ? 'ON DUTY (Active)' : 'OFF DUTY (Idle)'; ?>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center <?php echo ($job_status == 'active') ? 'bg-emerald-50 text-emerald-500' : 'bg-orange-50 text-orange-500'; ?>">
                        <i class="fas <?php echo ($job_status == 'active') ? 'fa-steering-wheel' : 'fa-bed'; ?>"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[40px] p-8 shadow-xl shadow-slate-200/60 border border-white text-center">
                        <div class="w-40 h-40 bg-slate-100 rounded-full mx-auto mb-6 border-4 border-white shadow-md overflow-hidden flex items-center justify-center bg-cover bg-center">
                            <?php if(!empty($d['driver_photo'])): ?>
                                <img src="<?php echo $profile_img; ?>" 
                                     class="w-full h-full object-cover" 
                                     onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>&background=0284c7&color=fff&size=256'">
                            <?php else: ?>
                                <i class="fas fa-user text-5xl text-slate-300"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter"><?php echo $name; ?></h3>
                        <div class="flex items-center justify-center space-x-2 mt-2">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <p class="text-emerald-600 font-bold text-[10px] uppercase tracking-widest"><?php echo $status; ?></p>
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-[35px] p-8 mt-8 text-white relative overflow-hidden">
                        <div class="relative z-10">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-4 italic leading-none">Registered Employer</p>
                            <div class="flex items-center space-x-4 mb-6">
                                <div class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-blue-500">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <p class="text-lg font-bold italic"><?php echo $owner_name; ?></p>
                            </div>
                            
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-4 italic leading-none">Current Assigned Bus</p>
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center text-blue-400">
                                    <i class="fas fa-bus"></i>
                                </div>
                                <p class="text-lg font-black text-blue-400 uppercase"><?php echo $assigned_bus; ?></p>
                            </div>
                        </div>
                        <i class="fas fa-id-card absolute -right-5 -bottom-5 text-8xl text-white/5"></i>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-[40px] p-10 shadow-xl shadow-slate-200/60 border border-white">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-800 mb-10 border-b pb-4 flex justify-between">
                            Personal Details <span><i class="fas fa-shield-check text-blue-600"></i></span>
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Email</label>
                                <p class="font-bold text-slate-700 truncate"><?php echo $email; ?></p>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Mobile</label>
                                <p class="font-bold text-slate-700"><?php echo $phone; ?></p>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">NIC</label>
                                <p class="font-bold text-slate-700 uppercase"><?php echo $nic; ?></p>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">License Number</label>
                                <p class="font-black text-blue-600 uppercase tracking-widest"><?php echo $license; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[40px] p-10 shadow-xl shadow-slate-200/60 border border-white">
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-800 mb-8">License Copy (Scan)</h4>
                        <div class="w-full h-56 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 flex items-center justify-center overflow-hidden group">
                            <?php if(!empty($d['license_photo'])): ?>
                                <img src="<?php echo $license_img; ?>" 
                                     class="w-full h-full object-contain group-hover:scale-105 transition duration-500"
                                     onerror="this.style.display='none'; document.getElementById('lic-msg').style.display='block';">
                                <div id="lic-msg" style="display:none;" class="text-center">
                                    <i class="fas fa-file-excel text-3xl text-slate-300 mb-2"></i>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Image not found</p>
                                </div>
                            <?php else: ?>
                                <div class="text-center opacity-30">
                                    <i class="fas fa-id-card text-4xl mb-2"></i>
                                    <p class="text-[9px] font-black uppercase">No Document Uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>