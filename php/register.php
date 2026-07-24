<?php
require 'db_connect.php';
require 'logger.php'; // FIXED: Path assumes this script is inside the php/ folder

// Tell the browser we are replying with JSON data
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $middle_initial = $conn->real_escape_string($_POST['middle_initial']);
    $email = $conn->real_escape_string($_POST['email']);
    $social_link = $conn->real_escape_string($_POST['social_link']);
    $age = (int)$_POST['age'];
    
    // Define full name early so it is available for all logs
    // Define full name early so it is available for all logs
    $full_name = $first_name . ' ' . $last_name;

    // // --- NEW: CHECK EVENT CAPACITY ---
    // $limit_check = $conn->query("SELECT setting_value FROM event_settings WHERE setting_key = 'max_registrants'");
    // $max_registrants = $limit_check->fetch_assoc()['setting_value'];

    // $count_check = $conn->query("SELECT COUNT(*) as total FROM registrants");
    // $current_total = $count_check->fetch_assoc()['total'];

    // if ($current_total >= $max_registrants) {
    //     echo json_encode([
    //         "status" => "full", 
    //         "message" => "Registration is officially closed! We have reached maximum capacity."
    //     ]);
        
    //     log_activity($conn, 'System', $full_name, "Attempted to register but event was full.");
    //     exit();
    // }

    // 1. CHECK FOR DUPLICATES (Check for BOTH Name OR Email)
    $check_sql = "SELECT first_name, last_name, email FROM registrants 
                  WHERE email = '$email' OR (first_name = '$first_name' AND last_name = '$last_name')";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $name_exists = false;
        $email_exists = false;
        
        // Loop through results to see exactly what matched
        while($row = $result->fetch_assoc()) {
            if (strcasecmp($row['first_name'], $first_name) == 0 && strcasecmp($row['last_name'], $last_name) == 0) {
                $name_exists = true;
            }
            if (strcasecmp($row['email'], $email) == 0) {
                $email_exists = true;
            }
        }
        
        if ($name_exists) {
            // The person is already registered
            echo json_encode(["status" => "duplicate"]);
            exit();
        } else if ($email_exists) {
            // The email is used by someone else
            echo json_encode(["status" => "email_taken"]);
            exit();
        }
    }

    // 2. PROCEED WITH FILE UPLOAD
    $target_dir = "../uploads/";
    $file_extension = pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION);
    $new_filename = uniqid("pay_") . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO registrants (first_name, last_name, middle_initial, email, social_link, age, payment_proof) 
                VALUES ('$first_name', '$last_name', '$middle_initial', '$email', '$social_link', $age, '$new_filename')";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success"]);
            log_activity($conn, 'Registrant', $full_name, "Submitted a new event registration.");
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Database error"]);
            // FIXED: Added missing parameters
            log_activity($conn, 'Registrant', $full_name, "Failed database insertion.");
            exit();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Upload failed"]);
        // FIXED: Used the pre-defined full name
        log_activity($conn, 'Registrant', $full_name, "Upload failed.");
        exit();
    }
}
$conn->close();
?>