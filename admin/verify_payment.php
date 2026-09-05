<?php
require 'auth.php';
require '../php/db_connect.php';
require '../php/logger.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require the PHPMailer files
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int)$_POST['user_id'];
    $user_email = $conn->real_escape_string($_POST['user_email']);

    // 1. Fetch the user's First Name from the database
    $user_query = $conn->query("SELECT first_name FROM registrants WHERE id = $user_id");
    $first_name = "Player"; // Default fallback
    
    if ($user_query && $user_query->num_rows > 0) {
        $user_data = $user_query->fetch_assoc();
        $first_name = htmlspecialchars($user_data['first_name']);
    }

    // 2. Generate a Unique QR Token (e.g., JL25-0001-A9F3B2)
    $qr_token = "JL25-" . str_pad($user_id, 4, '0', STR_PAD_LEFT) . "-" . strtoupper(bin2hex(random_bytes(3)));

    // 3. Generate and Save QR Code Image
    $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_token);
    $qr_image_data = file_get_contents($qr_api_url);
    
    $qr_filename = "ticket_" . $qr_token . ".png";
    $qr_filepath = "../qrcodes/" . $qr_filename;
    
    file_put_contents($qr_filepath, $qr_image_data);

    // 4. Update Database to 'Verified' and save the token
    $update_sql = "UPDATE registrants SET payment_status = 'Verified', qr_token = '$qr_token' WHERE id = $user_id";
    
    if ($conn->query($update_sql) === TRUE) {
        
        // 5. Send Email with PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            
            // ---> YOUR GMAIL & APP PASSWORD <---
            $mail->Username   = 'joloverse3@gmail.com'; 
            $mail->Password   = 'nfcqxvznyusrhsqm'; // 16 digits without spaces
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Recipients
            $mail->setFrom('joloverse3@gmail.com', 'Joloverse');
            $mail->addAddress($user_email);

            // Attach the QR Code file
            $mail->addAttachment($qr_filepath, 'Joloverse_Ticket.png');

            // Email Content with personalized First Name greeting
            $mail->isHTML(true);
            $mail->Subject = 'Your Laro Ka JL Registration is Verified!';
            $mail->Body    = "
                <div style='background:#1a0026; padding:30px; border: 3px solid #863fa9; border-radius: 10px; color:#f0eadd; font-family:Arial, sans-serif; text-align:center; max-width: 500px; margin: 0 auto;'>
                    <h1 style='color:#a0862d; margin-top: 0; font-size: 26px;'>PAYMENT VERIFIED</h1>
                    <p style='font-size: 18px; font-weight: bold; color: #a0862d;'>Hi {$first_name},</p>
                    <p style='font-size: 16px; line-height: 1.5; margin-bottom: 25px;'>Your payment for <strong>Laro Ka JL: 25th Birthday Celebration</strong> has been verified successfully!</p>
                    
                    <!-- Event Details Box -->
                    <div style='background: #390055; padding: 20px; border-radius: 8px; text-align: left; margin: 0 auto 25px auto; border: 1px solid #a0862d;'>
                        <h3 style='color: #a0862d; margin-top: 0; margin-bottom: 15px; font-size: 16px; text-align: center; letter-spacing: 1px;'>EVENT DETAILS</h3>
                        
                        <p style='margin: 0 0 10px 0; font-size: 15px; line-height: 1.5;'>
                            <strong style='color: #fff;'>Date:</strong> September 19, 2026<br>
                            <strong style='color: #fff;'>Time:</strong> 3:00 PM to 7:00 PM
                        </p>
                        <p style='margin: 0 0 15px 0; font-size: 15px; line-height: 1.5;'>
                            <strong style='color: #fff;'>Location:</strong> MACK EVENT PLACE<br>
                            <span style='font-size: 13px; color: #ccc;'>LCSM Bldg. 1621 Maceda St. Brgy. 497, Sampaloc, Manila, 1008</span><br>
                            <a href='https://maps.app.goo.gl/eFc4KoxmWRaWXMxd6' target='_blank' style='display: inline-block; margin-top: 10px; color: #2ecc71; text-decoration: none; font-weight: bold; border: 1px solid #2ecc71; padding: 5px 10px; border-radius: 4px; font-size: 12px;'>📍 VIEW ON GOOGLE MAPS</a>
                        </p>
            
                    </div>
                    
                    <p style='font-size: 16px; margin-bottom: 15px;'>Attached to this email is your unique QR Code Ticket.</p>
                    
                    <div style='background: #222; padding: 15px; margin: 0 auto 20px auto; border: 2px dashed #a0862d; max-width: 250px;'>
                        <p style='margin: 0; font-size: 14px; color: #ccc;'>TICKET ID</p>
                        <p style='margin: 5px 0 0 0; font-size: 20px; font-weight: bold; color: #fff; letter-spacing: 1px;'>{$qr_token}</p>
                    </div>
                    
                    <p style='font-size: 14px; color: #ff3333; font-weight: bold; margin-bottom: 0;'>Please download the attached QR code and present it at the event entrance.</p>
                </div>
            ";

            $mail->send();
            
            session_start(); // Ensure session is active to get admin name
            log_activity($conn, 'Admin', $_SESSION['admin_username'], "Verified payment for Player ID: $user_id");
            
            header("Location: index.php?status=verified");
            exit();
            
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>