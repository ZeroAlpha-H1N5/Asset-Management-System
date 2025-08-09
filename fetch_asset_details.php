<?php
require_once 'functions.php';

header('Content-Type: application/json');

$conn = db_connect();

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$assetId = isset($_GET['assetID']) ? intval($_GET['assetID']) : 0;

if ($assetId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID.']);
    exit;
}

$sql = "SELECT
                a.asset_id AS assetID,
                a.asset_brand AS assetName,
                a.asset_model AS assetModel,
                a.asset_serial_num AS serialNum,
                a.asset_tag AS assetTag,
                at.type_id AS typeID,
                ast.status_id AS statusID,
                DATE(a.asset_purchase_date) AS datePurchased,
                DATE(a.asset_register_date) AS dateRegistered,
                a.asset_depreciation_period AS deprecPeriod,
                a.asset_purchase_cost AS assetCost,
                a.asset_depreciated_cost AS deprecCost,
                sl.site_id AS siteID,
                sl.site_region AS region
            FROM
                assets a
            JOIN
                asset_type at ON a.type_id = at.type_id
            JOIN
                asset_status ast ON a.status_id = ast.status_id
            JOIN
                site_locations sl ON a.site_id = sl.site_id
            WHERE a.asset_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => "Error preparing statement: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $assetId);

if ($stmt->execute() === false) {
    echo json_encode(['success' => false, 'message' => "Error executing statement: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $asset = $result->fetch_assoc();
    echo json_encode(['success' => true,
                       'assetID' => $asset['assetID'],
                       'assetName' => $asset['assetName'],
                       'assetModel' => $asset['assetModel'],
                       'assetSerial' => $asset['serialNum'],
                       'assetTag' => $asset['assetTag'],
                       'assetType' => $asset['typeID'],
                       'assetStatus' => $asset['statusID'],
                       'assetCost' => $asset['assetCost'],
                       'deprecCost' => $asset['deprecCost'],
                       'deprecPeriod' => $asset['deprecPeriod'],
                       'assetPurchased' => $asset['datePurchased'],
                       'assetRegistered' => $asset['dateRegistered'],
                       'assetRegion' => $asset['region'],
                       'assetSiteLocation' => $asset['siteID']
                      ]);
} else {
    echo json_encode(['success' => false, 'message' => "Asset not found."]);
}

$stmt->close();
$conn->close();

?>