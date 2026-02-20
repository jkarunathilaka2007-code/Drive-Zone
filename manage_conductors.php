<?php
session_start();
require_once 'db_config.php';

// Owner log welada balanna
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

// --- Approval Logic ---
if (isset($_GET['approve_id'])) {
    $c_id = intval($_GET['approve_id']);
    $conn->query("UPDATE conductors SET status = 'approved' WHERE id = $c_id AND owner_id = $owner_id");
    echo "<script>alert('Conductor Approved!'); window.location.href='manage_conductors.php';</script>";
}

if (isset($_GET['reject_id'])) {
    $c_id = intval($_GET['reject_id']);
    $conn->query("UPDATE conductors SET status = 'rejected' WHERE id = $c_id AND owner_id = $owner_id");
    echo "<script>alert('Conductor Rejected!'); window.location.href='manage_conductors.php';</script>";
}

// Fetch all conductors for this owner
$sql = "SELECT * FROM conductors WHERE owner_id = $owner_id ORDER BY status DESC, created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Conductors | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen p-6 md:p-10 transition-colors">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <a href="bus_owner_dashboard.php" class="text-emerald-600 font-bold flex items-center mb-2 hover:underline">
                    <i class="fas fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-black italic">Manage <span class="text-emerald-600">Conductors</span></h1>
            </div>
            
            <button onclick="navigator.clipboard.writeText('http://localhost/drivezone/conductor_register.php?ref=<?php echo $owner_id; ?>'); alert('Link Copied!');" 
                class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition">
                <i class="fas fa-link mr-2"></i> Recruitment Link
            </button>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[40px] shadow-xl overflow-hidden border dark:border-slate-700">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-slate-900/50 border-b dark:border-slate-700 text-gray-400">
                    <tr>
                        <th class="p-6 text-xs font-black uppercase tracking-widest">Conductor</th>
                        <th class="p-6 text-xs font-black uppercase tracking-widest">NIC / License</th>
                        <th class="p-6 text-xs font-black uppercase tracking-widest text-center">Status</th>
                        <th class="p-6 text-xs font-black uppercase tracking-widest text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-slate-700">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition">
                        <td class="p-6 flex items-center space-x-4">
                            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-emerald-600 border-2 border-emerald-500">
                                <i class="fas fa-id-badge text-xl"></i>
                            </div>
                            <div>
                                <p class="font-black"><?php echo $row['full_name']; ?></p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase"><?php echo $row['email']; ?></p>
                            </div>
                        </td>
                        <td class="p-6">
                            <p class="text-sm font-bold"><?php echo $row['nic_number']; ?></p>
                            <p class="text-[10px] text-emerald-500 font-black">Lic: <?php echo $row['conductor_license_no']; ?></p>
                        </td>
                        <td class="p-6 text-center">
                            <?php 
                                $s = $row['status'];
                                $color = ($s == 'approved') ? 'bg-green-100 text-green-600' : (($s == 'pending') ? 'bg-orange-100 text-orange-600' : 'bg-red-100 text-red-600');
                                echo "<span class='px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest $color'>$s</span>";
                            ?>
                        </td>
                        <td class="p-6 text-right space-x-2">
                            <button onclick='showDetails(<?php echo json_encode($row); ?>)' class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:scale-105 transition">View More</button>
                            <?php if($row['status'] == 'pending'): ?>
                                <a href="?approve_id=<?php echo $row['id']; ?>" class="bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-bold hover:shadow-lg transition">Approve</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="conModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 w-full max-w-3xl rounded-[40px] shadow-2xl overflow-hidden relative border dark:border-slate-700">
            <button onclick="closeModal()" class="absolute top-6 right-6 w-10 h-10 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>

            <div class="p-10">
                <h3 class="text-2xl font-black italic mb-8 flex items-center">
                    <i class="fas fa-id-card-clip text-emerald-600 mr-3 text-3xl"></i> Conductor Profile
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Full Name</label>
                            <p id="m_name" class="font-black text-lg text-emerald-600"></p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Contact Details</label>
                            <p class="font-bold"><i class="fas fa-phone mr-2 text-xs opacity-50"></i> <span id="m_mobile"></span></p>
                            <p class="font-bold"><i class="fas fa-envelope mr-2 text-xs opacity-50"></i> <span id="m_email"></span></p>
                            <p class="font-bold text-red-500"><i class="fas fa-heartbeat mr-2 text-xs opacity-50"></i> <span id="m_emergency"></span></p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Address</label>
                            <p id="m_address" class="text-sm font-bold leading-relaxed"></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-slate-900/50 p-6 rounded-[30px] border dark:border-slate-700 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">License No</label>
                                <p id="m_lic_no" class="font-black text-emerald-600"></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Experience</label>
                                <p id="m_exp" class="font-black"></p>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">License Expiry</label>
                            <p id="m_expiry" class="font-black text-red-500 italic"></p>
                        </div>
                        <div class="pt-4 border-t dark:border-slate-700">
                             <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">License Document</label>
                             <img id="m_lic_img" src="" class="w-full h-32 object-cover rounded-2xl border dark:border-slate-700 hover:scale-105 transition cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDetails(con) {
            document.getElementById('m_name').innerText = con.full_name;
            document.getElementById('m_mobile').innerText = con.mobile_number;
            document.getElementById('m_email').innerText = con.email;
            document.getElementById('m_emergency').innerText = con.emergency_contact;
            document.getElementById('m_address').innerText = con.address;
            document.getElementById('m_lic_no').innerText = con.conductor_license_no;
            document.getElementById('m_exp').innerText = con.experience_years + " Years";
            document.getElementById('m_expiry').innerText = con.license_expiry;
            document.getElementById('m_lic_img').src = con.license_photo;

            document.getElementById('conModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('conModal').classList.add('hidden');
        }
    </script>
</body>
</html>