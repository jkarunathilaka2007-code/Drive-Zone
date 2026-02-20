<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $passenger_id = $_SESSION['user_id'];
    $bus_id       = mysqli_real_escape_string($conn, $_POST['bus_id']);
    $route_id     = mysqli_real_escape_string($conn, $_POST['route_id']);
    $travel_date  = mysqli_real_escape_string($conn, $_POST['travel_date']);
    $boarding_time= mysqli_real_escape_string($conn, $_POST['boarding_time']);
    $arrival_time = mysqli_real_escape_string($conn, $_POST['arrival_time']);
    $seats_str    = mysqli_real_escape_string($conn, $_POST['selected_seats']);
    $total_price  = mysqli_real_escape_string($conn, $_POST['final_amount']);
    $booking_ref  = mysqli_real_escape_string($conn, $_POST['booking_ref']);
    $package_name = mysqli_real_escape_string($conn, $_POST['package_name']);
    $pickup       = mysqli_real_escape_string($conn, $_POST['pickup_point']);
    $drop         = mysqli_real_escape_string($conn, $_POST['drop_point']);
    $gender       = mysqli_real_escape_string($conn, $_POST['gender']);

    $seats_array = explode(',', $seats_str);
    $success_count = 0;

    // එක් එක් සීට් එක සඳහා වෙන් වෙන්ව බුකින් එකක් ඇතුළු කිරීම
    foreach ($seats_array as $seat) {
        $seat = trim($seat);
        $sql = "INSERT INTO bookings (
                    bus_id, route_id, passenger_id, seat_number, gender, 
                    travel_date, boarding_time, arrival_time, status, 
                    booking_ref, package_name, pickup_point, drop_point, fare
                ) VALUES (
                    '$bus_id', '$route_id', '$passenger_id', '$seat', '$gender', 
                    '$travel_date', '$boarding_time', '$arrival_time', 'booked', 
                    '$booking_ref', '$package_name', '$pickup', '$drop', '$total_price'
                )";

        if (mysqli_query($conn, $sql)) {
            $success_count++;
        }
    }

    if ($success_count > 0) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "invalid_request";
}
?>