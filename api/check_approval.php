<?php
session_start();
include "../config/db.php";

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check standard table first, fallback to variations
$stmt = $conn->prepare("SELECT status FROM order_requests WHERE id = ?");
if (!$stmt) $stmt = $conn->prepare("SELECT status FROM order_request WHERE id = ?");
if (!$stmt) $stmt = $conn->prepare("SELECT status FROM odrder_requests WHERE id = ?");

if ($stmt) {
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        echo json_encode(['status' => strtolower(trim($row['status']))]);
        exit();
    }
}

echo json_encode(['status' => 'pending']);