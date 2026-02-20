<?php
require_once 'db_config.php';

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $owner_name = mysqli_real_escape_string($conn, $_POST['owner_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $nic = mysqli_real_escape_string($conn, $_POST['nic']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $msg_type = "error";
    } else {
        // Image Upload Folders
        $prof_dir = "images/owners/profiles/";
        $logo_dir = "images/owners/logos/";
        if (!file_exists($prof_dir)) mkdir($prof_dir, 0777, true);
        if (!file_exists($logo_dir)) mkdir($logo_dir, 0777, true);

        // Upload Profile Image
        $prof_name = time() . "_prof_" . basename($_FILES["profile_img"]["name"]);
        move_uploaded_file($_FILES["profile_img"]["tmp_name"], $prof_dir . $prof_name);

        // Upload Logo
        $logo_name = time() . "_logo_" . basename($_FILES["company_logo"]["name"]);
        move_uploaded_file($_FILES["company_logo"]["tmp_name"], $logo_dir . $logo_name);

        $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO bus_owners (company_name, owner_name, branch_address, contact_number, nic_number, email, profile_image, company_logo, password, status) 
                VALUES ('$company_name', '$owner_name', '$address', '$contact', '$nic', '$email', '$prof_dir$prof_name', '$logo_dir$logo_name', '$hashed_pw', 'pending')";

        if ($conn->query($sql) === TRUE) {
            $message = "Registration Successful! Please wait for Admin approval.";
            $msg_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bus Owner Registration | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 dark:bg-slate-900 transition-colors p-6">
    <div class="max-w-4xl mx-auto bg-white dark:bg-slate-800 rounded-3xl shadow-xl overflow-hidden">
        <div class="bg-green-600 p-6 text-white text-center">
            <h2 class="text-3xl font-bold">Bus Owner Registration</h2>
            <p>Register your fleet with DriveZone</p>
        </div>

        <?php if($message): ?>
            <div class="m-6 p-4 rounded-xl text-center font-bold <?php echo ($msg_type == 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="text" name="company_name" placeholder="Company Name" required class="w-full p-3 rounded-lg border dark:bg-slate-700 outline-none">
            <input type="text" name="owner_name" placeholder="Owner Full Name" required class="w-full p-3 rounded-lg border dark:bg-slate-700 outline-none">
            <textarea name="address" placeholder="Main Branch Address" required class="md:col-span-2 w-full p-3 rounded-lg border dark:bg-slate-700 outline-none"></textarea>
            <input type="text" name="contact_number" placeholder="Contact Number" required class="w-full p-3 rounded-lg border dark:bg-slate-700 outline-none">
            <input type="text" name="nic" placeholder="NIC Number" required class="w-full p-3 rounded-lg border dark:bg-slate-700 outline-none">
            <input type="email" name="email" placeholder="Email Address" required class="md:col-span-2 w-full p-3 rounded-lg border dark:bg-slate-700 outline-none">
            
            <div>
                <label class="text-xs font-bold text-gray-400">Owner Photo</label>
                <input type="file" name="profile_img" accept="image/*" required class="w-full text-sm mt-1">
            </div>
            <div>
                <label class="text-xs font-bold text-gray-400">Company Logo</label>
                <input type="file" name="company_logo" accept="image/*" required class="w-full text-sm mt-1">
            </div>

            <input type="password" name="password" placeholder="Password" required class="w-full p-3 rounded-lg border dark:bg-slate-700 outline-none">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full p-3 rounded-lg border dark:bg-slate-700 outline-none">

            <button type="submit" class="md:col-span-2 bg-green-600 text-white font-bold py-4 rounded-xl hover:bg-green-700 transition shadow-lg">
                Register as Bus Owner
            </button>
        </form>
    </div>
</body>
</html>