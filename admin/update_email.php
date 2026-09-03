<?php
require 'auth.php';
require '../php/db_connect.php';
require '../php/logger.php'; // Include your custom logger!

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
                // Log action using your existing logger function
                if (session_status() === PHP_SESSION_NONE) {
                    session_start(); 
                }
                $current_admin = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'System';
                
                log_activity($conn, 'Admin', $current_admin, "Updated player #{$user_id} email from '{$old_email}' to '{$new_email}'");
            }
        }
    }
}

// Redirect back to dashboard
header("Location: index.php");
exit();
?>