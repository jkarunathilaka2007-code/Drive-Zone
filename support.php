<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

$passenger_id = $_SESSION['user_id'];
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO support_tickets (passenger_id, category, subject, message) 
            VALUES ('$passenger_id', '$category', '$subject', '$message')";

    if (mysqli_query($conn, $sql)) {
        $success_msg = "Your message has been sent. We will contact you soon!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; color: #f1f5f9; }
        .glass-card { background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); }
        .input-style { background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); color: white; }
        .input-style:focus { border-color: #3b82f6; outline: none; }
    </style>
</head>
<body class="p-6 md:p-12">

    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black italic tracking-tighter">Help & <span class="text-blue-500">Support</span></h1>
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-2">How can we assist you today?</p>
            </div>
            <a href="passengerdashboard.php" class="w-12 h-12 glass-card rounded-2xl flex items-center justify-center text-slate-400 hover:text-white transition">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="space-y-4">
                <div class="glass-card p-6 rounded-[35px] border-l-4 border-blue-500">
                    <i class="fas fa-phone-alt text-blue-500 mb-4"></i>
                    <p class="text-[10px] font-black text-slate-500 uppercase">Hotline</p>
                    <p class="text-lg font-black">+94 112 000 000</p>
                </div>
                <div class="glass-card p-6 rounded-[35px] border-l-4 border-emerald-500">
                    <i class="fab fa-whatsapp text-emerald-500 mb-4"></i>
                    <p class="text-[10px] font-black text-slate-500 uppercase">WhatsApp</p>
                    <p class="text-lg font-black">+94 771 234 567</p>
                </div>
                <div class="glass-card p-6 rounded-[35px] border-l-4 border-amber-500">
                    <i class="fas fa-envelope text-amber-500 mb-4"></i>
                    <p class="text-[10px] font-black text-slate-500 uppercase">Email</p>
                    <p class="text-sm font-black">support@drivezone.lk</p>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="glass-card p-8 md:p-10 rounded-[45px]">
                    <?php if($success_msg): ?>
                        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold rounded-2xl flex items-center">
                            <i class="fas fa-check-circle mr-3"></i> <?= $success_msg ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block">Category</label>
                                <select name="category" class="input-style w-full p-4 rounded-2xl text-sm font-bold" required>
                                    <option value="Booking Issue">Booking Issue</option>
                                    <option value="Payment Problem">Payment Problem</option>
                                    <option value="Bus Quality">Bus Quality</option>
                                    <option value="Staff Behavior">Staff Behavior</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block">Subject</label>
                                <input type="text" name="subject" placeholder="Brief title" class="input-style w-full p-4 rounded-2xl text-sm font-bold" required>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-2 block">Your Message</label>
                            <textarea name="message" rows="5" placeholder="Describe your issue in detail..." class="input-style w-full p-4 rounded-3xl text-sm font-bold" required></textarea>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-5 rounded-[25px] font-black uppercase text-xs tracking-widest transition-all shadow-xl shadow-blue-900/20 active:scale-95">
                            Submit Ticket
                        </button>
                    </form>
                </div>

                <div class="mt-8">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4 mb-4">Your Recent Tickets</h4>
                    <div class="space-y-3">
                        <?php
                        $tickets_res = mysqli_query($conn, "SELECT * FROM support_tickets WHERE passenger_id = '$passenger_id' ORDER BY created_at DESC LIMIT 2");
                        while($ticket = mysqli_fetch_assoc($tickets_res)):
                            $st_color = ($ticket['status'] == 'open') ? 'text-blue-400 bg-blue-400/10' : 'text-emerald-400 bg-emerald-400/10';
                        ?>
                        <div class="glass-card p-5 rounded-3xl flex justify-between items-center">
                            <div>
                                <p class="text-xs font-black text-white"><?= $ticket['subject'] ?></p>
                                <p class="text-[9px] text-slate-500 uppercase font-bold"><?= date("M d, Y", strtotime($ticket['created_at'])) ?> • <?= $ticket['category'] ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase <?= $st_color ?>"><?= $ticket['status'] ?></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>