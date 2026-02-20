<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// 1. දැනට තියෙන දත්ත ලබා ගැනීම
$sql = "SELECT * FROM passengers WHERE id = '$passenger_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// 2. Form එක Submit කළ පසු දත්ත Update කිරීම
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_with_slashes($conn, $_POST['full_name']);
    $contact = mysqli_real_escape_with_slashes($conn, $_POST['contact_number']);
    $emergency = mysqli_real_escape_with_slashes($conn, $_POST['emergency_contact']);
    
    $profile_path = $user['profile_image']; // පරණ image එක default එක ලෙස

    // Image Upload Process
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/profiles/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
        $file_name = "user_" . $passenger_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_path = $target_file;
        }
    }

    $update_sql = "UPDATE passengers SET 
                   full_name = '$full_name', 
                   contact_number = '$contact', 
                   emergency_contact = '$emergency', 
                   profile_image = '$profile_path' 
                   WHERE id = '$passenger_id'";

    if (mysqli_query($conn, $update_sql)) {
        $success_msg = "Profile updated successfully!";
        // දත්ත අලුත් කරමු
        $user['full_name'] = $full_name;
        $user['contact_number'] = $contact;
        $user['emergency_contact'] = $emergency;
        $user['profile_image'] = $profile_path;
    } else {
        $error_msg = "Update failed. Please try again.";
    }
}

// Helper function to prevent SQL injection (Slashes handling)
function mysqli_real_escape_with_slashes($conn, $data) {
    return mysqli_real_escape_string($conn, $data);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0b1120; color: #cbd5e1; }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .input-style { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); color: white; transition: all 0.3s; }
        .input-style:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 15px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <nav class="p-6 border-b border-white/5 bg-slate-900/50">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <a href="passengerdashboard.php" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <h2 class="text-sm font-black uppercase tracking-[5px] italic">EDIT <span class="text-blue-500">PROFILE</span></h2>
        </div>
    </nav>

    <main class="flex-1 flex items-center justify-center p-6">
        <div class="glass-card w-full max-w-xl rounded-[50px] p-8 md:p-12 relative overflow-hidden">
            
            <?php if($success_msg): ?>
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-xs font-bold rounded-2xl text-center">
                    <?= $success_msg ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
                
                <div class="flex flex-col items-center">
                    <div class="relative group">
                        <img id="preview" src="<?= (!empty($user['profile_image'])) ? $user['profile_image'] : 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']).'&background=3b82f6&color=fff' ?>" 
                             class="w-32 h-32 rounded-[35px] object-cover border-4 border-blue-500/30 group-hover:border-blue-500 transition-all duration-500">
                        <label for="profile_image" class="absolute bottom-0 right-0 w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center cursor-pointer hover:scale-110 transition shadow-lg">
                            <i class="fas fa-camera text-white text-sm"></i>
                            <input type="file" name="profile_image" id="profile_image" class="hidden" onchange="previewImage(this)">
                        </label>
                    </div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-4">Change Profile Picture</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block">Full Name</label>
                        <input type="text" name="full_name" value="<?= $user['full_name'] ?>" class="input-style w-full p-4 rounded-2xl text-sm font-bold" required>
                    </div>

                    <div>
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block">NIC (Locked)</label>
                        <input type="text" value="<?= $user['nic_number'] ?>" class="input-style w-full p-4 rounded-2xl text-sm font-bold opacity-50 cursor-not-allowed" readonly>
                    </div>

                    <div>
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block">Gender</label>
                        <input type="text" value="<?= ucfirst($user['gender']) ?>" class="input-style w-full p-4 rounded-2xl text-sm font-bold opacity-50 cursor-not-allowed" readonly>
                    </div>

                    <div>
                        <label class="text-[9px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block">Mobile Number</label>
                        <input type="text" name="contact_number" value="<?= $user['contact_number'] ?>" class="input-style w-full p-4 rounded-2xl text-sm font-bold" required>
                    </div>

                    <div>
                        <label class="text-[9px] font-black text-red-500/50 uppercase tracking-widest ml-4 mb-2 block">Emergency Contact</label>
                        <input type="text" name="emergency_contact" value="<?= $user['emergency_contact'] ?>" class="input-style w-full p-4 rounded-2xl text-sm font-bold border-red-500/10" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-5 rounded-[25px] font-black uppercase text-xs tracking-[0.2em] transition-all shadow-xl shadow-blue-900/20 active:scale-95">
                    Save Changes
                </button>

            </form>
        </div>
    </main>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>