<?php
require_once 'db_config.php';
header('Content-Type: application/json');

if(isset($_GET['route_no'])){
    $route_no = mysqli_real_escape_string($conn, $_GET['route_no']);
    
    // ඔයාගේ table එකේ columns වලට අනුව query එක
    $query = "SELECT route_number, start_point, end_point, route_path_json FROM route_packs WHERE route_number = '$route_no' LIMIT 1";
    $res = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($res) > 0){
        $data = mysqli_fetch_assoc($res);
        $data['success'] = true;
        echo json_encode($data);
    } else {
        echo json_encode(['success' => false, 'message' => 'Route not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No route number provided']);
}
?>