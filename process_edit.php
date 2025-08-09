<?php
require_once 'functions.php';

$conn = db_connect();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assetID = isset($_POST['assetID']) ? intval($_POST['assetID']) : 0;
$assetName = isset($_POST['assetName']) ? sanitize_input($_POST['assetName']) : '';
$assetModel = isset($_POST['assetModel']) ? sanitize_input($_POST['assetModel']) : '';
$assetSerial = isset($_POST['assetSerial']) ? sanitize_input($_POST['assetSerial']) : '';
$typeID = isset($_POST['typeID']) ? intval($_POST['typeID']) : 0;
$statusID = isset($_POST['statusID']) ? intval($_POST['statusID']) : 0;
$deprecPeriod = isset($_POST['deprecPeriod']) ? sanitize_input(($_POST['deprecPeriod'])) : '';
$assetCost = isset($_POST['assetCost']) ? sanitize_input($_POST['assetCost']) : '';
$deprecCost = isset($_POST['deprecCost']) ? sanitize_input($_POST['deprecCost']) : '';
$datePurchased = isset($_POST['datePurchased']) ? sanitize_input($_POST['datePurchased']) : null;
$dateRegistered = isset($_POST['dateRegistered']) ? sanitize_input($_POST['dateRegistered']) : '';

//--- Validation ---
$errors = [];
if ($assetID <= 0) {
    $errors[] = "Invalid Asset ID.";
}
if (empty($assetName)) {
    $errors[] = "Asset Name is required.";
}
if ($typeID <= 0) {
    $errors[] = "Asset Type is required.";
}
if ($statusID <= 0) {
    $errors[] = "Asset Status is required.";
}

if (empty($dateRegistered)) {
    $errors[] = "Asset Register Date is required.";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode("<br>", $errors)]);
    exit;
}

//--- Fetch Original Asset Details ---
$sqlOriginal = "SELECT asset_tag, type_id FROM assets WHERE asset_id = ?";
$stmtOriginal = $conn->prepare($sqlOriginal);
if ($stmtOriginal === false) {
    error_log("Error preparing original asset statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => "Error preparing statement."]);
    exit;
}
$stmtOriginal->bind_param("i", $assetID);
if (!$stmtOriginal->execute()) {
    error_log("Error executing original asset statement: " . $stmtOriginal->error);
    echo json_encode(['success' => false, 'message' => "Error executing statement."]);
    exit;
}
$stmtOriginal->bind_result($originalAssetTag, $originalTypeID);
$stmtOriginal->fetch();
$stmtOriginal->close();

//--- Check if type_id has changed ---
$updateAssetTag = false;
if ($typeID != $originalTypeID) {
    $updateAssetTag = true;
}

//--- Construct Update Query ---
$sql = "UPDATE assets SET asset_brand = ?, asset_model = ?, asset_serial_num = ?, type_id = ?, status_id = ?, asset_depreciation_period = ?, asset_purchase_cost = ?, asset_depreciated_cost = ?, asset_purchase_date = ?, asset_register_date = ? WHERE asset_id = ?";
$stmt = $conn->prepare($sql);  //Preparing query to avoid SQL injection
if ($stmt === false) {
    error_log("Error preparing update statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => "Error preparing update statement."]);
    exit;
}

$stmt->bind_param("sssiisddssi", $assetName, $assetModel, $assetSerial, $typeID, $statusID, $deprecPeriod, $assetCost, $deprecCost, $datePurchased, $dateRegistered, $assetID);

if (!$stmt->execute()) {
    error_log("Error executing update statement: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => "Error executing update statement."]);
    exit;
}

$stmt->close();

//--- Update Asset Tag If Type Changed ---
if ($updateAssetTag) {
    $sqlTypeCode = "SELECT type_code FROM asset_type WHERE type_id = ?";
    $stmtTypeCode = $conn->prepare($sqlTypeCode);

    if ($stmtTypeCode === false) {
        error_log("Error preparing type code statement: " . $conn->error);
        echo json_encode(['success' => false, 'message' => "Error preparing type code statement."]);
        exit;
    }

    $stmtTypeCode->bind_param("i", $typeID);

    if (!$stmtTypeCode->execute()) {
        error_log("Error executing type code statement: " . $stmtTypeCode->error);
        echo json_encode(['success' => false, 'message' => "Error executing type code statement."]);
        exit;
    }

    $stmtTypeCode->bind_result($type_code);
    $stmtTypeCode->fetch();
    $stmtTypeCode->close();

    $lastDashPos = strrpos($originalAssetTag, '-');
    if ($lastDashPos === false) {
        $next_number = 1;
    }
    else {
        $last_number = (int)substr($originalAssetTag, $lastDashPos + 1);
        $next_number = $last_number;
    }

    //--- Construct New asset_tag ---
    $newAssetTag = "SLI-" . $type_code . "-" . sprintf("%04d", $next_number);

    //--- Update asset_tag in Database ---
    $sqlUpdateTag = "UPDATE assets SET asset_tag = ? WHERE asset_id = ?";
    $stmtUpdateTag = $conn->prepare($sqlUpdateTag);

    if ($stmtUpdateTag === false) {
        error_log("Error preparing asset_tag update statement: " . $conn->error);
        echo json_encode(['success' => false, 'message' => "Error preparing asset_tag update statement."]);
        exit;
    }

    $stmtUpdateTag->bind_param("si", $newAssetTag, $assetID);

    if (!$stmtUpdateTag->execute()) {
        error_log("Error executing asset_tag update statement: " . $stmtUpdateTag->error);
        echo json_encode(['success' => false, 'message' => "Error executing asset_tag update statement."]);
        exit;
    }

    $stmtUpdateTag->close();
}

echo json_encode(['success' => true, 'message' => "Asset updated successfully."]);

$conn->close();
?>