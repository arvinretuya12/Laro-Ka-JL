<?php
require 'auth.php';
require '../php/db_connect.php';
require '../php/logger.php';

// Tell the browser we are replying with JSON
header('Content-Type: application/json');

// Get the raw POST data from JavaScript
$data = json_decode(file_get_contents("php://input"));

if (isset($data->action_description)) {
    $action_desc = $conn->real_escape_string($data->action_description);
    
    // Log the action using the currently logged-in admin
    log_activity($conn, 'Admin', $admin_username, $action_desc);
    
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "No action provided"]);
}

$conn->close();
?>