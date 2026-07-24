<?php
session_start();

// 1. If not logged in, go to login page
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 2. If they need to change their password, restrict them ONLY to the change password page
if (isset($_SESSION['require_password_change']) && $_SESSION['require_password_change'] == 1) {
    if (basename($_SERVER['PHP_SELF']) !== 'change_password.php') {
        header("Location: change_password.php");
        exit();
    }
}

$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$admin_role = $_SESSION['admin_role']; // 'Super Admin' or 'Admin'
?>