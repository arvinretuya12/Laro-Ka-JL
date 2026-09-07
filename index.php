<?php
session_start();
// Make sure this points to your correct db_connect.php path
require 'php/db_connect.php'; 

// --- NEW: UNIQUE VISITOR TRACKING & LOCATION ---
if (!isset($_SESSION['has_visited'])) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $location = 'Unknown Location';
    
    // Fetch Location from IP (Skip if running on local test server)
    if ($ip !== '127.0.0.1' && $ip !== '::1' && $ip !== 'Unknown') {
        $ctx = stream_context_create(['http' => ['timeout' => 2]]); 
        $geo_data = @file_get_contents("http://ip-api.com/json/{$ip}", false, $ctx);
        if ($geo_data) {
            $geo = json_decode($geo_data, true);
            if (isset($geo['status']) && $geo['status'] === 'success') {
                $location = $geo['city'] . ', ' . $geo['country'];
            }
        }
    }

    // Log the visit into activity_logs
    $actor_type = 'Visitor';
    $actor_name = 'Anonymous';
    $action = "Visited Homepage - " . $location;
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (actor_type, actor_name, action_description, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $actor_type, $actor_name, $action, $ip);
        $stmt->execute();
    }
    
    // Set session so they are only counted once per visit
    $_SESSION['has_visited'] = true; 
}
// -----------------------------------------------

// Check capacity limit vs current registrants
$limit_check = $conn->query("SELECT setting_value FROM event_settings WHERE setting_key = 'max_registrants'");
$max_registrants = $limit_check->fetch_assoc()['setting_value'];

$count_check = $conn->query("SELECT COUNT(*) as total FROM registrants");
$current_total = $count_check->fetch_assoc()['total'];

// Create a simple boolean to use in our HTML
$is_full = ($current_total >= $max_registrants);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laro Ka JL - Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- LANDING PAGE (Matches the Poster Layout) -->
    <div id="landing-page">
        <div class="presents-text">JOLOVERSE PRESENTS</div>
        
        <img src="assets/images/LARO KA JL LOGO.png" alt="Laro Ka JL" class="main-logo">
        
        <!-- Floating Arcade Elements (Individual Images) -->
        <img src="assets/images/1.png" alt="Question Mark" class="floating-element element-1">
        <img src="assets/images/2.png" alt="Controller" class="floating-element element-2">
        <img src="assets/images/3.png" alt="Lightning" class="floating-element element-3">
        <img src="assets/images/4.png" alt="Ghost" class="floating-element element-4">
        <img src="assets/images/5.png" alt="Pacman" class="floating-element element-5">
        <img src="assets/images/6.png" alt="Heart" class="floating-element element-6">

        <!-- JL Hero Image -->
        <img src="assets/images/JL PICTURE.png" alt="JL Picture" class="jl-picture">
        
        <!-- Floating Rounded Banner -->
        <div class="bottom-banner">
            <div class="banner-text-left">
                <h2>25TH BIRTHDAY<br>CELEBRATION</h2>
            </div>
            
            <button class="start-btn" onclick="showForm()">START THE<br>GAME!</button>
            
            <!-- <div class="banner-text-right">
                <h2>TIME AM PM<br>LOCATION</h2>
            </div> -->
        </div>
    </div>

    <!-- REGISTRATION FORM -->
    <div id="reg-form">
        <h2>PLAYER REGISTRATION</h2>


        
        <?php if ($is_full): ?>

            <!-- THE "EVENT IS FULL" NOTICE -->
            <div style="text-align: center; padding: 50px 20px; background: #390055; color: #f0eadd; border-radius: 10px; margin: 50px auto; max-width: 500px; border: 2px solid #a0862d; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                <h2 style="font-family: 'DetailsFont', sans-serif; color: #a0862d; font-size: 32px; margin-bottom: 10px;">REGISTRATION CLOSED</h2>
                <p style="font-size: 18px; line-height: 1.5; margin: 0;">Registration is now full.<br>See you on the next Joloverse event!</p>
                <button type="button" class="submit-btn" style="background: transparent; border-color: #863fa9; color: #ffffff; margin-top: 10px;" onclick="hideForm()">GO BACK</button>
            </div>

        <?php else: ?>


        <div style="background: rgba(0, 0, 0, 0.6); border: 2px dashed #863fa9; padding: 15px; border-radius: 10px; margin-bottom: 25px;">
            <h3 style="color: #f0eadd; margin-top: 0; font-size: 18px; font-family: 'DetailsFont', sans-serif;">HOW TO PLAY (REGISTER):</h3>
            <ul style="text-align: left; color: #ccc; font-family: 'Arial', sans-serif; font-size: 14px; line-height: 1.5; padding-left: 20px; margin-bottom: 0;">
                <li style="margin-bottom: 8px;"><strong>1. Player Profile:</strong> Fill out your details below.</li>
                <li style="margin-bottom: 8px;"><strong>2. Insert Coin:</strong> Scan the GCash QR and upload your proof of payment.</li>
                <li style="margin-bottom: 8px;"><strong>3. Wait:</strong> Admin Verification takes up to 24-48 hours.</li>
                <li style="margin-bottom: 8px;"><strong>4. Recieve an Email:</strong> Receive your unique QR Code via email.</li>
                <li style="margin-bottom: 8px;"><strong>5. Claim:</strong> Present your QR Code for scanning to the registration booth to claim your arcade and food stall tickets. Enjoy!</li>
            </ul>
        </div>


        <form id="registrationForm" action="php/register.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>FIRST NAME</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>LAST NAME</label>
                <input type="text" name="last_name" required>
            </div>
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>M.I.</label>
                    <input type="text" name="middle_initial" maxlength="5">
                </div>
                <div class="form-group" style="flex: 3;">
                    <label>AGE</label>
                    <input type="number" name="age" required>
                </div>
            </div>
            <div class="form-group">
                <label>VALID EMAIL ADDRESS</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>FB OR X ACCOUNT LINK</label>
                <input type="url" name="social_link">
            </div>

            <hr style="border: 1px dashed #863fa9; margin: 30px 0;">
            
            <h2 style="font-size: 24px;">INSERT COIN (PAYMENT)</h2>
            <p style="text-align: center; color: #f0eadd; font-family: 'Arial'; font-size: 14px;">Scan the GCash QR code below. Registration Fee: <b>PHP 1000.00</b></p>
            
            <div style="text-align: center;">
                <img src="assets/images/gcash-joloverse.jpg" alt="GCash QR" style="width: 200px; border: 4px solid #863fa9; margin-bottom: 15px;">
            </div>
            
            <div class="form-group">
                <label>UPLOAD PROOF OF PAYMENT</label>
                
                <!-- Hidden actual file input -->
                <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required style="display: none;" onchange="updateFileName(this)">
                
                <!-- Giant clickable UI button -->
                <label for="payment_proof" class="custom-file-upload">
                    <span id="file-name">📸 TAP HERE TO UPLOAD RECEIPT</span>
                </label>
            </div>

            <div class="form-group">
                <label>TERMS AND CONDITIONS</label>
                <div class="terms-box">
                    <strong>1. Ticket Usage:</strong> Tickets issued are valid only for the date of the event.<br><br>
                    <strong>2. Verification:</strong> Payment verification may take up to 24-48 hours. Once verified, a unique QR ticket will be sent to your email.<br><br>
                    <strong>3. Refunds:</strong> Registration is non-refundable unless the event is officially canceled by the organizers.<br><br>
                    <strong>4. Transfer:</strong> Registration is non-transferable.<br><br>
                    <strong>5. Entry:</strong> Present your QR code at the door. No QR, no entry.
                </div>
                <input type="checkbox" id="terms" required style="width: auto; margin-right: 10px;">
                <label for="terms" style="display:inline; color:#a0862d;">I AGREE TO THE RULES OF THE GAME</label>
            </div>

            <button type="submit" class="submit-btn">SUBMIT PLAYER PROFILE</button>
            <button type="button" class="submit-btn" style="background: transparent; border-color: #863fa9; color: #863fa9; margin-top: 10px;" onclick="hideForm()">GO BACK</button>
        </form>

        <?php endif; ?>
    </div>


        <!-- POP-UP MODAL -->
    <div class="modal-overlay" id="status-modal">
        <div class="modal-box">
            <h2 id="modal-title">TITLE</h2>
            <p id="modal-message">Message goes here.</p>
            <button class="start-btn" onclick="closeModal()" style="width: auto; padding: 10px 30px; font-size: 16px;">OKAY</button>
        </div>
    </div>

    
<!-- JavaScript to handle screen switching, Fetch API, and Pop-ups -->
    <script>
        function showForm() {
            document.getElementById('landing-page').style.display = 'none';
            document.getElementById('reg-form').style.display = 'block';
            window.scrollTo(0, 0);
        }

        function hideForm() {
            document.getElementById('reg-form').style.display = 'none';
            document.getElementById('landing-page').style.display = 'block';
        }

        // --- BACKGROUND FORM SUBMISSION (AJAX) ---
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault(); 

            const formData = new FormData(this);
            const submitBtn = document.querySelector('.submit-btn');
            
            // Visual feedback while loading
            submitBtn.innerText = "PROCESSING...";
            submitBtn.disabled = true;
            submitBtn.style.opacity = "0.7";

            fetch('php/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                submitBtn.innerText = "SUBMIT PLAYER PROFILE";
                submitBtn.disabled = false;
                submitBtn.style.opacity = "1";

                const modal = document.getElementById('status-modal');
                const title = document.getElementById('modal-title');
                const message = document.getElementById('modal-message');

                if (data.status === 'success') {
                    title.innerText = "REGISTRATION SUCCESS!";
                    title.style.color = "#a0862d"; // Gold
                    message.innerText = "Your payment is currently being verified. We will email your QR code once approved.";
                    modal.dataset.action = "home"; 
                    
                    document.getElementById('registrationForm').reset(); // Clear whole form
                } 
                else if (data.status === 'duplicate') {
                    title.innerText = "ALREADY REGISTERED";
                    title.style.color = "#ff3333"; // Red
                    message.innerText = "You have already registered. Wait for our confirmation email.";
                    modal.dataset.action = "stay"; 
                    
                    // Clear the Name fields since they triggered the duplicate
                    document.querySelector('input[name="first_name"]').value = ''; 
                    document.querySelector('input[name="last_name"]').value = ''; 
                } 
                else if (data.status === 'email_taken') {
                    title.innerText = "EMAIL IN USE";
                    title.style.color = "#ff3333"; // Red
                    message.innerText = "This email address is already used, kindly use a different email.";
                    modal.dataset.action = "stay"; 
                    
                    // Clear ONLY the email field
                    document.querySelector('input[name="email"]').value = ''; 
                } 
                else if (data.status === 'full'){
                    title.innerText = "REGISTRATION IS FULL";
                    title.style.color = "#ff3333";
                    message.innerText = "Better Luck Next Time";
                    modal.dataset.action = "stay";
                }
                else {
                    title.innerText = "SYSTEM ERROR";
                    title.style.color = "#ff3333";
                    message.innerText = "Something went wrong. Please try again.";
                    modal.dataset.action = "stay";
                }

                modal.style.display = 'flex'; // Show the pop-up
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerText = "SUBMIT PLAYER PROFILE";
                submitBtn.disabled = false;
                submitBtn.style.opacity = "1";
            });
        });

        // Function to close the modal
        function closeModal() {
            const modal = document.getElementById('status-modal');
            modal.style.display = 'none';
            
            // If the registration was successful, return to the landing page
            if (modal.dataset.action === "home") {
                hideForm(); 
                window.scrollTo(0, 0);
            }
        }

        // Function to show the selected image file name
        function updateFileName(input) {
            const fileNameSpan = document.getElementById('file-name');
            if (input.files && input.files.length > 0) {
                // Change text to the file name and make it green
                fileNameSpan.innerHTML = '✅ ' + input.files[0].name;
                fileNameSpan.style.color = '#2ecc71'; 
            } else {
                // Reset if they cancel
                fileNameSpan.innerHTML = '📸 TAP HERE TO UPLOAD RECEIPT';
                fileNameSpan.style.color = '#f0eadd';
            }
        }
    </script>


</body>
</html>