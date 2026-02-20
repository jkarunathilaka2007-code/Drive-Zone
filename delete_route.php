<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $route_id = intval($_GET['id']);

    // 1. මේ Route එක අයිති මේ Owner ටමද කියලා චෙක් කරනවා
    $check_sql = "SELECT id FROM routes WHERE id = $route_id AND owner_id = $owner_id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        
        $conn->begin_transaction();

        try {
            // 2. මේ route එකට link වෙලා තියෙන Bus එකේ ID එක ගන්නවා
            $bus_query = $conn->query("SELECT id FROM buses WHERE route_id = $route_id AND owner_id = $owner_id");
            $bus = $bus_query->fetch_assoc();
            
            if ($bus) {
                $bus_id = $bus['id'];

                // A. Bookings මකනවා (මේ බස් එකට අදාළ සියලුම bookings මකා දමයි)
                $conn->query("DELETE FROM bookings WHERE bus_id = $bus_id");

                // B. Drivers table එකේ bus_id එක NULL කරනවා
                $conn->query("UPDATE drivers SET bus_id = NULL WHERE bus_id = $bus_id AND owner_id = $owner_id");

                // C. Conductors table එකේ bus_id එක NULL කරනවා
                $conn->query("UPDATE conductors SET bus_id = NULL WHERE bus_id = $bus_id AND owner_id = $owner_id");

                // D. Buses table එකේ route_id එක NULL කරනවා
                $conn->query("UPDATE buses SET route_id = NULL WHERE id = $bus_id AND owner_id = $owner_id");
            }

            // 3. Ticket prices මකනවා
            $conn->query("DELETE FROM ticket_prices WHERE route_id = $route_id");

            // 4. අවසානයේ Route එක මකනවා
            $conn->query("DELETE FROM routes WHERE id = $route_id AND owner_id = $owner_id");

            $conn->commit();
            header("Location: routes.php?status=deleted");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            die("Error: " . $e->getMessage());
        }

    } else {
        header("Location: routes.php?status=unauthorized");
        exit();
    }
} else {
    header("Location: routes.php");
    exit();
}
?>