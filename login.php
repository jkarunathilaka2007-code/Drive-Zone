<?php
// login.php
session_start();
require_once 'db_config.php';

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // පරීක්ෂා කළ යුතු Tables සහ Dashboards ලැයිස්තුව
    $roles = [
        ['table' => 'admins', 'dashboard' => 'admin_dashboard.php', 'role_name' => 'admin'],
        ['table' => 'bus_owners', 'dashboard' => 'bus_owner_dashboard.php', 'role_name' => 'owner'],
        ['table' => 'conductors', 'dashboard' => 'conductordashboard.php', 'role_name' => 'conductor'],
        ['table' => 'drivers', 'dashboard' => 'driverdashboard.php', 'role_name' => 'driver'],
        ['table' => 'passengers', 'dashboard' => 'passengerdashboard.php', 'role_name' => 'passenger']
    ];

    $user_found = false;

    foreach ($roles as $role) {
        $table = $role['table'];
        $sql = "SELECT * FROM `$table` WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $user_found = true;

            // Password එක verify කිරීම
            if (password_verify($password, $user['password'])) {
                
                // Bus Owner Status එක Approved ද කියා බැලීම
                if ($table == 'bus_owners' && $user['status'] !== 'approved') {
                    $error = "ඔබේ ගිණුම තවමත් අනුමත කර නොමැත.";
                    break;
                }

                // --- සෑම Role එකකටම පොදු Session Variables ---
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_role'] = $role['role_name']; // Dashboard Guards සඳහා

                // --- Role-Specific Session Setup (එක් එක් Dashboard එකට අවශ්‍ය විදිහට) ---

                if ($role['role_name'] == 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['user_name'] = $user['name'];
                } 
                elseif ($role['role_name'] == 'owner') {
                    $_SESSION['user_name'] = $user['owner_name'];
                } 
                elseif ($role['role_name'] == 'passenger') {
                    $_SESSION['user_name'] = $user['full_name']; // Passenger table එකේ තියෙන්නේ full_name
                } 
                else {
                    $_SESSION['user_name'] = $user['name'] ?? 'User';
                }

                header("Location: " . $role['dashboard']);
                exit();
            } else {
                $error = "මුරපදය වැරදියි!";
                break;
            }
        }
    }

    if (!$user_found) {
        $error = "මෙම ඊමේල් ලිපිනයෙන් පරිශීලකයෙකු හමු නොවීය!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveZone | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#0f172a] flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-slate-900/50 backdrop-blur-xl p-10 rounded-[40px] border border-white/5 shadow-2xl">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black italic tracking-tighter uppercase text-white">
                Drive<span class="text-blue-500">Zone</span>
            </h1>
            <p class="text-slate-500 font-bold text-[10px] uppercase tracking-[3px] mt-2">Access Your Portal</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-2xl mb-6 text-xs font-bold flex items-center">
                <i class="fas fa-circle-exclamation mr-3 text-lg"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2 mb-2 block">Email Address</label>
                <input type="email" name="email" required 
                       class="w-full bg-slate-800/50 border border-white/5 focus:border-blue-500 p-4 rounded-2xl outline-none font-bold text-sm text-white transition-all">
            </div>

            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-2 mb-2 block">Password</label>
                <input type="password" name="password" required 
                       class="w-full bg-slate-800/50 border border-white/5 focus:border-blue-500 p-4 rounded-2xl outline-none font-bold text-sm text-white transition-all">
            </div>

            <button type="submit" name="login" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white p-5 rounded-2xl font-black text-xs uppercase tracking-[2px] transition-all shadow-lg shadow-blue-900/20 active:scale-95">
                Sign In
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">
                Don't have an account? <a href="passenger_register.php" class="text-blue-500 hover:underline">Register Now</a>
            </p>
        </div>
    </div>

</body>
</html>