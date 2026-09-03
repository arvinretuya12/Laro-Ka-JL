<?php
require 'auth.php';
require '../php/db_connect.php';
require '../php/logger.php'; // Included your logger

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require the PHPMailer files
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int)$_POST['user_id'];

    if (!empty($user_id)) {
        // 1. Fetch verified player details (First Name, Email, and existing QR Token)
        $stmt = $conn->prepare("SELECT first_name, email, qr_token FROM registrants WHERE id = ? AND payment_status = 'Verified'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $first_name = htmlspecialchars($row['first_name']);
            $user_email = $row['email'];
            $qr_token = $row['qr_token'];

            // 2. Identify the existing QR Code file path
            $qr_filepath = "../qrcodes/ticket_" . $qr_token . ".png";

            // Fallback: If the QR code image was accidentally deleted from the server folder, quickly regenerate it
            if (!file_exists($qr_filepath)) {
                $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_token);
                $qr_image_data = file_get_contents($qr_api_url);
                file_put_contents($qr_filepath, $qr_image_data);
            }

            // 3. Send Email with PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                
                // Your Gmail & App Password
                $mail->Username   = 'joloverse3@gmail.com'; 
                $mail->Password   = 'nfcqxvznyusrhsqm'; 
                
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Recipients
                $mail->setFrom('joloverse3@gmail.com', 'Joloverse');
                $mail->addAddress($user_email);

                // Attach the QR Code file
                $mail->addAttachment($qr_filepath, 'Joloverse_Ticket.png');

                // Exact Email Content Match
                $mail->isHTML(true);
                $mail->Subject = 'Your Laro Ka JL Registration is Verified! (Resent)'; // Added (Resent) to subject line
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
                                <strong style='color: #fff;'>Location:</strong> To be Announced<br>
                                <span style='font-size: 13px; color: #ccc;'>We will notify you of the location details soon.</span>
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
                
                // 4. Log the action using your standard logger
                if (session_status() === PHP_SESSION_NONE) {
                    session_start(); 
                }
                $current_admin = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'System';
                log_activity($conn, 'Admin', $current_admin, "Resent ticket email to Player ID: $user_id ($user_email)");
                
                header("Location: index.php?status=resent");
                exit();
                
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: Player not found or is not verified.";
        }
    }
}
?>