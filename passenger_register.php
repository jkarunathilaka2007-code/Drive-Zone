<?php
// Database connection eka include kirima
require_once 'db_config.php';

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $nic = mysqli_real_escape_string($conn, $_POST['nic']);
    $emergency = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validations
    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $msg_type = "error";
    } else {
        // Email or NIC check
        $check_query = "SELECT * FROM passengers WHERE email='$email' OR nic_number='$nic'";
        $res = $conn->query($check_query);

        if ($res->num_rows > 0) {
            $message = "Email or NIC already registered!";
            $msg_type = "error";
        } else {
            // Image Upload Handling
            $target_dir = "images/passenger/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $image_name = time() . "_" . basename($_FILES["profile_img"]["name"]);
            $target_file = $target_dir . $image_name;
            
            if (move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file)) {
                $img_path = $target_file;
            } else {
                $img_path = "images/passenger/default.png";
            }

            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO passengers (full_name, contact_number, email, gender, nic_number, profile_image, emergency_contact, password) 
                    VALUES ('$full_name', '$contact', '$email', '$gender', '$nic', '$img_path', '$emergency', '$hashed_pw')";

            if ($conn->query($sql) === TRUE) {
                $message = "Registration Successful!";
                $msg_type = "success";
                echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $message = "Error: " . $conn->error;
                $msg_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Registration | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: '#3b82f6' } } } }
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="bg-white dark:bg-slate-800 w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
            
            <div class="hidden md:flex md:w-1/3 bg-primary p-10 flex-col justify-center text-white">
                <i class="fas fa-bus-alt text-6xl mb-6"></i>
                <h2 class="text-3xl font-bold mb-4">DriveZone</h2>
                <p class="text-blue-100 italic">"Join the most trusted bus booking network in the country."</p>
                <div class="mt-8 space-y-4">
                    <div class="flex items-center space-x-3"><i class="fas fa-check-circle"></i> <span>Secure Booking</span></div>
                    <div class="flex items-center space-x-3"><i class="fas fa-check-circle"></i> <span>Live Tracking</span></div>
                    <div class="flex items-center space-x-3"><i class="fas fa-check-circle"></i> <span>24/7 Support</span></div>
                </div>
            </div>

            <div class="flex-1 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Passenger Registration</h2>
                    <a href="index.php" class="text-primary hover:underline font-bold text-sm">Back to Home</a>
                </div>

                <?php if($message): ?>
                    <div class="mb-4 p-3 rounded-lg text-sm font-bold <?php echo ($msg_type == 'success') ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                        <i class="<?php echo ($msg_type == 'success') ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'; ?> mr-2"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Full Name</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Contact Number</label>
                        <input type="text" name="contact_number" required class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Email Address</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Gender</label>
                        <select name="gender" class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">NIC Number</label>
                        <input type="text" name="nic" required class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Emergency Contact</label>
                        <input type="text" name="emergency_contact" required class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Profile Photo</label>
                        <input type="file" name="profile_img" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" required class="w-full px-4 py-2 rounded-lg border dark:bg-slate-700 dark:border-slate-600 outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <button type="submit" class="w-full bg-primary text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition shadow-lg">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>