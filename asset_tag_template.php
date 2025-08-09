<?php
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Direct access is not allowed.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Asset Tag</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page { margin: 25pt; }

        body {
            font-family: Montserrat, sans-serif;
            line-height: 1.3;
            font-size: 9pt;
        }

        .asset-tag {
            padding: 5pt;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5pt;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 60%;
        }

        .logo-img {
            max-height: 25pt;
            max-width: 100%;
            display: block;
        }

        .title-cell {
            width: 40%;
            text-align: right;
            font-size: 15pt;
            font-weight: bold;
            padding-right: 2pt;
        }

        /* --- Table for QR and Details --- */
        .main-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5pt;
        }

        .qr-cell-outer {
            width: 60px;
            vertical-align: middle;
            padding-right: 5pt;
        }

        .details-cell-outer {
            vertical-align: middle;
        }

        .details-table {
            width: 100%;
            border: 0.5pt solid #333;
            border-collapse: collapse;
        }

        .details-table td {
            border: 0.5pt solid #333;
            padding: 2pt 4pt;
            vertical-align: middle;
            font-size: 8pt;
        }

        .details-table td:first-child {
            width: 35%;
            white-space: nowrap;
        }

        .details-table td:last-child {
            width: 65%;
        }

        .detail-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="asset-tag">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo" class="logo-img">
                </td>
                <td class="title-cell">
                    Asset Tag
                </td>
            </tr>
        </table>
        <div class="asset-tag-body">
            <table class="main-info-table">
                <tr>
                    <td class="qr-cell-outer">
                        <img src="data:image/svg+xml;base64,<?php echo base64_encode($qrCodeDataUri); ?>" alt="QR Code" style="width: 80px";>
                    </td>

                    <td class="details-cell-outer">
                        <table class="details-table"> <!-- NESTED TABLE for details -->
                            <tr>
                                <td><span class="detail-label">Asset Code</span></td>
                                <td><span class="detail-value"><?php echo htmlspecialchars($asset_tag); ?></span></td>
                            </tr>
                            <tr>
                                <td><span class="detail-label">Asset Name</span></td>
                                <td><span class="detail-value"><?php echo htmlspecialchars($asset_brand); ?></span></td>
                            </tr>
                            <tr>
                                <td><span class="detail-label">Serial No.</span></td>
                                <td><span class="detail-value"><?php echo htmlspecialchars($asset_serial_num); ?></span></td>
                            </tr>
                            <tr>
                                <td><span class="detail-label">Acquisition Date</span></td>
                                <td><span class="detail-value"><?php echo htmlspecialchars($date_purchased); ?></span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>