<?php
require 'auth.php';
require '../php/db_connect.php';

// STRICT SECURITY: Kick out regular admins
if ($admin_role !== 'Super Admin') {
    header("Location: index.php");
    exit();
}

// --- NEW: FETCH VISITOR COUNTS ---
$total_query = $conn->query("SELECT COUNT(*) as cnt FROM activity_logs WHERE actor_type = 'Visitor'");
$total_visitors = $total_query->fetch_assoc()['cnt'];

$month_query = $conn->query("SELECT COUNT(*) as cnt FROM activity_logs WHERE actor_type = 'Visitor' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$month_visitors = $month_query->fetch_assoc()['cnt'];

$today_query = $conn->query("SELECT COUNT(*) as cnt FROM activity_logs WHERE actor_type = 'Visitor' AND DATE(created_at) = CURDATE()");
$today_visitors = $today_query->fetch_assoc()['cnt'];
// ---------------------------------

// Fetch the latest 500 logs
$logs_query = $conn->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 500");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css?v=3.0">
</head>
<body>

    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <div style="color: #f0eadd; font-size: 12px; margin-bottom: 20px; text-align: center;">
            Logged in as: <strong style="color: #a0862d;"><?php echo htmlspecialchars($admin_username); ?></strong><br>
            Role: <em><?php echo htmlspecialchars($admin_role); ?></em>
        </div>
        <a href="index.php">Dashboard</a>
        <a href="qr_scanner.php">QR Scanner</a>
        <a href="print_tickets.php">Print Tickets</a>
        <a href="event_modules.php">Event Modules</a>
        
        <?php if ($admin_role === 'Super Admin'): ?>
            <hr style="border-color: #390055; margin: 20px 0;">
            <a href="manage_admins.php" style="border-color: #ff3333; color: #ff3333;">Manage Admins</a>
            <a href="activity_logs.php" class="active" style="border-color: #3498db; color: #3498db;">Activity Logs</a>
        <?php endif; ?>

        <a href="logout.php" style="margin-top: 50px; background: #222;">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-family: 'DetailsFont', sans-serif; color: #f0eadd;">SYSTEM LOGS</h1>
        <p style="color: #ccc; margin-top: -15px; margin-bottom: 30px;">Monitoring all admin actions and player registrations.</p>
        
        <!-- NEW: VISITOR METRICS -->
        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div style="flex: 1; background: #222; border: 2px solid #863fa9; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: #a0862d; margin: 0 0 10px 0; font-size: 14px;">TODAY'S VISITORS</h3>
                <p style="color: #fff; font-size: 28px; font-weight: bold; margin: 0;"><?php echo $today_visitors; ?></p>
            </div>
            <div style="flex: 1; background: #222; border: 2px solid #863fa9; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: #a0862d; margin: 0 0 10px 0; font-size: 14px;">THIS MONTH</h3>
                <p style="color: #fff; font-size: 28px; font-weight: bold; margin: 0;"><?php echo $month_visitors; ?></p>
            </div>
            <div style="flex: 1; background: #222; border: 2px solid #863fa9; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: #a0862d; margin: 0 0 10px 0; font-size: 14px;">TOTAL VISITORS</h3>
                <p style="color: #fff; font-size: 28px; font-weight: bold; margin: 0;"><?php echo $total_visitors; ?></p>
            </div>
        </div>
        <!-- ---------------------- -->
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Type</th>
                    <th>User / Actor</th>
                    <th>Action Performed</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs_query->num_rows > 0): ?>
                    <?php while($row = $logs_query->fetch_assoc()): ?>
                    <tr>
                        <td style="font-size: 12px; color: #aaa;"><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <?php 
                            if ($row['actor_type'] == 'Admin') echo "<span style='color: #e74c3c; font-weight: bold;'>ADMIN</span>";
                            elseif ($row['actor_type'] == 'Registrant') echo "<span style='color: #2ecc71; font-weight: bold;'>PLAYER</span>";
                            elseif ($row['actor_type'] == 'Visitor') echo "<span style='color: #3498db; font-weight: bold;'>VISITOR</span>";
                            else echo "<span style='color: #f1c40f; font-weight: bold;'>SYSTEM</span>";
                            ?>
                        </td>
                        <td style="font-weight: bold; color: #fff;"><?php echo htmlspecialchars($row['actor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['action_description']); ?></td>
                        <td style="font-size: 12px; font-family: monospace; color: #888;"><?php echo htmlspecialchars($row['ip_address']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan='5' style='text-align:center;'>No activity logged yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
<?php $conn->close(); ?>