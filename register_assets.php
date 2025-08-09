<?php

require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Dompdf\Dompdf;
use Dompdf\Options;

header('Content-Type: application/json'); // Ensure JSON output

// Function to generate QR code using BaconQrCode
function generateQRCode($text) {
    try {
        $renderer = new ImageRenderer(
            new RendererStyle(200, 1, null, null),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        // Generate QR code as SVG RAW string
        $qrCodeRawSvg = $writer->writeString($text);

        return $qrCodeRawSvg; // Return the raw SVG

    } catch (Exception $e) {
        error_log("QR Code Generation Error: " . $e->getMessage());
        return null;
    }
}
function generatePDF(
    $asset_tag,
    $asset_brand,
    $asset_model,
    $asset_serial_num,
    $date_purchased,
    $owner_name,
    $department_name,
    $logo_path
) {
    try {
        $options = new Options();
        $options->set('defaultFont', 'Montserrat');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $customPaper = array(0, 0, 300, 170);
        $dompdf->setPaper($customPaper);
        $qrCode_base64_uri = null;
        $qrCodeData =
            "Tag - " . $asset_tag . "\n" .
            "Brand: " . $asset_brand . "\n" .
            "Model: " . $asset_model . "\n" .
            "Serial Number: " . $asset_serial_num . "\n" .
            "Acquisition Date: " . $date_purchased . "\n" .
            "Custodian: " . $owner_name . "\n" .
            "Department: " . $department_name;
        $qrCodeRawSvg = generateQRCode($qrCodeData);
        if ($qrCodeRawSvg !== null) {
            $base64Svg = base64_encode($qrCodeRawSvg);
            if ($base64Svg !== false) {
                $qrCode_base64_uri = 'data:image/svg+xml;base64,' . $base64Svg;
            } else { error_log("PDF QR Error: Failed to base64 encode QR code SVG."); }
        } else { error_log("PDF QR Error: QR Code generation returned null."); }
        $html = '
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
                line-height:1.3;
                font-size: 9pt;
            }
            .asset-tag
                { padding: 5pt; }
            .header-table
                { width: 100%; border-collapse: collapse; margin-bottom: 5pt; }
            .header-table td
                { vertical-align: middle; padding: 0; }
            .logo-cell
                { width: 60%; }
            .logo-img
                { max-height: 25pt; max-width: 100%; display: block; }
            .title-cell
                { width: 40%; text-align: right; font-size: 15pt; font-weight: bold; padding-right: 2pt; }
            .main-info-table
                { width: 100%; border-collapse: collapse; margin-top: 5pt; }
            .qr-cell-outer
                { width: 60px; vertical-align: middle; padding-right: 5pt; }
            .details-cell-outer
                { vertical-align: middle; }
            .details-table
                { width: 100%; border: 0.5pt solid #333; border-collapse: collapse; }
            .details-table td
                { border: 0.5pt solid #333; padding: 2pt 4pt; vertical-align: middle; font-size: 8pt; }
            .details-table td:first-child
                { width: 35%; white-space: nowrap; }
            .details-table td:last-child
                { width: 65%; }
            .detail-label
                { font-weight: bold; }
        </style>
        </head>
        <body>
            <div class="asset-tag">
                <!-- Header Table -->
                <table class="header-table">
                    <tr>
                        <td class="logo-cell">
                            <img src="' . htmlspecialchars($logo_path) . '" alt="Logo" class="logo-img">
                        </td>
                        <td class="title-cell">
                           Asset Tag
                        </td>
                    </tr>
                </table>
                <div class="asset-tag-body"> <!-- Keep this div if needed for structure -->
                    <table class="main-info-table">
                        <tr>
                            <td class="qr-cell-outer">';
         if (!empty($qrCode_base64_uri)) {
              $html .= '<img src="' . $qrCode_base64_uri . '" alt="QR Code"  style="width: 80px">';
         } else {
             $html .= '<div style="width:80px; height:80px; border:1px solid #ccc; font-size:6pt; text-align:center;">[QR]</div>'; // Placeholder
         }
         $html .= '      </td>
                            <td class="details-cell-outer">
                                <table class="details-table">
                                    <tr>
                                        <td><span class="detail-label">Asset Code</span></td>
                                        <td><span class="detail-value">' . htmlspecialchars($asset_tag) . '</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="detail-label">Asset Name</span></td>
                                        <td><span class="detail-value">' . htmlspecialchars($asset_brand) . '</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="detail-label">Serial No.</span></td>
                                        <td><span class="detail-value">' . htmlspecialchars($asset_serial_num) . '</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="detail-label">Acquisition Date</span></td>
                                        <td><span class="detail-value">' . htmlspecialchars($date_purchased) . '</span></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div> <!-- End asset-tag-body -->
            </div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->render();
        $pdfData = $dompdf->output(); // Get raw PDF data

        // Base64 encode the PDF data before returning
        $pdfBase64 = base64_encode($pdfData);

        return $pdfBase64; // Return Base64 as expected by the calling code

    } catch (Exception $e) {
        error_log("PDF Generation Error in register_assets: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        return null; // Return null on failure
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Asset Details
    $asset_brand = sanitize_input($_POST["asset_name"]);
    $asset_model = sanitize_input($_POST["asset_model"]);
    $asset_serial_num = sanitize_input($_POST["serial_num"]);
    $type_id = sanitize_input($_POST["asset_type"]);
    $status_id = sanitize_input($_POST["asset_status"]);
    $purchase_cost = sanitize_input($_POST["purchase_cost"]);
    $asset_depreciated_cost = sanitize_input($_POST["deprec_cost"]);
    $date_purchased = sanitize_input($_POST["date_purchased"]);
    $date_registered = sanitize_input($_POST["date_registered"]);
    $deprec_period = sanitize_input($_POST["deprec_period"]);
    $site_id = sanitize_input($_POST["site_id"]);
    $owner_name = sanitize_input($_POST["assigned_to"]);
    $department_id = sanitize_input($_POST["departmentID"]);
    $owner_position = sanitize_input($_POST["position"]);
    $owner_date_hired = sanitize_input($_POST["date_hired"]);
    $owner_phone_num = sanitize_input($_POST["phone_num"]);

    $newImagePath = null;
    $default_image_path = "/SLI_ASSET/assets/default.jpg";

    $image_path = $default_image_path;

    if (isset($_FILES["imageUpload"]) && $_FILES["imageUpload"]["error"] == 0) {
        include "upload_img.php";
        if (isset($newImagePath)) {
            $image_path = $newImagePath;
        }

    }

    // Database Connection
    $conn = db_connect();

    $sqlOwner = "INSERT INTO owners (owner_name, department_id, owner_position, owner_date_hired, owner_phone_num) VALUES (?, ?, ?, ?, ?)";
    $stmtOwner = $conn->prepare($sqlOwner);

    if ($stmtOwner === false) {
        error_log("Error preparing owner statement: " . $conn->error);
        // Return an error response
        $response = array("status" => "error", "message" => "Error preparing owner statement.");
        echo json_encode($response);
        exit;
    }

    $stmtOwner->bind_param("sisss", $owner_name, $department_id, $owner_position, $owner_date_hired, $owner_phone_num);

    if (!$stmtOwner->execute()) {
        error_log("Error executing owner statement: " . $stmtOwner->error);
        // Return an error response
        $response = array("status" => "error", "message" => "Error executing owner statement.");
        echo json_encode($response);
        exit;
    }

    $owner_id = $conn->insert_id;

    $sqlTypeCode = "SELECT type_code FROM asset_type WHERE type_id = ?";
    $stmtTypeCode = $conn->prepare($sqlTypeCode);

    if ($stmtTypeCode === false) {
        error_log("Error preparing type code statement: " . $conn->error);
          // Return an error response
        $response = array("status" => "error", "message" => "Error preparing type code statement.");
        echo json_encode($response);
        exit;
    }

    $stmtTypeCode->bind_param("i", $type_id);

    if (!$stmtTypeCode->execute()) {
        error_log("Error executing type code statement: " . $stmtTypeCode->error);
          // Return an error response
        $response = array("status" => "error", "message" => "Error executing type code statement.");
        echo json_encode($response);
        exit;
    }

    $stmtTypeCode->bind_result($type_code);
    $stmtTypeCode->fetch();
    $stmtTypeCode->close();

    $sqlGetLastTag = "SELECT asset_tag FROM assets WHERE type_id = ? ORDER BY asset_tag DESC LIMIT 1";
    $stmtGetLastTag = $conn->prepare($sqlGetLastTag);

    if ($stmtGetLastTag === false) {
        error_log("Error preparing last tag statement: " . $conn->error);
          // Return an error response
        $response = array("status" => "error", "message" => "Error preparing last tag statement.");
        echo json_encode($response);
        exit;
    }

    $stmtGetLastTag->bind_param("i", $type_id);

    if (!$stmtGetLastTag->execute()) {
        error_log("Error executing last tag statement: " . $stmtGetLastTag->error);
          // Return an error response
        $response = array("status" => "error", "message" => "Error executing last tag statement.");
        echo json_encode($response);
        exit;
    }

    $stmtGetLastTag->bind_result($last_tag);
    $stmtGetLastTag->fetch();
    $stmtGetLastTag->close();

    $next_number = 1;
    if ($last_tag) {
        $last_number = (int)substr($last_tag, strrpos($last_tag, '-') + 1);
        $next_number = $last_number + 1;
    }

    $asset_tag = "SLI-" . $type_code . "-" . sprintf("%04d", $next_number);

    $sql = "INSERT INTO assets (
        asset_brand,
        asset_model,
        asset_serial_num,
        type_id,
        status_id,
        asset_purchase_cost,
        asset_depreciated_cost,
        asset_purchase_date,
        asset_register_date,
        asset_depreciation_period,
        site_id,
        owner_id,
        image_path,
        asset_tag)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; //Add one ? for asset_depreciated_cost
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing asset statement: " . $conn->error);
        // Return an error response
        $response = array("status" => "error", "message" => "Error preparing asset statement.");
        echo json_encode($response);
        exit;
    }

    if (empty($date_purchased)) {
        $date_purchased = "";
    }

    $stmt->bind_param('sssiiidsssiiss',
        $asset_brand,
        $asset_model,
        $asset_serial_num,
        $type_id,
        $status_id,
        $purchase_cost,
        $asset_depreciated_cost,
        $date_purchased,
        $date_registered,
        $deprec_period,
        $site_id,
        $owner_id,
        $image_path,
        $asset_tag);

    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Error executing asset statement: " . $stmt->error);
        // Return an error response
        $response = array("status" => "error", "message" => "Error executing asset statement.");
        echo json_encode($response);
        exit;
    }

    $asset_id = $conn->insert_id; // Get the auto-generated asset_id

    //--- FETCH DEPARTMENT NAME ---
    $sqlDepartment = "SELECT department_name FROM departments WHERE department_id = ?";
    $stmtDepartment = $conn->prepare($sqlDepartment);

    if ($stmtDepartment === false) {
        error_log("Error preparing department statement: " . $conn->error);
        $response = array("status" => "error", "message" => "Error preparing department statement.");
        echo json_encode($response);
        exit;
    }

    $stmtDepartment->bind_param("i", $department_id);

    if (!$stmtDepartment->execute()) {
        error_log("Error executing department statement: " . $stmtDepartment->error);
        $response = array("status" => "error", "message" => "Error executing department statement.");
        echo json_encode($response);
        exit;
    }

    $stmtDepartment->bind_result($department_name);
    $stmtDepartment->fetch();
    $stmtDepartment->close();

    if (!$department_name) {
        $department_name = "N/A";  // Or handle the case where the department is not found.
        error_log("Department name not found for ID: " . $department_id);
    }
    //--- END FETCH DEPARTMENT NAME ---

    //Close statements
    $stmtOwner->close();
    $stmt->close();

    // Close connection
    $conn->close();

    // Logo URL Calculation
    $logo_web_path = '/SLI_ASSET/icons/safexpress_logo.png'; // Adjust if needed
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $logo_full_url = $protocol . $host . $logo_web_path;

    // Call the function to generate the PDF, passing the data
    $pdfBase64 = generatePDF(
        $asset_tag,
        $asset_brand,
        $asset_model,
        $asset_serial_num,
        $date_purchased,
        $owner_name,
        $department_name,
        $logo_full_url
    );

    if ($pdfBase64) {
        // Return success response with the Base64 encoded PDF
        $response = array("status" => "success", "pdfData" => $pdfBase64, "asset_tag" => $asset_tag);
        echo json_encode($response);
        exit();
    } else {
        // Return error response if PDF generation failed
        $response = array("status" => "error", "message" => "Failed to generate PDF.");
        echo json_encode($response);
        exit();
    }
}
?>