<?php
require 'auth.php';
require '../php/db_connect.php';
require '../php/logger.php';

// Only allow Super Admin to change this setting
if ($_SERVER["REQUEST_METHOD"] == "POST" && $admin_role === 'Super Admin') {
    $new_limit = (int)$_POST['new_limit'];
    
    $stmt = $conn->prepare("UPDATE event_settings SET setting_value = ? WHERE setting_key = 'max_registrants'");
    $stmt->bind_param("i", $new_limit);
    
    if ($stmt->execute()) {
        session_start();
        log_activity($conn, 'Admin', $_SESSION['admin_username'], "Updated maximum registrant limit to: $new_limit");
    }
    $stmt->close();
}

header("Location: index.php");
exit();
?>