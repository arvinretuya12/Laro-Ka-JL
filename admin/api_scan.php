<?php
require '../php/db_connect.php';
require '../php/logger.php';
header('Content-Type: application/json'); // Tell the browser we are sending JSON data back

// Get the raw POST data sent by our JavaScript scanner
$data = json_decode(file_get_contents("php://input"));

if (isset($data->qr_token)) {
    $token = $conn->real_escape_string($data->qr_token);

    // Look for this exact token in the database
    $sql = "SELECT id, first_name, last_name, is_scanned, scan_timestamp FROM registrants WHERE qr_token = '$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // CHECK 1: Has it already been used?
        if ($row['is_scanned'] == 1) {
            $scan_time = date('M d, Y - h:i A', strtotime($row['scan_timestamp']));
            echo json_encode([
                "status" => "error", 
                "message" => "TICKET ALREADY USED", 
                "details" => "Scanned previously on: <br>$scan_time"
            ]);
        } else {
            // CHECK 2: Ticket is valid! Lock it and grant access.
            $update_sql = "UPDATE registrants SET is_scanned = 1, scan_timestamp = NOW() WHERE id = " . $row['id'];
            
            if ($conn->query($update_sql) === TRUE) {
                $player_name = strtoupper(htmlspecialchars($row['first_name'] . " " . $row['last_name']));
                echo json_encode([
                    "status" => "success", 
                    "message" => "ACCESS GRANTED", 
                    "details" => "Player: $player_name<br>Please hand them their physical tickets."
                ]);

                session_start();
                $admin_name = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Scanner';
                log_activity($conn, 'Admin', $admin_name, "Scanned QR and granted entry to Player ID: " . $row['id']);
            } else {
                echo json_encode(["status" => "error", "message" => "SYSTEM ERROR", "details" => "Failed to update database."]);
            }
        }
    } else {
        // CHECK 3: Fake or invalid QR code
        echo json_encode([
            "status" => "error", 
            "message" => "INVALID TICKET", 
            "details" => "This QR code does not exist in our system."
        ]);
        session_start();
        $admin_name = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Scanner';
        log_activity($conn, 'Admin', $admin_name, "Scanned QR and INVALID QR to Player ID: " . $row['id']);
    }
} else {
    echo json_encode(["status" => "error", "message" => "NO DATA", "details" => "No QR token received."]);
}

$conn->close();
?>