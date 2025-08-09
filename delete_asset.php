<?php
require_once 'functions.php';

// Validate assetId
$assetId = isset($_POST['assetID']) ? intval($_POST['assetID']) : 0;

if ($assetId <= 0) {
    error_log("Invalid assetId provided.");
    http_response_code(400);
    echo "Invalid asset ID.";
    exit;
}

$conn = db_connect();

// Start transaction
$conn->begin_transaction();

try {
    // Step 1: Delete related records from asset_turnover_log
    $sql_delete_log = "DELETE FROM asset_turnover_log WHERE asset_id = ?";
    $stmt_log = $conn->prepare($sql_delete_log);

    if ($stmt_log === false) {
        throw new Exception("Error preparing turnover log delete statement: " . $conn->error);
    }

    $stmt_log->bind_param("i", $assetId);

    if (!$stmt_log->execute()) {
        throw new Exception("Error deleting from asset_turnover_log: " . $stmt_log->error);
    }

    $stmt_log->close();

    // Step 2: Delete the asset from the assets table
    $sql_delete_asset = "DELETE FROM assets WHERE asset_id = ?";
    $stmt_asset = $conn->prepare($sql_delete_asset);

    if ($stmt_asset === false) {
        throw new Exception("Error preparing asset delete statement: " . $conn->error);
    }

    $stmt_asset->bind_param("i", $assetId);

    if (!$stmt_asset->execute()) {
        throw new Exception("Error deleting asset: " . $stmt_asset->error);
    }

    $stmt_asset->close();

    // Commit transaction
    $conn->commit();

    echo "Asset deleted successfully.";  // Success Message

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log($e->getMessage());
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>