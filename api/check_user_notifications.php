<?php
session_start();
include "../config/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['has_notification' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check for the most recent completed order for this user
// status 'completed' means it was just finished by admin
$query = "SELECT id FROM orders WHERE user_id = $user_id AND status = 'completed' ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($query);

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'has_notification' => true, 
        'order_id' => $row['id']
    ]);
} else {
    echo json_encode(['has_notification' => false]);
}
?>