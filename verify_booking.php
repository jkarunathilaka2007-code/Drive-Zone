<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ref_code'])) {
    $ref = mysqli_real_escape_string($conn, $_POST['ref_code']);
    $bus_id = mysqli_real_escape_string($conn, $_POST['bus_id']);

    // Booking එක පරීක්ෂා කිරීම
    $check = mysqli_query($conn, "SELECT * FROM bookings WHERE booking_ref = '$ref' AND bus_id = '$bus_id' LIMIT 1");
    
    if (mysqli_num_rows($check) > 0) {
        $booking = mysqli_fetch_assoc($check);
        
        if ($booking['is_verified'] == 1) {
            echo json_encode(['status' => 'already', 'msg' => 'මම මගියා දැනටමත් Verify කර ඇත!']);
        } else {
            // Status එක සහ is_verified එක update කිරීම
            mysqli_query($conn, "UPDATE bookings SET is_verified = 1, status = 'boarded' WHERE id = '{$booking['id']}'");
            echo json_encode(['status' => 'success', 'msg' => 'Verification සාර්ථකයි! ආසන අංකය: ' . $booking['seat_number']]);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'වැරදි Reference කේතයක්!']);
    }
    exit();
}