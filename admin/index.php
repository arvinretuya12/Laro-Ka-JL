<?php
require 'auth.php';
require '../php/db_connect.php';

$total_query = $conn->query("SELECT COUNT(*) as count FROM registrants");
$total_registrants = $total_query->fetch_assoc()['count'];

$verified_query = $conn->query("SELECT COUNT(*) as count FROM registrants WHERE payment_status = 'Verified'");
$total_verified = $verified_query->fetch_assoc()['count'];

$total_earnings = $total_verified * 1000;
$limit_query = $conn->query("SELECT setting_value FROM event_settings WHERE setting_key = 'max_registrants'");
$max_limit = $limit_query->fetch_assoc()['setting_value'];
$registrants_query = $conn->query("SELECT * FROM registrants ORDER BY registration_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <div style="color: #f0eadd; font-size: 12px; margin-bottom: 20px; text-align: center;">
            Logged in as: <strong style="color: #a0862d;"><?php echo htmlspecialchars($admin_username); ?></strong><br>
            Role: <em><?php echo htmlspecialchars($admin_role); ?></em>
        </div>
        <a href="index.php" class="active">Dashboard</a>
        <a href="qr_scanner.php">QR Scanner</a>
        <a href="print_tickets.php">Print Tickets</a>
        <a href="event_modules.php">Event Modules</a>
        
        <?php if ($admin_role === 'Super Admin'): ?>
            <hr style="border-color: #390055; margin: 20px 0;">
            <a href="manage_admins.php" style="border-color: #ff3333; color: #ff3333;">Manage Admins</a>
            <a href="activity_logs.php">Activity Logs</a>
        <?php endif; ?>

        <a href="logout.php" style="margin-top: 50px; background: #222;">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-family: 'DetailsFont', sans-serif; color: #f0eadd;">PLAYER DATABASE</h1>
        
        <div class="dashboard-cards">
            <div class="card" style="position: relative;">
                <h3>TOTAL PLAYERS</h3>
                <p><?php echo $total_registrants; ?> <span style="font-size: 16px; color: #888;">/ <?php echo $max_limit; ?></span></p>
                
                <?php if ($admin_role === 'Super Admin'): ?>
                    <button onclick="openLimitModal()" style="position: absolute; top: 15px; right: 15px; background: #863fa9; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px;">Edit Limit</button>
                <?php endif; ?>
            </div>
            <div class="card">
                <h3>VERIFIED PAYMENTS</h3>
                <p><?php echo $total_verified; ?></p>
            </div>
            <div class="card">
                <h3>TOTAL EARNINGS</h3>
                <p>PHP <?php echo number_format($total_earnings, 2); ?></p>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Proof of Payment</th>
                    <th>Status</th>
                    <th>Check-In</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($registrants_query->num_rows > 0) {
                    while($row = $registrants_query->fetch_assoc()) {
                        $status = $row['payment_status'];
                        $status_class = 'status-pending';
                        if ($status == 'Verified') $status_class = 'status-verified';
                        if ($status == 'Short') $status_class = 'status-short';
                        if ($status == 'Invalid') $status_class = 'status-invalid';
                        
                        echo "<tr>";
                        echo "<td>#" . $row['id'] . "</td>";
                        
                        // --- Name & Ticket ID Column ---
                        echo "<td style='font-weight: bold; color: #fff;'>";
                        echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']);
                        
                        if (!empty($row['qr_token'])) {
                            echo "<br><span style='font-size: 11px; color: #a0862d; font-family: monospace; font-weight: normal;'>🎟️ " . htmlspecialchars($row['qr_token']) . "</span>";
                        } else {
                            echo "<br><span style='font-size: 11px; color: #666; font-style: italic; font-weight: normal;'>(Pending Verification)</span>";
                        }
                        echo "</td>";
                        
                        // --- UPDATED: Email Column with Edit Icon ---
                        echo "<td>";
                        echo htmlspecialchars($row['email']);
                        echo " <button type='button' title='Edit Email' onclick=\"openEditEmailModal({$row['id']}, '" . htmlspecialchars($row['email'], ENT_QUOTES) . "')\" style='background: none; border: none; color: #a0862d; cursor: pointer; font-size: 13px; margin-left: 5px;'>✏️</button>";
                        echo "</td>";

                        echo "<td><a href='../uploads/" . htmlspecialchars($row['payment_proof']) . "' target='_blank' class='btn-view'>View Receipt</a></td>";
                        echo "<td><span class='status-badge {$status_class}'>{$status}</span></td>";
                        
                        // Check-In Status Column
                        echo "<td>";
                        if ($status == 'Verified') {
                            if ($row['is_scanned'] == 1) {
                                echo "<span style='background: #2ecc71; color: white; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;'>SCANNED IN</span>";
                            } else {
                                echo "<span style='background: #f39c12; color: white; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;'>WAITING</span>";
                            }
                        } else {
                            echo "<span style='color: #888; font-size: 11px;'>N/A</span>";
                        }
                        echo "</td>";

                        // --- UPDATED: Action Column ---
                        echo "<td>";
                        if ($status !== 'Verified') {
                            echo "<form action='verify_payment.php' method='POST' style='display:inline;' onsubmit='return disableVerifyBtn(this);'>
                                    <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                                    <input type='hidden' name='user_email' value='" . htmlspecialchars($row['email']) . "'>
                                    <button type='submit' class='btn-verify'>Verify</button>
                                  </form> ";
                                  
                            echo "<button type='button' class='btn-flag' onclick=\"openFlagModal({$row['id']}, '" . htmlspecialchars($row['email']) . "', '" . htmlspecialchars($row['first_name']) . "')\">Flag Issue</button>";
                        } else {
                            echo "<form action='resend_email.php' method='POST' style='display:inline;'>
                                    <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                                    <button type='submit' onclick=\"return confirm('Resend ticket email to this player?')\" style='background: #3498db; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; font-size: 12px; font-weight: bold;'>Resend Ticket</button>
                                  </form>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;'>No players registered yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- EDIT EMAIL MODAL -->
    <div class="admin-modal-overlay" id="editEmailModal">
        <div class="admin-modal-box">
            <h3>EDIT PLAYER EMAIL</h3>
            <form action="update_email.php" method="POST">
                <input type="hidden" name="user_id" id="edit_email_user_id">
                
                <p style="color: #ccc; font-size: 14px; margin-bottom: 10px;">Enter the corrected email address:</p>
                <input type="email" name="new_email" id="edit_email_input" required style="width: 100%; padding: 10px; margin-bottom: 15px; background: #222; color: #fff; border: 1px solid #863fa9; box-sizing: border-box;">
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="background: #2ecc71; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 3px; width: 100%; font-weight: bold;">Update Email</button>
                    <button type="button" onclick="closeEditEmailModal()" style="background: #555; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 3px; width: 100%;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT LIMIT MODAL -->
    <div class="admin-modal-overlay" id="limitModal">
        <div class="admin-modal-box">
            <h3>SET EVENT CAPACITY</h3>
            <form action="update_limit.php" method="POST">
                <p style="color: #ccc; font-size: 14px; margin-bottom: 10px;">Enter the maximum number of players allowed to register:</p>
                <input type="number" name="new_limit" value="<?php echo $max_limit; ?>" min="1" required style="width: 100%; padding: 10px; margin-bottom: 15px; background: #222; color: #fff; border: 1px solid #863fa9; box-sizing: border-box;">
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="background: #2ecc71; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 3px; width: 100%; font-weight: bold;">Save Limit</button>
                    <button type="button" onclick="closeLimitModal()" style="background: #555; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 3px; width: 100%;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FLAG ISSUE MODAL -->
    <div class="admin-modal-overlay" id="flagModal">
        <div class="admin-modal-box">
            <h3>FLAG PAYMENT ISSUE</h3>
            <form action="flag_payment.php" method="POST">
                <input type="hidden" name="user_id" id="flag_user_id">
                <input type="hidden" name="user_email" id="flag_user_email">
                <input type="hidden" name="first_name" id="flag_first_name">
                
                <p style="color: #ccc; font-size: 14px; margin-bottom: 10px;">Select the issue type to email to the player:</p>
                <select name="issue_type" required>
                    <option value="" disabled selected>Select Issue...</option>
                    <option value="Short">Short / Partial Payment</option>
                    <option value="Invalid">Invalid / Unreadable Receipt</option>
                </select>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit-flag">Send Warning Email</button>
                    <button type="button" onclick="closeFlagModal()" style="background: #555; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 3px; width: 100%;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditEmailModal(id, email) {
            document.getElementById('edit_email_user_id').value = id;
            document.getElementById('edit_email_input').value = email;
            document.getElementById('editEmailModal').style.display = 'flex';
        }
        function closeEditEmailModal() {
            document.getElementById('editEmailModal').style.display = 'none';
        }
        function openLimitModal() { document.getElementById('limitModal').style.display = 'flex'; }
        function closeLimitModal() { document.getElementById('limitModal').style.display = 'none'; }
        function openFlagModal(id, email, name) {
            document.getElementById('flag_user_id').value = id;
            document.getElementById('flag_user_email').value = email;
            document.getElementById('flag_first_name').value = name;
            document.getElementById('flagModal').style.display = 'flex';
        }
        function closeFlagModal() {
            document.getElementById('flagModal').style.display = 'none';
        }
        function disableVerifyBtn(form) {
            // Find the submit button inside the specific form that was clicked
            const btn = form.querySelector('.btn-verify');
            
            // If it's already disabled, stop the form from submitting again
            if (btn.disabled) {
                return false; 
            }
            
            // Lock the button, change the color, and update the text
            btn.disabled = true;
            btn.innerHTML = '⏳ VERIFYING...';
            btn.style.backgroundColor = '#555'; // Grey out the button
            btn.style.cursor = 'not-allowed';
            btn.style.color = '#fff';
            
            // Allow the form to submit normally to verify_payment.php
            return true; 
        }
    </script>

</body>
</html>
<?php $conn->close(); ?>