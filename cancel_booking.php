<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['ref'])) {
    header("Location: my_bookings.php");
    exit();
}

$ref = mysqli_real_escape_string($conn, $_GET['ref']);
$user_id = $_SESSION['user_id'];

// තමන්ගේ බුකින් එකක් පමණක් ඩිලීට් කිරීමට සහ is_verified නොවූ ඒවා පමණක් ඩිලීට් කිරීමට වග බලා ගැනීම
$sql = "DELETE FROM bookings WHERE booking_ref = '$ref' AND passenger_id = '$user_id' AND is_verified = 0";

if (mysqli_query($conn, $sql)) {
    header("Location: my_bookings.php?msg=cancelled");
} else {
    echo "Error deleting record: " . mysqli_error($conn);
}
?>