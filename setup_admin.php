<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_config.php'; 

$email = "admin@gmail.com";
$new_password = "admin123";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// තියෙන record එක update කිරීම
$sql = "UPDATE admins SET password = '$hashed_password' WHERE email = '$email'";

if (mysqli_query($conn, $sql)) {
    echo "<h3>සාර්ථකයි! Password එක Update වුණා.</h3>";
    echo "දැන් <b>admin@gmail.com</b> සහ <b>admin123</b> භාවිතා කර ලොග් වන්න.";
    echo "<br><br><a href='login.php'>Login Page එකට යන්න</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>