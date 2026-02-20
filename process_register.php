<?php
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $contact = $_POST['contact_number'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $nic = $_POST['nic'];
    $emergency = $_POST['emergency_contact'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Password check
    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    // Password encrypt kirima (Security)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 2. Image Upload Logic
    $target_dir = "images/passenger/";
    
    // Folder eka nathnam create kirima
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_name = time() . '_' . basename($_FILES["profile_img"]["name"]); // File name eka unique kirima
    $target_file = $target_dir . $image_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Image ekakda kiyala check kirima
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["profile_img"]["tmp_name"]);
        if($check === false) { $uploadOk = 0; }
    }

    // File eka upload kirima
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file)) {
            $profile_image_path = $target_file;
        } else {
            $profile_image_path = "images/passenger/default.png"; // Upload une nathnam default ekak
        }
    }

    // 3. Database ekata insert kirima
    $sql = "INSERT INTO passengers (full_name, contact_number, email, gender, nic_number, profile_image, emergency_contact, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $full_name, $contact, $email, $gender, $nic, $profile_image_path, $emergency, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Registration Successful!'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>