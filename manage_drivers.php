<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

// Approval Logic
if (isset($_GET['approve_id'])) {
    $d_id = intval($_GET['approve_id']);
    $conn->query("UPDATE drivers SET status = 'approved' WHERE id = $d_id AND owner_id = $owner_id");
    echo "<script>alert('Driver Approved!'); window.location.href='manage_drivers.php';</script>";
}

// Fetch all drivers
$sql = "SELECT * FROM drivers WHERE owner_id = $owner_id ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Drivers | DriveZone</title>
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
<body class="bg-gray-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen p-6 md:p-10 transition-colors duration-300">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <a href="bus_owner_dashboard.php" class="text-blue-600 font-bold flex items-center mb-2 hover:underline">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <h1 class="text-4xl font-black italic italic">Manage <span class="text-blue-600">Drivers</span></h1>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-[40px] shadow-xl overflow-hidden border dark:border-slate-700">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-slate-900/50 border-b dark:border-slate-700">
                    <tr>
                        <th class="p-6 text-xs font-black uppercase text-gray-400">Driver</th>
                        <th class="p-6 text-xs font-black uppercase text-gray-400">Status</th>
                        <th class="p-6 text-xs font-black uppercase text-gray-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-slate-700">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition">
                        <td class="p-6 flex items-center space-x-4">
                            <img src="<?php echo $row['driver_photo']; ?>" class="w-12 h-12 rounded-full object-cover border-2 border-blue-500">
                            <div>
                                <p class="font-black"><?php echo $row['full_name']; ?></p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?php echo $row['nic_number']; ?></p>
                            </div>
                        </td>
                        <td class="p-6 text-sm">
                            <?php 
                                $status_class = $row['status'] == 'approved' ? 'bg-green-100 text-green-600' : 'bg-orange-100 text-orange-600';
                                echo "<span class='px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter $status_class'>{$row['status']}</span>";
                            ?>
                        </td>
                        <td class="p-6 text-right space-x-2">
                            <button onclick='showDriverDetails(<?php echo json_encode($row); ?>)' class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-blue-700 transition">
                                View More
                            </button>
                            <?php if($row['status'] == 'pending'): ?>
                                <a href="?approve_id=<?php echo $row['id']; ?>" class="bg-emerald-500 text-white px-4 py-2 rounded-xl text-xs font-bold">Approve</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="driverModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 w-full max-w-4xl max-h-[90vh] rounded-[40px] shadow-2xl overflow-hidden flex flex-col relative">
            
            <button onclick="closeModal()" class="absolute top-6 right-6 w-10 h-10 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center text-gray-500 hover:bg-red-500 hover:text-white transition z-10">
                <i class="fas fa-times"></i>
            </button>

            <div class="overflow-y-auto p-8 md:p-12">
                <div class="flex flex-col md:flex-row gap-10 items-start">
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <img id="m_photo" src="" class="w-48 h-48 rounded-[30px] object-cover border-4 border-blue-600 shadow-xl mb-6">
                        <h3 id="m_name" class="text-2xl font-black text-center mb-1"></h3>
                        <p id="m_status" class="text-[10px] font-black uppercase tracking-[0.2em] mb-6"></p>
                    </div>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-8 w-full">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Personal Info</label>
                            <p class="font-bold"><i class="fas fa-id-card mr-2 text-blue-600"></i> <span id="m_nic"></span></p>
                            <p class="font-bold"><i class="fas fa-birthday-cake mr-2 text-blue-600"></i> <span id="m_dob"></span></p>
                            <p class="font-bold text-sm"><i class="fas fa-map-marker-alt mr-2 text-blue-600"></i> <span id="m_address"></span></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Contact Info</label>
                            <p class="font-bold text-blue-600"><i class="fas fa-phone mr-2"></i> <span id="m_phone"></span></p>
                            <p class="font-bold"><i class="fas fa-envelope mr-2 text-blue-600"></i> <span id="m_email"></span></p>
                            <p class="font-bold text-red-500"><i class="fas fa-heartbeat mr-2"></i> <span id="m_emergency"></span></p>
                        </div>
                        <div class="space-y-1 p-6 bg-slate-50 dark:bg-slate-900 rounded-3xl border dark:border-slate-700 md:col-span-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-3">License & Experience</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">License No</p>
                                    <p id="m_license_no" class="font-black text-lg"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Classes</p>
                                    <p id="m_classes" class="font-black text-lg text-blue-600"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Experience</p>
                                    <p id="m_exp" class="font-black"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Expiry Date</p>
                                    <p id="m_expiry" class="font-black text-red-500"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="md:col-span-2 grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Driving License Photo</label>
                                <img id="m_license_img" src="" class="w-full h-32 object-cover rounded-2xl border dark:border-slate-700 hover:scale-105 transition cursor-pointer">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Police Report</label>
                                <div class="w-full h-32 bg-gray-100 dark:bg-slate-900 rounded-2xl flex items-center justify-center border-2 border-dashed dark:border-slate-700">
                                    <i class="fas fa-file-shield text-3xl text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDriverDetails(driver) {
            document.getElementById('m_name').innerText = driver.full_name;
            document.getElementById('m_photo').src = driver.driver_photo;
            document.getElementById('m_nic').innerText = driver.nic_number;
            document.getElementById('m_dob').innerText = driver.dob;
            document.getElementById('m_address').innerText = driver.address;
            document.getElementById('m_phone').innerText = driver.contact_no;
            document.getElementById('m_email').innerText = driver.email;
            document.getElementById('m_emergency').innerText = "Emergency: " + driver.emergency_contact;
            document.getElementById('m_license_no').innerText = driver.license_no;
            document.getElementById('m_classes').innerText = driver.license_classes;
            document.getElementById('m_exp').innerText = driver.experience_years + " Years";
            document.getElementById('m_expiry').innerText = driver.license_expiry;
            document.getElementById('m_status').innerText = driver.status;
            document.getElementById('m_license_img').src = driver.license_photo;

            document.getElementById('driverModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('driverModal').classList.add('hidden');
        }
    </script>
</body>
</html>