<?php
session_start();

// FIX: If they are already logged in, bounce them directly to the dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

require '../php/db_connect.php';

// Auto-create default Super Admin if the table is empty
$check_empty = $conn->query("SELECT id FROM admins");
if ($check_empty->num_rows == 0) {
    $default_password = password_hash('U2X{sD2F!irda)_#Jk_`', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admins (username, password, role) VALUES ('superadmin', '$default_password', 'Super Admin')");
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

   // Fetch the flag as well
    $sql = "SELECT id, username, password, role, require_password_change FROM admins WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            // Set all session variables
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_role'] = $row['role'];
            $_SESSION['require_password_change'] = $row['require_password_change'];
            
            // Route them based on the flag
            if ($row['require_password_change'] == 1) {
                header("Location: change_password.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin account not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        body { justify-content: center; align-items: center; height: 100vh; background-color: #1a0026; }
        .login-box { background: #390055; padding: 40px; border-radius: 15px; border: 5px solid #863fa9; text-align: center; width: 100%; max-width: 400px; box-shadow: 0 0 30px #863fa9; }
        .login-box h2 { font-family: 'DetailsFont', sans-serif; color: #a0862d; margin-top: 0; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; color: #f0eadd; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 2px solid #863fa9; background: #222; color: #fff; box-sizing: border-box; outline: none; }
        .form-group input:focus { border-color: #a0862d; }
        .btn-login { width: 100%; background: #a0862d; color: #fff; padding: 15px; border: none; font-family: 'DetailsFont', sans-serif; font-size: 18px; cursor: pointer; margin-top: 10px; border-radius: 5px; }
        .btn-login:hover { background: #c4a73e; }
        .error { color: #ff3333; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>SYSTEM LOGIN</h2>
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">ACCESS TERMINAL</button>
        </form>
    </div>
</body>
</html>