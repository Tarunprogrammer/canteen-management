<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Increment
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]++;
    } else {
        $_SESSION['cart'][$item_id] = 1;
    }

    echo json_encode([
        'success' => true,
        'cart_count' => array_sum($_SESSION['cart']),
        'item_qty' => $_SESSION['cart'][$item_id] // Critical fix for 'undefined' error
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>