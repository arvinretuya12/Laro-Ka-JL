<?php
require 'auth.php';
require '../php/db_connect.php';
require '../php/logger.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int)$_POST['user_id'];
    $user_email = $conn->real_escape_string($_POST['user_email']);
    $first_name = htmlspecialchars($_POST['first_name']);
    $issue_type = $conn->real_escape_string($_POST['issue_type']); // 'Short' or 'Invalid'

    // 1. Update Database Status
    $update_sql = "UPDATE registrants SET payment_status = '$issue_type' WHERE id = $user_id";
    
    if ($conn->query($update_sql) === TRUE) {
        
        // 2. Prepare the Email Content based on the Issue Type
        if ($issue_type == 'Short') {
            $subject = "Action Required: Incomplete Payment for Laro Ka JL";
            $message_title = "INCOMPLETE PAYMENT";
            $message_body = "We received your GCash payment, but the amount sent was incomplete. The ticket price is <strong>PHP 1000</strong>.<br><br>To receive your unique QR ticket, please send the remaining balance to the same GCash number and <strong>reply directly to this email</strong> with the new screenshot.";
        } else {
            $subject = "Action Required: Invalid Payment Receipt for Laro Ka JL";
            $message_title = "INVALID RECEIPT";
            $message_body = "We are unable to verify the GCash receipt you uploaded. The image may be unreadable, or the reference number could not be found.<br><br>Please <strong>reply directly to this email</strong> and attach a clear screenshot of your valid GCash transaction so we can verify your ticket.";
        }

        // 3. Send the Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            
            // ---> YOUR GMAIL & APP PASSWORD <---
            $mail->Username   = 'joloverse3@gmail.com'; 
            $mail->Password   = 'nfcqxvznyusrhsqm'; 
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('joloverse3@gmail.com', 'Joloverse');
            $mail->addAddress($user_email);
            $mail->addReplyTo('joloverse3@gmail.com', 'Joloverse Admin');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = "
                <div style='background:#1a0026; padding:30px; border: 3px solid #e74c3c; border-radius: 10px; color:#f0eadd; font-family:Arial, sans-serif; text-align:center; max-width: 500px; margin: 0 auto;'>
                    <h1 style='color:#e74c3c; margin-top: 0;'>{$message_title}</h1>
                    <p style='font-size: 18px; font-weight: bold; color: #a0862d;'>Hi {$first_name},</p>
                    <p style='font-size: 16px; line-height: 1.5; text-align: left;'>{$message_body}</p>
                    <div style='background: #390055; padding: 15px; margin: 20px 0; border: 1px solid #863fa9;'>
                        <p style='margin: 0; font-size: 14px; color: #fff;'>Need help? Just hit reply on this email and our team will assist you.</p>
                    </div>
                </div>
            ";

            $mail->send();

            session_start();
            log_activity($conn, 'Admin', $_SESSION['admin_username'], "Flagged Player ID: $user_id for issue: $issue_type");
            
            header("Location: index.php?status=flagged");
            exit();
            
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>