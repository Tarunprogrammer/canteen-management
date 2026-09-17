<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];

    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] -= 1;
        
        // If count hits 0 or less, remove the item entirely from session
        if ($_SESSION['cart'][$item_id] <= 0) {
            unset($_SESSION['cart'][$item_id]);
        }
    }

    $total_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    $item_qty = isset($_SESSION['cart'][$item_id]) ? $_SESSION['cart'][$item_id] : 0;

    echo json_encode([
        'success' => true,
        'cart_count' => $total_count,
        'item_qty' => $item_qty,
        'message' => 'Item removed/decremented'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
?>