<?php
require_once 'functions.php';

$conn = db_connect();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Get filter parameters from the URL ---
$statusFilter = $_GET['status'] ?? 'all';
$locationFilter = $_GET['location'] ?? 'all';   
$departmentFilter = $_GET['department'] ?? 'all'; 

// --- Define Table and Column Names ---
$assetTable = 'assets'; $assetIdCol = 'asset_id'; $assetTypeIdFk = 'type_id';
$assetStatusIdFk = 'status_id'; $assetSiteIdFk = 'site_id'; $assetOwnerIdFk = 'owner_id';
$assetTypeTable = 'asset_type'; $assetTypeIdPk = 'type_id'; $assetTypeNameCol = 'type_name';
$assetStatusTable = 'asset_status'; $assetStatusIdPk = 'status_id'; $assetStatusNameCol = 'status_name';
$ownerTable = 'owners'; $ownerIdPk = 'owner_id'; $ownerDeptIdFk = 'department_id';
  
// --- Get ALL asset type names (categories) ---
$allCategories = [];
$allCategoriesSql = "SELECT {$assetTypeNameCol} FROM {$assetTypeTable} ORDER BY {$assetTypeNameCol} ASC";
$allCategoriesResult = $conn->query($allCategoriesSql);

if ($allCategoriesResult) { 
    while ($row = $allCategoriesResult->fetch_assoc()) { 
        if (!empty($row[$assetTypeNameCol])) { 
            $allCategories[] = $row[$assetTypeNameCol]; 
        } } 
    $allCategoriesResult->free(); 
} else { 
    error_log("SQL Error fetching categories: ".$conn->error); 
    header('Content-Type: application/json'); 
    echo json_encode(['error' => 'Could not get categories.', 'labels'=>[], 'datasets'=>[]]); 
    $conn->close(); 
    exit; 
}

// --- Get ALL statuses and assign colors ---
$allStatuses = [];
$statusColors = []; 
$baseStatusColors = [ 
    'Brand New'    => 'rgba(54, 162, 235, 0.8)', 
    'Used'         => 'rgba(255, 206, 86, 0.8)', 
    'Operational'  => 'rgba(75, 192, 192, 0.8)',
    'For Repair'   => 'rgba(255, 159, 64, 0.8)', 
    'For Disposal' => 'rgba(255, 99, 132, 0.8)',  
    'Other'        => 'rgba(153, 102, 255, 0.8)' 
];
$statusColorKeys = array_keys($baseStatusColors);
$statusColorIndex = 0;

$allStatusSql = "SELECT {$assetStatusNameCol} FROM {$assetStatusTable} ORDER BY FIELD({$assetStatusNameCol}, 'Brand New', 'Used', 'Operational', 'For Repair', 'For Disposal'), {$assetStatusNameCol} ASC"; // Order logically
$allStatusResult = $conn->query($allStatusSql);
if ($allStatusResult) {
    while ($row = $allStatusResult->fetch_assoc()) {
        $statusName = $row[$assetStatusNameCol];
        if (!empty($statusName)) {
            $allStatuses[] = $statusName;
            if (isset($baseStatusColors[$statusName])) {
                $statusColors[$statusName] = $baseStatusColors[$statusName];
            } else {
                $fallbackColorKey = $statusColorKeys[count($baseStatusColors)-1 + ($statusColorIndex % (count($statusColorKeys)-1))]; 
                $statusColors[$statusName] = $baseStatusColors[$fallbackColorKey];
                $statusColorIndex++;
                error_log("Warning: Status '{$statusName}' not found in predefined colors. Assigned fallback.");
            }
        }
    }
    $allStatusResult->free();
} else { 
    error_log("SQL Error fetching statuses: ".$conn->error); 
    header('Content-Type: application/json'); 
    echo json_encode(['error' => 'Could not get statuses.', 'labels'=>$allCategories, 'datasets'=>[]]); 
    $conn->close(); exit; }
if (empty($allStatuses)) { 
    header('Content-Type: application/json'); 
    echo json_encode(['labels'=>$allCategories, 'datasets'=>[]]); 
    $conn->close(); exit; }

// --- Initialize results structure ---
$results = [];
foreach ($allCategories as $category) {
    $results[$category] = array_fill_keys($allStatuses, 0);
}

// --- Build and Execute Query for Counts (Grouped by Category AND Status) ---
$sql = "SELECT
            at.{$assetTypeNameCol} AS category,
            as2.{$assetStatusNameCol} AS status, 
            COUNT(a.{$assetIdCol}) AS asset_count
        FROM
            {$assetTable} a
        LEFT JOIN {$assetTypeTable} at ON a.{$assetTypeIdFk} = at.{$assetTypeIdPk}
        LEFT JOIN {$assetStatusTable} as2 ON a.{$assetStatusIdFk} = as2.{$assetStatusIdPk}
        LEFT JOIN {$ownerTable} o ON a.{$assetOwnerIdFk} = o.{$ownerIdPk}
        ";

$whereClauses = []; $params = []; $types = "";

// Add filters 
if ($statusFilter !== 'all') { $whereClauses[] = "as2.{$assetStatusNameCol} = ?"; $params[] = $statusFilter; $types .= "s"; }
if ($locationFilter !== 'all') { $whereClauses[] = "a.{$assetSiteIdFk} = ?"; $params[] = $locationFilter; $types .= "i"; }
if ($departmentFilter !== 'all') { $whereClauses[] = "o.{$ownerDeptIdFk} = ?"; $params[] = $departmentFilter; $types .= "i"; }

// Append WHERE clause if filters active
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

// Group by BOTH category AND status
$sql .= " GROUP BY at.{$assetTypeNameCol}, as2.{$assetStatusNameCol}";

// Prepare & Execute (with error handling from previous version)
$stmt = $conn->prepare($sql);
if ($stmt === false) { 
    error_log("SQL Prepare Error: ".$conn->error." | SQL: ".$sql); 
    header('Content-Type: application/json'); 
    echo json_encode(['error'=>'DB prep failed.', 'labels'=>$allCategories, 'datasets'=>[]]); 
    $conn->close(); exit; }

if (!empty($params)) { 
    if (strlen($types) !== count($params)) { 
        error_log("Param mismatch"); 
        header('Content-Type: application/json'); 
        echo json_encode(['error'=>'Param error.', 'labels'=>$allCategories, 'datasets'=>[]]); 
        $stmt->close(); $conn->close(); exit; } $stmt->bind_param($types, ...$params); }

if (!$stmt->execute()) { 
    error_log("SQL Execute Error: ".$stmt->error); 
    header('Content-Type: application/json'); 
    echo json_encode(['error'=>'DB exec failed.', 'labels'=>$allCategories, 'datasets'=>[]]); 
    $stmt->close(); $conn->close(); exit; }
$result = $stmt->get_result();

if ($result === false) { 
    error_log("SQL Get Result Error: ".$stmt->error); 
    header('Content-Type: application/json'); 
    echo json_encode(['error'=>'DB result failed.', 'labels'=>$allCategories, 'datasets'=>[]]); 
    $stmt->close(); $conn->close(); exit; }

// --- Populate counts from results into the $results structure ---
while ($row = $result->fetch_assoc()) {
    $categoryName = $row['category'];
    $statusName = $row['status'];
    if (!empty($categoryName) && isset($results[$categoryName]) && !empty($statusName) && isset($results[$categoryName][$statusName])) {
        $results[$categoryName][$statusName] = (int)$row['asset_count'];
    }
}
$stmt->close();

// --- Format data for Chart.js stacked bar chart ---
$finalLabels = $allCategories;
$datasets = [];

// Create one dataset per STATUS
foreach ($allStatuses as $status) {
    $dataForStatus = [];
    // For the current status, get the count for each category
    foreach ($finalLabels as $category) {
        $dataForStatus[] = $results[$category][$status]; // Get the pre-calculated count
    }

    // Add the dataset for this status
    $datasets[] = [
        'label' => $status,
        'data' => $dataForStatus,
        'backgroundColor' => $statusColors[$status] ?? $baseStatusColors['Other'], 
        'borderColor' => str_replace('0.8', '1', ($statusColors[$status] ?? $baseStatusColors['Other'])), 
        'borderWidth' => 1,
    ];
}

$chartData = [
    'labels' => $finalLabels,
    'datasets' => $datasets 
];

$conn->close();

header('Content-Type: application/json');
echo json_encode($chartData);
?>