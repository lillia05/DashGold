<?php
session_start();

if($_SESSION['status'] != "login"){
    header("location:landing.php");
    exit();
}
?>