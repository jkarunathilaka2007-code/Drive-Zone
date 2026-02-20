<?php
require_once 'db_config.php';

$owner_ref = isset($_GET['ref']) ? intval($_GET['ref']) : 0;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic Details
    $name = $_POST['full_name'];
    $nic = $_POST['nic_number'];
    $email = $_POST['email'];
    $owner_id = $_POST['owner_id'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Image Uploads
    $target_dir = "uploads/drivers/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

    $photo = $target_dir . time() . "_photo.jpg";
    $license = $target_dir . time() . "_license.jpg";
    
    move_uploaded_file($_FILES["driver_photo"]["tmp_name"], $photo);
    move_uploaded_file($_FILES["license_photo"]["tmp_name"], $license);

    $sql = "INSERT INTO drivers (owner_id, full_name, nic_number, email, dob, address, contact_no, emergency_contact, license_no, license_expiry, license_classes, experience_years, driver_photo, license_photo, password, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssssssisss", $owner_id, $name, $nic, $email, $_POST['dob'], $_POST['address'], $_POST['contact_no'], $_POST['emergency_contact'], $_POST['license_no'], $_POST['license_expiry'], $_POST['license_classes'], $_POST['experience_years'], $photo, $license, $pass);

    if ($stmt->execute()) {
        $message = "<div class='bg-green-100 text-green-700 p-4 rounded-2xl'>Registration successful! Wait for owner approval.</div>";
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-2xl'>Error: Email or NIC already exists.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Registration | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 p-6">
    <div class="max-w-4xl mx-auto bg-white rounded-[40px] shadow-2xl overflow-hidden">
        <div class="bg-blue-600 p-10 text-white">
            <h1 class="text-3xl font-black italic">Driver <span class="text-blue-200">Registration</span></h1>
            <p class="opacity-80">Join the DriveZone network under Owner ID: <?php echo $owner_ref; ?></p>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="p-10 space-y-6">
            <?php echo $message; ?>
            <input type="hidden" name="owner_id" value="<?php echo $owner_ref; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <input type="text" name="full_name" placeholder="Full Name" required class="w-full p-4 rounded-2xl border-2 outline-none focus:border-blue-600">
                <input type="text" name="nic_number" placeholder="NIC Number" required class="w-full p-4 rounded-2xl border-2 outline-none focus:border-blue-600">
                <input type="email" name="email" placeholder="Email Address" required class="w-full p-4 rounded-2xl border-2 outline-none focus:border-blue-600">
                <input type="date" name="dob" required class="w-full p-4 rounded-2xl border-2 outline-none focus:border-blue-600">
                <input type="text" name="contact_no" placeholder="Contact Number" required class="w-full p-4 rounded-2xl border-2 outline-none focus:border-blue-600">
                <input type="text" name="emergency_contact" placeholder="Emergency Contact" required class="w-full p-4 rounded-2xl border-2 outline-none focus:border-blue-600">
            </div>

            <textarea name="address" placeholder="Residential Address" class="w-full p-4 rounded-2xl border-2 outline-none h-24"></textarea>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-6 bg-gray-50 rounded-3xl">
                <input type="text" name="license_no" placeholder="License Number" required class="p-3 rounded-xl border">
                <input type="date" name="license_expiry" required class="p-3 rounded-xl border">
                <input type="text" name="license_classes" placeholder="Classes (D, G1)" class="p-3 rounded-xl border">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs font-bold text-gray-400">Driver Photo</label>
                    <input type="file" name="driver_photo" required class="w-full">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400">License Photo</label>
                    <input type="file" name="license_photo" required class="w-full">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 pt-4 border-t">
                <input type="password" name="password" placeholder="Create Password" required class="p-4 rounded-2xl border-2">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required class="p-4 rounded-2xl border-2">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[25px] hover:bg-blue-700 shadow-xl shadow-blue-200">
                Register as Driver
            </button>
        </form>
    </div>
</body>
</html>