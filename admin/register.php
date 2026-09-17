<?php
session_start();
include "../config/db.php";

$msg="";

if($_SERVER["REQUEST_METHOD"]=="POST"){

$name=$_POST['name'];
$email=$_POST['email'];
$password=$_POST['password'];

$password=password_hash($password,PASSWORD_DEFAULT);

$stmt=$conn->prepare("INSERT INTO admins (name,email,password) VALUES (?,?,?)");
$stmt->bind_param("sss",$name,$email,$password);

if($stmt->execute()){
$msg="Admin registered successfully";
}else{
$msg="Error registering admin";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Register</title>

<style>
body{
font-family:Arial;
background:#f5f5f5;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.box{
background:white;
padding:40px;
border-radius:10px;
width:300px;
}

input{
width:100%;
padding:10px;
margin-top:10px;
}

button{
width:100%;
padding:10px;
margin-top:15px;
background:#6c3cff;
color:white;
border:none;
cursor:pointer;
}
</style>

</head>

<body>

<div class="box">

<h2>Admin Register</h2>

<p><?= $msg ?></p>

<form method="POST">

<input type="text" name="name" placeholder="Admin Name" required>

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit">Register</button>

</form>

</div>

</body>
</html>