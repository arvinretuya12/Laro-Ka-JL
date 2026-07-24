<?php
$host = 'localhost';
$user = 'root'; // Default XAMPP user
$pass = 'joloverseadmin1234'; // Default XAMPP password is empty
$db = 'laro_event_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>