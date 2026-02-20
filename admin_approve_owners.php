<?php
require_once 'db_config.php';

// Approve or Reject Logic
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = ($_GET['action'] == 'approve') ? 'approved' : 'rejected';
    
    $update_sql = "UPDATE bus_owners SET status='$status' WHERE id=$id";
    if ($conn->query($update_sql)) {
        echo "<script>alert('Owner status updated to $status'); window.location.href='admin_approve_owners.php';</script>";
    }
}

// Fetch Pending Owners
$sql = "SELECT * FROM bus_owners WHERE status='pending' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Approve Owners</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal-bg { background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(5px); }
    </style>
</head>
<body class="bg-gray-50 p-4 md:p-10">

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Pending Approval Requests</h1>
                <p class="text-gray-500">Review and manage bus owner registrations</p>
            </div>
            <a href="admin_dashboard.php" class="bg-white border-2 border-gray-800 text-gray-800 px-6 py-2 rounded-xl font-bold hover:bg-gray-800 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i> Dashboard
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="p-5 text-sm font-semibold uppercase">Company & Owner</th>
                            <th class="p-5 text-sm font-semibold uppercase">Contact Details</th>
                            <th class="p-5 text-sm font-semibold uppercase text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="p-5">
                                    <div class="flex items-center space-x-4">
                                        <img src="<?php echo $row['company_logo']; ?>" class="w-12 h-12 rounded-lg object-cover shadow-sm border">
                                        <div>
                                            <p class="font-bold text-gray-900"><?php echo $row['company_name']; ?></p>
                                            <p class="text-xs text-primary font-semibold underline"><?php echo $row['owner_name']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <p class="text-sm font-medium"><i class="fas fa-envelope text-gray-400 mr-2"></i><?php echo $row['email']; ?></p>
                                    <p class="text-sm font-medium"><i class="fas fa-phone text-gray-400 mr-2"></i><?php echo $row['contact_number']; ?></p>
                                </td>
                                <td class="p-5">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick='openModal(<?php echo json_encode($row); ?>)' class="bg-blue-100 text-blue-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 hover:text-white transition">
                                            <i class="fas fa-eye mr-1"></i> View More
                                        </button>
                                        <a href="?action=approve&id=<?php echo $row['id']; ?>" class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-green-600 transition shadow-md">Approve</a>
                                        <a href="?action=reject&id=<?php echo $row['id']; ?>" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-600 transition shadow-md">Reject</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-20 text-center text-gray-400 italic">No pending registrations at the moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="detailsModal" class="fixed inset-0 z-50 hidden items-center justify-center modal-bg p-4">
        <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden relative animate-in fade-in zoom-in duration-300">
            <button onclick="closeModal()" class="absolute top-5 right-5 text-gray-400 hover:text-red-500 transition text-2xl">
                <i class="fas fa-times-circle"></i>
            </button>
            
            <div class="p-8">
                <h2 class="text-2xl font-bold border-b pb-4 mb-6"><i class="fas fa-info-circle text-primary mr-2"></i>Owner Full Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-2">Company Logo</p>
                            <img id="m_logo" src="" class="w-full h-32 object-contain rounded-xl border bg-gray-50">
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase mb-2">Owner Profile Photo</p>
                            <img id="m_profile" src="" class="w-full h-48 object-cover rounded-xl border">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Company Name</p>
                            <p id="m_company" class="font-bold text-lg"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Owner Name</p>
                            <p id="m_owner" class="font-semibold"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">NIC Number</p>
                            <p id="m_nic" class="font-semibold"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Branch Address</p>
                            <p id="m_address" class="text-sm text-gray-600"></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase">Contact & Email</p>
                            <p id="m_contact" class="text-sm font-semibold"></p>
                            <p id="m_email" class="text-sm text-blue-600"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-6 flex justify-end">
                <button onclick="closeModal()" class="bg-gray-800 text-white px-8 py-2 rounded-xl font-bold">Close View</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(data) {
            document.getElementById('m_logo').src = data.company_logo;
            document.getElementById('m_profile').src = data.profile_image;
            document.getElementById('m_company').innerText = data.company_name;
            document.getElementById('m_owner').innerText = data.owner_name;
            document.getElementById('m_nic').innerText = data.nic_number;
            document.getElementById('m_address').innerText = data.branch_address;
            document.getElementById('m_contact').innerText = data.contact_number;
            document.getElementById('m_email').innerText = data.email;

            document.getElementById('detailsModal').classList.remove('hidden');
            document.getElementById('detailsModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('detailsModal').classList.add('hidden');
            document.getElementById('detailsModal').classList.remove('flex');
        }
    </script>

</body>
</html>