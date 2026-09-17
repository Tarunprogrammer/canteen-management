<?php
session_start();
include "../config/db.php";

// Set header immediately to ensure valid JSON response even on empty results
header('Content-Type: application/json');

// Ensure only admins can access this data
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

// Select amount and user details to verify the transaction
$query = "SELECT r.id, r.user_id, r.payment_method, r.amount, u.name as user_name
          FROM order_requests r
          JOIN users u ON r.user_id = u.id
          WHERE r.status = 'pending'
          ORDER BY r.id DESC";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(["error" => $conn->error]);
    exit();
}

$data = [];
while($row = $result->fetch_assoc()){
    // Ensure amount is formatted as a float for the frontend
    $row['amount'] = (float)$row['amount'];
    $data[] = $row;
}

echo json_encode($data);
?>