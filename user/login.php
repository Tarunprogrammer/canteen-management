<?php
session_start();
include "../config/db.php";

/* ===============================
CHECK IF USER ALREADY LOGGED IN
================================*/

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'user') {
    header("Location: ../index.php");
    exit();
}

$error = "";

/* ===============================
LOGIN PROCESS
================================*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        /* PASSWORD CHECK */
        if ($password == $user['password']) {

            /* Only allow role = user */
            if ($user['role'] != 'user') {
                $error = "Admin must login from admin panel.";
            } else {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                header("Location: ../index.php");
                exit();
            }

        } else {
            $error = "Incorrect password";
        }

    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>User Login</title>

<style>

body{
font-family:Arial;
background:#f5f5f5;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.login-box{
background:white;
padding:40px;
width:300px;
border-radius:8px;
box-shadow:0 3px 10px rgba(0,0,0,0.2);
}

input{
width:100%;
padding:10px;
margin-top:10px;
}

button{
margin-top:15px;
width:100%;
padding:10px;
background:#6c3cff;
color:white;
border:none;
cursor:pointer;
}

.error{
color:red;
margin-top:10px;
}

</style>

</head>

<body>

<div class="login-box">

<h2>User Login</h2>

<?php if($error!=""): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit">Login</button>

</form>

</div>

</body>
</html>