<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['admin_id'])){
    echo json_encode(["success" => false]);
    exit();
}

$id = $_POST['id'];
$action = $_POST['action'];

$conn->begin_transaction();

try {
    // 1. Update the request status
    $stmt = $conn->prepare("UPDATE order_requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $id);
    $stmt->execute();

    if ($action === 'accepted') {
        // 2. Find the user ID for this request
        $uQuery = $conn->prepare("SELECT user_id FROM order_requests WHERE id = ?");
        $uQuery->bind_param("i", $id);
        $uQuery->execute();
        $userId = $uQuery->get_result()->fetch_assoc()['user_id'];

        // 3. Promote their latest 'verifying' order to 'pending' (Kitchen view)
        $promote = $conn->prepare("UPDATE orders SET status = 'pending' WHERE user_id = ? AND status = 'verifying' ORDER BY id DESC LIMIT 1");
        $promote->bind_param("i", $userId);
        $promote->execute();
    }

    $conn->commit();
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false]);
}
?>