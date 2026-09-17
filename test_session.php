<?php
session_start();

if(isset($_SESSION['user_id'])){
    echo "Logged user id: ".$_SESSION['user_id'];
}else{
    echo "No user logged in";
}
?>