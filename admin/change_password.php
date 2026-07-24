<?php
require 'auth.php'; // Includes the session check
require '../php/db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $admin_id = $_SESSION['admin_id'];

        // Update password and remove the required change flag
        $update_sql = "UPDATE admins SET password = '$hashed_password', require_password_change = 0 WHERE id = $admin_id";
        
        if ($conn->query($update_sql) === TRUE) {
            $_SESSION['require_password_change'] = 0; // Update active session
            header("Location: index.php"); // Send them to dashboard
            exit();
        } else {
            $error = "Failed to update password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        body { justify-content: center; align-items: center; height: 100vh; background-color: #1a0026; }
        .login-box { background: #390055; padding: 40px; border-radius: 15px; border: 5px solid #ff3333; text-align: center; width: 100%; max-width: 400px; box-shadow: 0 0 30px #ff3333; }
        .login-box h2 { font-family: 'DetailsFont', sans-serif; color: #ff3333; margin-top: 0; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; color: #f0eadd; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 2px solid #863fa9; background: #222; color: #fff; box-sizing: border-box; }
        .btn-login { width: 100%; background: #ff3333; color: #fff; padding: 15px; border: none; font-family: 'DetailsFont', sans-serif; font-size: 18px; cursor: pointer; margin-top: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>SECURITY ALERT</h2>
        <p style="color: #ccc; font-size: 14px; margin-bottom: 20px;">You must change your nominated password before accessing the terminal.</p>
        
        <?php if($error): ?><div style="color: #ff3333; margin-bottom: 15px; font-weight: bold;"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-login">SAVE & CONTINUE</button>
        </form>
    </div>
</body>
</html>