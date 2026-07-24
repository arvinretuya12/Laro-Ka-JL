<?php
require 'auth.php';
require '../php/db_connect.php';

// Strict security: Kick out normal Admins
if ($admin_role !== 'Super Admin') {
    header("Location: index.php");
    exit();
}

$message = '';

// Handle Form Submissions (Add or Reset)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ACTION 1: ADD NEW ADMIN
    if (isset($_POST['action']) && $_POST['action'] == 'add_admin') {
        $new_username = $conn->real_escape_string($_POST['username']);
        $new_password = $_POST['initial_password'];
        $new_role = $conn->real_escape_string($_POST['role']);
        
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        
        // require_password_change defaults to 1 per our database schema
        $sql = "INSERT INTO admins (username, password, role) VALUES ('$new_username', '$hashed', '$new_role')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "<div style='color: #2ecc71; margin-bottom: 15px;'>Admin '$new_username' added successfully! They must change their password on first login.</div>";
        } else {
            $message = "<div style='color: #ff3333; margin-bottom: 15px;'>Error: Username might already exist.</div>";
        }
    }
    
    // ACTION 2: RESET PASSWORD
    if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
        $target_id = (int)$_POST['target_id'];
        $reset_password = $_POST['reset_password_val'];
        
        $hashed = password_hash($reset_password, PASSWORD_DEFAULT);
        
        // Set new password AND force change on next login
        $sql = "UPDATE admins SET password = '$hashed', require_password_change = 1 WHERE id = $target_id";
        
        if ($conn->query($sql) === TRUE) {
            $message = "<div style='color: #2ecc71; margin-bottom: 15px;'>Password reset successfully! They will be forced to change it on next login.</div>";
        }
    }
}

// Fetch all admins
$admins_query = $conn->query("SELECT id, username, role, require_password_change, created_at FROM admins ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        .admin-form-box { background: #390055; padding: 25px; border-radius: 10px; border: 2px solid #863fa9; margin-bottom: 30px; }
        .admin-form-box input, .admin-form-box select { padding: 10px; background: #222; color: #fff; border: 1px solid #863fa9; margin-right: 10px; }
        .admin-form-box button { background: #a0862d; color: #fff; padding: 10px 20px; border: none; font-family: 'DetailsFont', sans-serif; cursor: pointer; border-radius: 3px; }
        .reset-form { display: inline-flex; align-items: center; gap: 10px; }
        .reset-form input { padding: 5px; width: 120px; }
        .btn-reset { background: #e67e22; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <div style="color: #f0eadd; font-size: 12px; margin-bottom: 20px; text-align: center;">
            Logged in as: <strong style="color: #a0862d;"><?php echo htmlspecialchars($admin_username); ?></strong><br>
            Role: <em><?php echo htmlspecialchars($admin_role); ?></em>
        </div>
        <a href="index.php">Dashboard</a>
        <hr style="border-color: #390055; margin: 20px 0;">
        <a href="manage_admins.php" class="active" style="border-color: #ff3333; color: #ff3333;">Manage Admins</a>
        <a href="activity_logs.php">Activity Logs</a>
        <a href="logout.php" style="margin-top: 50px; background: #222;">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-family: 'DetailsFont', sans-serif; color: #f0eadd;">MANAGE SYSTEM ADMINS</h1>
        
        <?php echo $message; ?>

        <!-- Add New Admin Form -->
        <div class="admin-form-box">
            <h3 style="margin-top: 0; color: #a0862d;">Nominate New Admin</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_admin">
                <input type="text" name="username" placeholder="Username" required>
                <input type="text" name="initial_password" placeholder="Nominate Password" required>
                <select name="role" required>
                    <option value="Admin">Admin</option>
                    <option value="Super Admin">Super Admin</option>
                </select>
                <button type="submit">CREATE ACCOUNT</button>
            </form>
        </div>

        <!-- Admins Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Reset Password</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $admins_query->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo $row['role']; ?></td>
                    <td>
                        <?php if($row['require_password_change'] == 1): ?>
                            <span style="color: #f39c12; font-size: 12px;">Pending Change</span>
                        <?php else: ?>
                            <span style="color: #2ecc71; font-size: 12px;">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Reset Password Form -->
                        <form method="POST" action="" class="reset-form">
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="target_id" value="<?php echo $row['id']; ?>">
                            <input type="text" name="reset_password_val" placeholder="New Password" required>
                            <button type="submit" class="btn-reset">FORCE RESET</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
<?php $conn->close(); ?>