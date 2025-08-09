<?php
require_once 'functions.php';

// Validate assetId
$assetId = isset($_POST['assetId']) ? intval($_POST['assetId']) : 0;
if ($assetId <= 0) {
    error_log("Invalid assetId provided.");
    http_response_code(400);
    echo "Invalid asset ID.";
    exit;
}

// Define the path to the default image
$defaultImagePath = "/SLI_ASSET/assets/default.jpg";

$conn = db_connect();

// Use image_path to UPDATE the ASSET
$sqlUpdateAsset = "UPDATE assets SET image_path = ? WHERE asset_id = ?";  // <-- Changed
if ($stmtUpdate = $conn->prepare($sqlUpdateAsset)) {
    $stmtUpdate->bind_param("si", $defaultImagePath, $assetId);   // <-- Changed

    if ($stmtUpdate->execute()) {
        // Successfully updated asset, echo the default image path
        echo htmlspecialchars($defaultImagePath);  // Return the hardcoded path

    } else {
        error_log("Error updating asset: " . $stmtUpdate->error);
        http_response_code(500);
        echo "Error updating asset.";
    }
    $stmtUpdate->close();
} else {
    error_log("Error preparing asset update statement: " . $conn->error);
    http_response_code(500);
    echo "Error preparing asset update statement.";
}

$conn->close();
?>