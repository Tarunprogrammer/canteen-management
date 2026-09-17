<?php
session_start();
include "../config/db.php";

// Clear buffer to prevent JSON corruption
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$payment_method = $_POST['payment_method'] ?? 'cash';

// Try standard table name first, fallback to variations to prevent crashes
$stmt = $conn->prepare("INSERT INTO order_requests (user_id, payment_method, status, created_at) VALUES (?, ?, 'pending', NOW())");
if (!$stmt) {
    $stmt = $conn->prepare("INSERT INTO order_request (user_id, payment_method, status, created_at) VALUES (?, ?, 'pending', NOW())");
}
if (!$stmt) {
    $stmt = $conn->prepare("INSERT INTO odrder_requests (user_id, payment_method, status, created_at) VALUES (?, ?, 'pending', NOW())");
}

if ($stmt) {
    $stmt->bind_param("is", $user_id, $payment_method);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'request_id' => $conn->insert_id]);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Database error. Table not found.']);