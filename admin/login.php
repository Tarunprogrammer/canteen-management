<?php
session_start();
include "../config/db.php";

/* ===============================
CHECK IF ADMIN ALREADY LOGGED IN
================================*/
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

/* ===============================
LOGIN PROCESS
================================*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];

            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Incorrect password";
        }

    } else {
        $error = "Admin account not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Admin Login</title>

<style>

body{
font-family:Arial;
background:#f4f4f4;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.login-box{
background:white;
padding:40px;
width:320px;
border-radius:10px;
box-shadow:0 4px 15px rgba(0,0,0,0.2);
}

h2{
text-align:center;
}

input{
width:100%;
padding:10px;
margin-top:10px;
border:1px solid #ccc;
border-radius:5px;
}

button{
width:100%;
margin-top:15px;
padding:10px;
background:#6c3cff;
color:white;
border:none;
border-radius:5px;
cursor:pointer;
}

.error{
color:red;
text-align:center;
margin-top:10px;
}

</style>

</head>

<body>

<div class="login-box">

<h2>Admin Login</h2>

<?php if($error!=""): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<input type="email" name="email" placeholder="Admin Email" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit">Login</button>

</form>

</div>

</body>
</html>