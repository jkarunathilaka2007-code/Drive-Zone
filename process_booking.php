<?php
session_start();
require_once 'db_config.php';

// 1. පරිශීලකයා ලොග් වී ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'passenger') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Form එකෙන් එන දත්ත ලබා ගැනීම
    $passenger_id = $_SESSION['user_id'];
    $bus_id = mysqli_real_escape_string($conn, $_POST['bus_id']);
    $trip_type = mysqli_real_escape_string($conn, $_POST['trip_type']);
    $passenger_count = mysqli_real_escape_string($conn, $_POST['passenger_count']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $pickup_location = mysqli_real_escape_string($conn, $_POST['pickup_location']);
    $final_destination = mysqli_real_escape_string($conn, $_POST['final_destination']);
    $visit_places = mysqli_real_escape_string($conn, $_POST['visit_places']);
    $extra_facilities = mysqli_real_escape_string($conn, $_POST['extra_facilities']);

    // 3. Database එකට දත්ත ඇතුළත් කිරීම (Prepared Statement පාවිච්චිය වඩාත් සුදුසුයි)
    $sql = "INSERT INTO trip_bookings (
                bus_id, passenger_id, trip_type, passenger_count, 
                start_date, end_date, start_time, 
                pickup_location, final_destination, visit_places, extra_facilities, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iisssssssss", 
            $bus_id, $passenger_id, $trip_type, $passenger_count, 
            $start_date, $end_date, $start_time, 
            $pickup_location, $final_destination, $visit_places, $extra_facilities
        );

        if (mysqli_stmt_execute($stmt)) {
            // සාර්ථක නම් මගියාව සාර්ථකත්වයේ පණිවිඩයක් සහිත පිටුවකට යොමු කිරීම
            $_SESSION['booking_success'] = "Your hire request has been sent successfully! The owner will contact you soon with a price estimate.";
            header("Location: my_bookings.php"); 
            exit();
        } else {
            echo "Error: Could not execute query. " . mysqli_error($conn);
        }
    } else {
        echo "Error: Could not prepare query. " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    // කෙලින්ම URL එකෙන් එන්න හැදුවොත් redirect කිරීම
    header("Location: tripbook.php");
    exit();
}
?>