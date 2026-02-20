<?php
require_once 'db_config.php';

$owner_ref = isset($_GET['ref']) ? intval($_GET['ref']) : 0;
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_id = $_POST['owner_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $nic = $_POST['nic_number'];
    $dob = $_POST['dob'];
    $mobile = $_POST['mobile_number'];
    $address = $_POST['address'];
    $license_no = $_POST['conductor_license_no'];
    $exp = $_POST['experience'];
    $expiry = $_POST['license_expiry_date'];
    $emergency = $_POST['emergency_contact'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Image Upload
    $target_dir = "uploads/conductors/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $license_img = $target_dir . time() . "_con_license.jpg";
    move_uploaded_file($_FILES["license_photo"]["tmp_name"], $license_img);

    $sql = "INSERT INTO conductors (owner_id, full_name, email, nic_number, dob, mobile_number, address, conductor_license_no, experience_years, license_photo, license_expiry, emergency_contact, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssissss", $owner_id, $full_name, $email, $nic, $dob, $mobile, $address, $license_no, $exp, $license_img, $expiry, $emergency, $password);

    if ($stmt->execute()) {
        $msg = "<p class='bg-green-500 text-white p-4 rounded-2xl font-bold'>Registration Successful! Wait for Owner's Approval.</p>";
    } else {
        $msg = "<p class='bg-red-500 text-white p-4 rounded-2xl font-bold'>Error: NIC or Email already registered.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Conductor Registration | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 p-6 md:p-12 font-sans">
    <div class="max-w-4xl mx-auto bg-white rounded-[50px] shadow-2xl overflow-hidden">
        <div class="bg-emerald-600 p-12 text-white italic">
            <h1 class="text-4xl font-black italic italic">Conductor <span class="text-emerald-200">Registration</span></h1>
            <p class="font-bold opacity-80 mt-2 tracking-widest uppercase text-xs">Register under Owner ID: #<?php echo $owner_ref; ?></p>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="p-12 space-y-6">
            <?php echo $msg; ?>
            <input type="hidden" name="owner_id" value="<?php echo $owner_ref; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="text" name="full_name" placeholder="Full Name" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
                <input type="email" name="email" placeholder="Email Address" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
                <input type="text" name="nic_number" placeholder="NIC Number" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-widest">Date of Birth</label>
                    <input type="date" name="dob" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
                </div>
                <input type="text" name="mobile_number" placeholder="Mobile Number" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
                <input type="text" name="emergency_contact" placeholder="Emergency Contact Number" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
            </div>

            <textarea name="address" placeholder="Residential Address" class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition h-24"></textarea>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-8 bg-emerald-50 rounded-[35px] border border-emerald-100">
                <input type="text" name="conductor_license_no" placeholder="License No" required class="w-full p-4 rounded-xl bg-white border-none shadow-sm font-bold">
                <input type="number" name="experience" placeholder="Years of Exp" required class="w-full p-4 rounded-xl bg-white border-none shadow-sm font-bold">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-emerald-400 uppercase ml-2 tracking-widest">License Expiry</label>
                    <input type="date" name="license_expiry_date" required class="w-full p-3 rounded-xl bg-white border-none shadow-sm font-bold">
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-3xl border-2 border-dashed border-gray-200">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 block text-center">Upload Conductor License Photo</label>
                <input type="file" name="license_photo" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                <input type="password" name="password" placeholder="Create Password" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-emerald-600 outline-none font-bold transition">
            </div>

            <button type="submit" class="w-full bg-emerald-600 text-white font-black py-6 rounded-[30px] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition transform active:scale-95 text-lg uppercase tracking-widest italic">
                Register as Conductor
            </button>
        </form>
    </div>
</body>
</html>