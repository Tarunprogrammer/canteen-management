<?php
session_start();
include "../config/db.php";

/* ===============================
1. CHECK LOGIN SESSION
================================*/
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../user/login.php");
    exit();
}

$session_id = intval($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? "User";

/* ===============================
2. VERIFY USER FROM DATABASE
================================*/
$user_stmt = $conn->prepare("SELECT id,name FROM users WHERE id=? LIMIT 1");
$user_stmt->bind_param("i",$session_id);
$user_stmt->execute();
$user_res = $user_stmt->get_result();

if ($user_res->num_rows === 0) {
    die("User not found");
}

$user_data = $user_res->fetch_assoc();
$verified_user_id = $user_data['id'];
$user_name = $user_data['name'];

/* ===============================
3. CHECK CART
================================*/
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: ../index.php");
    exit();
}

$cart = $_SESSION['cart'];
$payment_method = $_POST['payment_method'] ?? "cash";

/* ===============================
4. START TRANSACTION
================================*/
$conn->begin_transaction();

$success=false;
$error_message="";
$order_id=null;

try {

// Insert directly as 'pending' so it triggers the admin dashboard notification
$order_stmt = $conn->prepare("
INSERT INTO orders
(user_id,payment_method,status,created_at,updated_at)
VALUES (?,?, 'pending',NOW(),NOW())
");

$order_stmt->bind_param("is",$verified_user_id,$payment_method);
$order_stmt->execute();

$order_id=$conn->insert_id;

foreach($cart as $item_id=>$qty){

$item_id=intval($item_id);
$qty=intval($qty);

$check_stmt=$conn->prepare("SELECT stock,name FROM menu_items WHERE id=? FOR UPDATE");
$check_stmt->bind_param("i",$item_id);
$check_stmt->execute();

$item=$check_stmt->get_result()->fetch_assoc();

if(!$item){
throw new Exception("Item not found");
}

if($item['stock']<$qty){
throw new Exception("Insufficient stock for ".$item['name']);
}

$item_stmt=$conn->prepare("
INSERT INTO order_items
(order_id,item_id,quantity)
VALUES (?,?,?)
");

$item_stmt->bind_param("iii",$order_id,$item_id,$qty);
$item_stmt->execute();

$stock_stmt=$conn->prepare("
UPDATE menu_items
SET stock=stock-?
WHERE id=?
");

$stock_stmt->bind_param("ii",$qty,$item_id);
$stock_stmt->execute();
}

$conn->commit();

unset($_SESSION['cart']);
$success=true;

}
catch(Exception $e){

$conn->rollback();
$error_message=$e->getMessage();

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Status</title>
<style>
body{font-family:Arial;background:#f4f4f4;display:flex;justify-content:center;align-items:center;height:100vh;}
.box{background:white;padding:40px;border-radius:10px;text-align:center;box-shadow:0 4px 10px rgba(0,0,0,0.2);}
.success{color:green;font-size:22px;font-weight:bold;}
.error{color:red;font-size:20px;font-weight:bold;}
button{margin-top:20px;padding:12px 20px;background:#6c3cff;color:white;border:none;border-radius:5px;cursor:pointer;}
</style>
</head>
<body>

<div class="box">
<?php if($success): ?>
    <div class="success">✅ Order Placed Successfully</div>
    <p>Order ID : <b>#<?= $order_id ?></b></p>
    <p>Please wait while the canteen admin processes your order.</p>
    <a href="../index.php"><button>Go Back Home</button></a>
<?php else: ?>
    <div class="error">❌ Order Failed</div>
    <p><?= htmlspecialchars($error_message) ?></p>
    <a href="../index.php"><button>Place New Order</button></a>
<?php endif; ?>
</div>

</body>
</html>