<?php
require 'auth.php';
require '../php/db_connect.php';

// 1. Get the total number of VERIFIED players
$verified_query = $conn->query("SELECT COUNT(*) as count FROM registrants WHERE payment_status = 'Verified'");
$verified_count = $verified_query->fetch_assoc()['count'];

// 2. Fetch all defined event modules
$modules_query = $conn->query("SELECT * FROM event_modules ORDER BY module_type, id ASC");
$modules = [];
if ($modules_query->num_rows > 0) {
    while($row = $modules_query->fetch_assoc()) {
        $modules[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Tickets - Laro Ka JL</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        /* --- SCREEN STYLES --- */
        .summary-box { background: #390055; padding: 25px; border-radius: 10px; border: 2px solid #863fa9; margin-bottom: 30px; }
        .summary-box h3 { color: #a0862d; margin-top: 0; font-family: 'DetailsFont', sans-serif; }
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-table th, .summary-table td { padding: 10px; border-bottom: 1px solid #863fa9; text-align: left; }
        
        /* Print Controls */
        .print-controls { background: #1a0026; padding: 15px; border-radius: 5px; display: flex; gap: 15px; align-items: center; margin-top: 20px; border: 1px solid #863fa9; }
        .print-controls select { padding: 10px; background: #222; color: #fff; border: 1px solid #a0862d; border-radius: 3px; font-size: 16px; outline: none; }
        .btn-print { background: #2ecc71; color: #fff; padding: 10px 20px; border: none; font-family: 'DetailsFont', sans-serif; font-size: 16px; cursor: pointer; border-radius: 3px; flex: 1; }
        .btn-print:hover { background: #27ae60; }
        .btn-pdf { background: #e74c3c; color: #fff; padding: 10px 20px; border: none; font-family: 'DetailsFont', sans-serif; font-size: 16px; cursor: pointer; border-radius: 3px; flex: 1; }
        .btn-pdf:hover { background: #c0392b; }

        /* --- TICKET STYLES (Available to html2pdf generator) --- */
        #printable-tickets { 
            display: none; /* Hidden on standard screen */
            flex-wrap: wrap; 
            gap: 15px; 
            justify-content: center;
            padding: 20px;
            background: #fff; /* Force white background for PDF */
        }

        .ticket {
            width: 2.5in; 
            height: 1.2in;
            border: 2px dashed #000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            page-break-inside: avoid;
            box-sizing: border-box;
            padding: 10px;
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
        }
        .ticket-type { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .ticket-name { font-size: 16px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; }
        .ticket-event { font-size: 10px; font-weight: bold; }

        /* --- PRINT STYLES (For Direct Print / Ctrl+P) --- */
        @media print {
            .sidebar, .main-content > *:not(#printable-tickets) { display: none !important; }
            body, html { background: white; margin: 0; padding: 0; }
            .main-content { margin: 0; padding: 0; width: 100%; }
            #printable-tickets { display: flex !important; }
        }
    </style>
    
    <style id="dynamic-page-size">
        @page { size: A4; margin: 0.5in; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>ADMIN PANEL</h2>
        <div style="color: #f0eadd; font-size: 12px; margin-bottom: 20px; text-align: center;">
            Logged in as: <strong style="color: #a0862d;"><?php echo htmlspecialchars($admin_username); ?></strong>
        </div>
        <a href="index.php">Dashboard</a>
        <a href="qr_scanner.php">QR Scanner</a>
        <a href="print_tickets.php" class="active">Print Tickets</a>
        <a href="event_modules.php">Event Modules</a>
        
        <?php if ($admin_role === 'Super Admin'): ?>
            <hr style="border-color: #390055; margin: 20px 0;">
            <a href="manage_admins.php" style="border-color: #ff3333; color: #ff3333;">Manage Admins</a>
            <a href="activity_logs.php">Activity Logs</a>
            <?php endif; ?>
        <a href="logout.php" style="margin-top: 50px; background: #222;">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="font-family: 'DetailsFont', sans-serif; color: #f0eadd;">PRINT TICKETS</h1>
        
        <div class="summary-box">
            <h3>PRODUCTION SUMMARY</h3>
            <p>Verified Players Registered: <strong style="color: #2ecc71; font-size: 20px;"><?php echo $verified_count; ?></strong></p>
            
            <?php if ($verified_count > 0 && !empty($modules)): ?>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Booth / Module</th>
                            <th>Category</th>
                            <th>Qty per Player</th>
                            <th>TOTAL TO PRINT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach ($modules as $mod): 
                            $total_for_booth = $verified_count * $mod['default_tickets'];
                            $grand_total += $total_for_booth;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mod['module_name']); ?></td>
                                <td><?php echo $mod['module_type']; ?></td>
                                <td><?php echo $mod['default_tickets']; ?></td>
                                <td style="font-weight: bold; color: #a0862d;"><?php echo $total_for_booth; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="text-align: right; margin-bottom: 0;">Grand Total Tickets: <strong><?php echo $grand_total; ?></strong></p>
                
                <div class="print-controls">
                    <label style="color: #fff; font-weight: bold;">Paper Size:</label>
                    <select id="paper_size" onchange="updatePageSize()">
                        <option value="a4">A4 (8.27 x 11.69 in)</option>
                        <option value="letter">Letter (8.5 x 11 in)</option>
                        <option value="folio">Folio/Long (8.5 x 13 in)</option>
                    </select>
                    
                    <!-- <button class="btn-print" onclick="window.print()">DIRECT PRINT</button> -->
                    <button class="btn-print" onclick="handleDirectPrint()">DIRECT PRINT</button>
                    <button class="btn-pdf" onclick="downloadPDF()">SAVE AS PDF</button>
                </div>
            <?php else: ?>
                <p style="color: #e74c3c;">You must have at least 1 verified player and 1 event module created before printing tickets.</p>
            <?php endif; ?>
        </div>

        <!-- HIDDEN PRINTABLE AREA -->
        <div id="printable-tickets">
            <?php 
            foreach ($modules as $mod): 
                $total_tickets_to_generate = $verified_count * $mod['default_tickets'];
                for ($i = 1; $i <= $total_tickets_to_generate; $i++): 
            ?>
                <div class="ticket">
                    <div class="ticket-type"><?php echo htmlspecialchars($mod['module_type']); ?> TICKET</div>
                    <div class="ticket-name"><?php echo htmlspecialchars($mod['module_name']); ?></div>
                    <div class="ticket-event">Laro Ka JL: 25th Bday</div>
                </div>
            <?php 
                endfor; 
            endforeach; 
            ?>
        </div>
        
    </div>

    <script>

        function logPrintAction(actionText) {
            fetch('api_log_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action_description: actionText })
            }).catch(err => console.error("Logging failed:", err));
        }


        function updatePageSize() {
            const size = document.getElementById('paper_size').value;
            const styleTag = document.getElementById('dynamic-page-size');
            
            if (size === 'a4') {
                styleTag.innerHTML = '@page { size: A4; margin: 0.5in; }';
            } else if (size === 'letter') {
                styleTag.innerHTML = '@page { size: 8.5in 11in; margin: 0.5in; }';
            } else if (size === 'folio') {
                styleTag.innerHTML = '@page { size: 8.5in 13in; margin: 0.5in; }';
            }
        }

        function handleDirectPrint() {
            logPrintAction("Initiated Direct Print for physical tickets.");
            window.print();
        }

        function downloadPDF() {
            const element = document.getElementById('printable-tickets');
            const sizeSelection = document.getElementById('paper_size').value;
            
            let pdfFormat = 'a4';
            if (sizeSelection === 'letter') pdfFormat = 'letter';
            if (sizeSelection === 'folio') pdfFormat = [8.5, 13]; // 8.5 x 13 in inches
            
            // Show the container so html2pdf can "see" the styling
            element.style.display = 'flex';

            const opt = {
                margin:       0.5,
                filename:     'Joloverse_Tickets.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true }, 
                jsPDF:        { unit: 'in', format: pdfFormat, orientation: 'portrait' }
            };

            logPrintAction(`Exported physical tickets as PDF (${sizeSelection.toUpperCase()}).`);

            html2pdf().set(opt).from(element).save().then(() => {
                // Hide it again after downloading
                element.style.display = 'none';
            });
        }
    </script>

</body>
</html>
<?php $conn->close(); ?>