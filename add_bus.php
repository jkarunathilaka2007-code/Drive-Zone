<?php
session_start();
require_once 'db_config.php';

// Owner log welada balanna
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $gear = $_POST['gear_type'];
    $bus_no = mysqli_real_escape_string($conn, $_POST['bus_number']);
    $type = $_POST['bus_type'];
    $seats = intval($_POST['number_of_seats']);
    $facilities = mysqli_real_escape_string($conn, $_POST['facilities']);

    // Image Upload Logic
    $target_dir = "images/buses/" . $bus_no . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $uploaded_images = [];
    $image_fields = ['front', 'back', 'left', 'right', 'interior'];

    foreach ($image_fields as $field) {
        $file_name = time() . "_" . $field . "_" . basename($_FILES["img_" . $field]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["img_" . $field]["tmp_name"], $target_file)) {
            $uploaded_images[$field] = $target_file;
        } else {
            $uploaded_images[$field] = "";
        }
    }

    $sql = "INSERT INTO buses (owner_id, brand, model, gear_type, bus_number, bus_type, number_of_seats, facilities, img_front, img_back, img_left, img_right, img_interior) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssissssss", $owner_id, $brand, $model, $gear, $bus_no, $type, $seats, $facilities, 
                      $uploaded_images['front'], $uploaded_images['back'], $uploaded_images['left'], $uploaded_images['right'], $uploaded_images['interior']);

    if ($stmt->execute()) {
        $message = "<div class='bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-bold'>Bus registered successfully!</div>";
    } else {
        $message = "<div class='bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-bold'>Error: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register New Bus | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-6 md:p-12">

    <div class="max-w-5xl mx-auto bg-white rounded-[30px] shadow-2xl overflow-hidden">
        <div class="bg-primary bg-blue-600 p-8 text-white flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold italic">DriveZone <span class="text-blue-200">Fleet Management</span></h1>
                <p class="opacity-80">Add your bus details to the network</p>
            </div>
            <a href="bus_owner_dashboard.php" class="bg-white/20 hover:bg-white/30 px-6 py-2 rounded-xl font-bold transition">Back to Dashboard</a>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
            <?php echo $message; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-2">Bus Brand</label>
                    <input type="text" name="brand" required placeholder="Ex: Toyota, Leyland" class="w-full p-3 rounded-xl border-2 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-2">Model</label>
                    <input type="text" name="model" required placeholder="Ex: Coaster, Viking" class="w-full p-3 rounded-xl border-2 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-2">Bus Number</label>
                    <input type="text" name="bus_number" required placeholder="Ex: ND-4567" class="w-full p-3 rounded-xl border-2 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-2">Gear Type</label>
                    <select name="gear_type" class="w-full p-3 rounded-xl border-2 outline-none">
                        <option value="Auto">Auto</option>
                        <option value="Manual">Manual</option>
                        <option value="Both">Both</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-2">Bus Type</label>
                    <select name="bus_type" class="w-full p-3 rounded-xl border-2 outline-none">
                        <option value="AC">AC</option>
                        <option value="Non-AC">Non-AC</option>
                        <option value="Luxury">Luxury</option>
                        <option value="Semi-Luxury">Semi-Luxury</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-500 mb-2">Number of Seats</label>
                    <input type="number" name="number_of_seats" required class="w-full p-3 rounded-xl border-2 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-500 mb-2">Facilities (Comma separated)</label>
                <textarea name="facilities" placeholder="Ex: Wi-Fi, USB Charging, Adjustable Seats, TV" class="w-full p-3 rounded-xl border-2 outline-none h-24"></textarea>
            </div>

            <hr>

            <h3 class="text-xl font-bold text-gray-700 mb-4"><i class="fas fa-camera mr-2"></i>Bus Images</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase text-gray-400">Front View</label>
                    <input type="file" name="img_front" required class="text-xs">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase text-gray-400">Back View</label>
                    <input type="file" name="img_back" required class="text-xs">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase text-gray-400">Left Side</label>
                    <input type="file" name="img_left" required class="text-xs">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase text-gray-400">Right Side</label>
                    <input type="file" name="img_right" required class="text-xs">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase text-gray-400">Interior</label>
                    <input type="file" name="img_interior" required class="text-xs">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                Register Bus in DriveZone
            </button>
        </form>
    </div>

</body>
</html>