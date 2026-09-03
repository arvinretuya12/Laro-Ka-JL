<?php
require 'auth.php';
require '../php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $new_email = trim($_POST['new_email']);

    if (!empty($user_id) && filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        // Fetch old email for logging purposes
        $stmt = $conn->prepare("SELECT email FROM registrants WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $old_email = $row['email'];

            // Update the email address
            $update_stmt = $conn->prepare("UPDATE registrants SET email = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_email, $user_id);

            if ($update_stmt->execute()) {
                // Log action to activity_logs
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $log_msg = "Updated player #{$user_id} email from '{$old_email}' to '{$new_email}'";
                
                $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_username, action, ip_address) VALUES (?, ?, ?)");
                $log_stmt->bind_param("sss", $admin_username, $log_msg, $ip);
                $log_stmt->execute();
            }
        }
    }
}

header("Location: index.php");
exit();
?>