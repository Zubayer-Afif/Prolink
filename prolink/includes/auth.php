<?php
session_start();

function checkUser($role)
{
    if (!isset($_SESSION['user_id']) || $_SESSION['type'] != $role) {
        header("Location: ../auth/login.php");
        exit();
    }
}
?>