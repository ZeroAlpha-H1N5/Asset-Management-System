<?php
require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Dompdf\Dompdf; // Keep Dompdf for PDF generation
use Dompdf\Options;

if (!isset($_GET['asset_id']) || !is_numeric($_GET['asset_id'])) {
    http_response_code(400); // Bad Request
    echo "Invalid asset ID.";
    exit;
}

$asset_id = (int)$_GET['asset_id'];

$conn = db_connect();
$sql = "SELECT
                a.asset_brand AS assetName,
                a.asset_model AS assetModel,
                a.asset_serial_num AS serialNum,
                a.asset_tag AS assetTag,
                o.owner_name AS ownerName,
                a.asset_purchase_date AS datePurchased,
                d.department_name AS departmentName
            FROM
                assets a
            JOIN
                owners o ON a.owner_id = o.owner_id
            JOIN
                departments d ON o.department_id = d.department_id
            WHERE a.asset_id = ?"; //Simplified query

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo "Could not prepare statement.";
    exit;
}

$stmt->bind_param("i", $asset_id);

if (!$stmt->execute()) {
    http_response_code(500); // Internal Server Error
    echo "Could not execute statement.";
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(404); // Not Found
    echo "Asset not found.";
    exit;
}

$asset = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Logo
$logo_web_path = '/SLI_ASSET/icons/safexpress_logo.png';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST']; // e.g., 'localhost' or your domain
$logo_full_url = $protocol . $host . $logo_web_path;

// ----- Generate the QR code data URI
$qrCodeData =
    "Tag - " . $asset['assetTag'] . "\n" .
    "Brand: " . $asset['assetName'] . "\n" .
    "Model: " . $asset['assetModel'] . "\n" .
    "Serial Number: " . $asset['serialNum'] . "\n" .
    "Acquisition Date: " . $asset['datePurchased'] . "\n" .
    "Custodian: " . $asset['ownerName'] . "\n" .
    "Department: " . $asset['departmentName'];

$qrCodeDataUri = generateQRCode($qrCodeData);

// ----- Assign variables that the template expects
$asset_tag = $asset['assetTag'];
$asset_brand = $asset['assetName'];
$asset_model = $asset['assetModel'];
$asset_serial_num = $asset['serialNum'];
$date_purchased = $asset['datePurchased'];
$owner_name = $asset['ownerName'];
$department = $asset['departmentName'];
$logo_path = $logo_full_url;

// ----- Check if we should serve the HTML (for the preview) or the PDF
if (isset($_GET['action']) && $_GET['action'] === 'preview') {
    // ----- Serve HTML for the preview in the iframe
    ob_start();
    include 'asset_tag_template.php'; // Include the HTML template
    $html = ob_get_clean();  // Store the captured HTML
    echo $html;
    exit;
} else {
    // ----- Serve the PDF for download
    $options = new Options();
    $options->set('defaultFont', 'Montserrat');
    $options->set('isRemoteEnabled', true); // allow loading external files.
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $customPaper = array(0, 0, 300, 170);
    $dompdf->setPaper($customPaper);

    ob_start();  // Start output buffering
    include 'asset_tag_template.php'; // Include the HTML template
    $html = ob_get_clean(); // Store the captured HTML

    $dompdf->loadHtml($html);
    $dompdf->render(); // Render the HTML as PDF
    $pdfData = $dompdf->output(); // Get the PDF data

    if ($pdfData) {
        header('Content-Type: application/pdf'); // IMPORTANT: Set the correct content type
        header('Content-Disposition: inline; filename="SLI-ASSET-' . $asset['assetTag'] . '.pdf"'); // Suggest a filename
        echo $pdfData;  // Output the raw PDF data
    } else {
        http_response_code(500);  // Internal Server Error
        echo "PDF generation failed.";
    }
    exit;
}

function generateQRCode($text) {
    try {
        $renderer = new ImageRenderer(
            new RendererStyle(200, 1, null, null),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        // Generate QR code as SVG data URI
        $qrCodeDataUri = $writer->writeString($text);

        return $qrCodeDataUri;

    } catch (Exception $e) {
        error_log("QR Code Generation Error: " . $e->getMessage());
        return null;
    }
}
?>