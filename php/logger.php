<?php
function log_activity($conn, $actor_type, $actor_name, $action_description) {
    // Get the user's real IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    // Prepare and insert the log
    $stmt = $conn->prepare("INSERT INTO activity_logs (actor_type, actor_name, action_description, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $actor_type, $actor_name, $action_description, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}
?>