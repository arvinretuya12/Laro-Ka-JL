<?php
require 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css">
    
    <!-- Load the html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        .scanner-container {
            background: #390055;
            padding: 30px;
            border-radius: 10px;
            border: 2px solid #863fa9;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Viewfinder overrides */
        #reader { width: 100%; border-radius: 10px; border: 4px solid #a0862d !important; overflow: hidden; background: #000; }
        #reader button { background: #863fa9; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 5px; margin-top: 10px; font-family: Arial, sans-serif; }
        #reader select { padding: 10px; margin-bottom: 10px; background: #222; color: white; border: 1px solid #863fa9; border-radius: 5px; }

        /* Status Banner */
        #scan-result {
            display: none;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border: 4px solid;
            animation: popIn 0.3s ease-out forwards;
        }

        .result-success { background: rgba(46, 204, 113, 0.2); border-color: #2ecc71 !important; }
        .result-success h2 { color: #2ecc71; font-family: 'DetailsFont', sans-serif; margin: 0; font-size: 28px;}
        
        .result-error { background: rgba(231, 76, 60, 0.2); border-color: #e74c3c !important; }
        .result-error h2 { color: #e74c3c; font-family: 'DetailsFont', sans-serif; margin: 0; font-size: 28px;}

        @keyframes popIn {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <div style="color: #f0eadd; font-size: 12px; margin-bottom: 20px; text-align: center;">
            Logged in as: <strong style="color: #a0862d;"><?php echo htmlspecialchars($admin_username); ?></strong>
        </div>
        <a href="index.php">Dashboard</a>
        <a href="qr_scanner.php" class="active">QR Scanner</a>
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
        <h1 style="font-family: 'DetailsFont', sans-serif; color: #f0eadd; text-align: center;">ENTRANCE SCANNER</h1>
        
        <div class="scanner-container">
            <p style="color: #ccc; margin-top: 0; margin-bottom: 20px;">Point camera at player's QR ticket to verify entry.</p>
            
            <!-- Camera Viewfinder -->
            <div id="reader"></div>

            <!-- Dynamic Result Box -->
            <div id="scan-result">
                <h2 id="result-title"></h2>
                <p id="result-details" style="color: #fff; font-size: 16px; font-weight: bold; margin-bottom: 0;"></p>
            </div>
            
            <button id="resume-btn" style="display:none; width: 100%; background: #a0862d; color: #fff; padding: 15px; border: none; font-family: 'DetailsFont', sans-serif; font-size: 18px; cursor: pointer; margin-top: 20px; border-radius: 5px;" onclick="resumeScanner()">SCAN NEXT PLAYER</button>
        </div>
    </div>

    <script>
        let html5QrcodeScanner;
        let isScanning = true;

        // Function called when a QR code is successfully read
        function onScanSuccess(decodedText, decodedResult) {
            // Prevent multiple rapid-fire scans of the same code
            if (!isScanning) return;
            isScanning = false; 

            // Pause the camera so the admin can read the result
            html5QrcodeScanner.pause();

            // Send the scanned token to our API
            fetch('api_scan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ qr_token: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                const resultBox = document.getElementById('scan-result');
                const resultTitle = document.getElementById('result-title');
                const resultDetails = document.getElementById('result-details');
                const resumeBtn = document.getElementById('resume-btn');

                // Apply correct styling based on API response
                if (data.status === 'success') {
                    resultBox.className = 'result-success';
                } else {
                    resultBox.className = 'result-error';
                }

                resultTitle.innerText = data.message;
                resultDetails.innerHTML = data.details;
                
                resultBox.style.display = 'block';
                resumeBtn.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Network error. Could not reach the database.");
                resumeScanner();
            });
        }

        function onScanFailure(error) {
            // Background scanning errors can be ignored
        }

        // Initialize the scanner
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Reset UI to scan the next person
        function resumeScanner() {
            document.getElementById('scan-result').style.display = 'none';
            document.getElementById('resume-btn').style.display = 'none';
            html5QrcodeScanner.resume();
            isScanning = true;
        }
    </script>
</body>
</html>