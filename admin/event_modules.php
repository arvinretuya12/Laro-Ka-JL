<?php
require 'auth.php';
require '../php/db_connect.php';
require '../php/logger.php';

$message = '';

// Handle Adding a Module
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $module_name = $conn->real_escape_string($_POST['module_name']);
    $module_type = $conn->real_escape_string($_POST['module_type']);
    $default_tickets = (int)$_POST['default_tickets']; // Grab the ticket quantity
    
    $sql = "INSERT INTO event_modules (module_name, module_type, default_tickets) VALUES ('$module_name', '$module_type', $default_tickets)";
    if ($conn->query($sql) === TRUE) {
        $message = "<div style='color: #2ecc71; margin-bottom: 15px;'>Module '$module_name' added successfully!</div>";
    
        log_activity($conn, 'Admin', $admin_username, "Added new event module: $module_name ($module_type)");
        }
}

// Handle Deleting a Module
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $delete_id = (int)$_POST['delete_id'];
    
    // 1. Fetch the name BEFORE deleting it so the logger knows what it is
    $name_check = $conn->query("SELECT module_name FROM event_modules WHERE id = $delete_id");
    $deleted_name = ($name_check->num_rows > 0) ? $name_check->fetch_assoc()['module_name'] : "Unknown Booth";

    // 2. Now execute the delete command
    if ($conn->query("DELETE FROM event_modules WHERE id = $delete_id") === TRUE) {
        $message = "<div style='color: #e74c3c; margin-bottom: 15px;'>Module deleted.</div>";
        
        // 3. Log the action successfully
        log_activity($conn, 'Admin', $admin_username, "Deleted event module: $deleted_name");
    }
}

// Fetch all modules
$modules_query = $conn->query("SELECT * FROM event_modules ORDER BY module_type, id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Modules - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        .module-form { background: #390055; padding: 25px; border-radius: 10px; border: 2px solid #863fa9; margin-bottom: 30px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .module-form input, .module-form select { padding: 10px; background: #222; color: #fff; border: 1px solid #863fa9; flex: 1; min-width: 150px; }
        .module-form button { background: #a0862d; color: #fff; padding: 10px 20px; border: none; font-family: 'DetailsFont', sans-serif; cursor: pointer; border-radius: 3px; }
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; border: none; cursor: pointer; border-radius: 3px; font-size: 12px; }
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
        <a href="qr_scanner.php">QR Scanner</a>
        <a href="print_tickets.php">Print Tickets</a>
        <a href="event_modules.php" class="active">Event Modules</a>
        
        <?php if ($admin_role === 'Super Admin'): ?>
            <hr style="border-color: #390055; margin: 20px 0;">
            <a href="manage_admins.php" style="border-color: #ff3333; color: #ff3333;">Manage Admins</a>
            <a href="activity_logs.php">Activity Logs</a>
            <?php endif; ?>

        <a href="logout.php" style="margin-top: 50px; background: #222;">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-family: 'DetailsFont', sans-serif; color: #f0eadd;">EVENT MODULES</h1>
        <p style="color: #ccc; margin-top: -15px; margin-bottom: 30px;">Define the Arcade Games and Food Booths available at the event.</p>
        
        <?php echo $message; ?>

        <!-- Add Module Form -->
        <div class="module-form">
            <h3 style="margin-top: 0; color: #a0862d; width: 100%;">Add New Booth</h3>
            <form method="POST" action="" style="display: flex; gap: 10px; width: 100%;">
                <input type="hidden" name="action" value="add">
                <input type="text" name="module_name" placeholder="e.g. Claw Machine" required style="flex: 2;">
                <select name="module_type" required>
                    <option value="" disabled selected>Category...</option>
                    <option value="Arcade">Arcade Game</option>
                    <option value="Food">Food Booth</option>
                </select>
                <input type="number" name="default_tickets" placeholder="Qty per player" required min="1" style="flex: 1;">
                <button type="submit">ADD BOOTH</button>
            </form>
        </div>

        <!-- Modules Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Booth Name</th>
                    <th>Category</th>
                    <th>Tickets per Player</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($modules_query->num_rows > 0): ?>
                    <?php while($row = $modules_query->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td style="font-weight: bold; color: #fff;"><?php echo htmlspecialchars($row['module_name']); ?></td>
                        <td>
                            <?php 
                            $type = strtolower(trim($row['module_type']));
                            if ($type === 'arcade') {
                                echo "<span style='color: #3498db; font-weight: bold;'>Arcade Game</span>";
                            } elseif ($type === 'food') {
                                echo "<span style='color: #e67e22; font-weight: bold;'>Food Booth</span>";
                            } else {
                                // Fallback just in case the database has a weird value
                                echo "<span style='color: #ff3333;'>" . htmlspecialchars($row['module_type']) . "</span>";
                            }
                            ?>
                        </td>
                        <td style="font-weight: bold; color: #a0862d;"><?php echo $row['default_tickets']; ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Remove this booth?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan='5' style='text-align:center;'>No booths added yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
<?php $conn->close(); ?>