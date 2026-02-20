<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$alert_sent = false;

// SOS එකක් Register කිරීම (AJAX හෝ Button Click එකකින් පසුව සිදුවිය හැක)
if (isset($_POST['confirm_sos'])) {
    $loc = "Location Shared via Mobile"; // මීට වඩා දියුණු කරන්න GPS පාවිච්චි කළ හැක
    $sql = "INSERT INTO sos_alerts (passenger_id, current_location) VALUES ('$passenger_id', '$loc')";
    if (mysqli_query($conn, $sql)) {
        $alert_sent = true;
    }
}

// මගියාගේ විස්තර ලබා ගැනීම
$user_res = mysqli_query($conn, "SELECT * FROM passengers WHERE id = '$passenger_id'");
$user = mysqli_fetch_assoc($user_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOS EMERGENCY | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #7f1d1d; } /* තද රතු පාට background එකක් */
        .emergency-ring { animation: ring 1.5s infinite; }
        @keyframes ring {
            0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
            100% { box-shadow: 0 0 0 30px rgba(255, 255, 255, 0); }
        }
        .glass { background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 text-white font-sans">

    <div class="max-w-md w-full text-center">
        
        <?php if (!$alert_sent): ?>
            <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center mx-auto mb-8 emergency-ring">
                <i class="fas fa-exclamation-triangle text-red-600 text-5xl"></i>
            </div>

            <h1 class="text-4xl font-black uppercase italic tracking-tighter mb-4">Emergency!</h1>
            <p class="text-red-100 font-medium mb-10 leading-relaxed">හදිසි අවස්ථාවක්ද? පහත බොත්තම තද කිරීමෙන් ඔබේ ස්ථානය සහ විස්තර අපගේ හදිසි මෙහෙයුම් මැදිරියට වහාම ලැබෙනු ඇත.</p>

            <form action="" method="POST" class="space-y-4">
                <button type="submit" name="confirm_sos" class="w-full bg-white text-red-700 py-6 rounded-[30px] font-black uppercase text-lg tracking-widest shadow-2xl hover:bg-red-50 transition-all active:scale-95">
                    SEND SOS SIGNAL
                </button>
                <a href="passengerdashboard.php" class="block w-full py-4 text-white/60 font-bold uppercase text-[10px] tracking-[0.3em] hover:text-white transition">
                    Cancel & Go Back
                </a>
            </form>
        <?php else: ?>
            <div class="glass p-10 rounded-[50px] border-emerald-500/30">
                <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-white text-3xl"></i>
                </div>
                <h2 class="text-2xl font-black uppercase mb-4 tracking-tighter">Signal Sent!</h2>
                <p class="text-emerald-100 text-sm mb-6">ඔබේ හදිසි පණිවිඩය අප වෙත ලැබුණි. කරුණාකර ඔබ සිටින ස්ථානයේදීම ආරක්ෂිතව රැඳී සිටින්න. සහන කණ්ඩායම් දැනුවත් කර ඇත.</p>
                
                <div class="bg-black/20 p-6 rounded-3xl mb-8">
                    <p class="text-[10px] font-black text-white/50 uppercase mb-2">Primary Contact</p>
                    <p class="text-2xl font-black"><?= $user['emergency_contact'] ?></p>
                </div>

                <a href="passengerdashboard.php" class="inline-block bg-white/10 hover:bg-white/20 px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition">
                    Return to Home
                </a>
            </div>
        <?php endif; ?>

        <div class="mt-12 grid grid-cols-2 gap-4">
            <div class="glass p-4 rounded-3xl">
                <i class="fas fa-phone-alt text-white/50 mb-2"></i>
                <p class="text-[8px] font-black uppercase text-white/70">Police</p>
                <p class="text-sm font-bold">119</p>
            </div>
            <div class="glass p-4 rounded-3xl">
                <i class="fas fa-ambulance text-white/50 mb-2"></i>
                <p class="text-[8px] font-black uppercase text-white/70">Ambulance</p>
                <p class="text-sm font-bold">1990</p>
            </div>
        </div>

    </div>

    <script>
        // SOS page එකට ආපු ගමන් රතු පාට background එක blink වෙන්න
        if (!<?= $alert_sent ? 'true' : 'false' ?>) {
            setInterval(() => {
                document.body.style.backgroundColor = 
                    document.body.style.backgroundColor === 'rgb(127, 29, 29)' ? '#991b1b' : '#7f1d1d';
            }, 500);
        }
    </script>
</body>
</html>