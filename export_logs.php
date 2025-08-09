<?php
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

// Function to safely retrieve POST values, handling missing keys
function getPostValue(string $key, string $default = ''): string {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

// Retrieve export parameters for Registered Assets
$export_filter = getPostValue('export_filter_registered');
$export_asset_type = getPostValue('asset_type_registered');
$export_asset_status = getPostValue('asset_status_registered');
$export_department = getPostValue('departmentID_registered');
$export_site_location = getPostValue('site_id_registered');
$export_asset_brand_order = getPostValue('export_asset_brand', 'ASC');

// Retrieve export parameters for Turnovered Assets
$export_filter_turnovered = getPostValue('export_filter_turnovered');
$export_asset_type_turnovered = getPostValue('asset_type'); // Note: Can share the same asset_type field
$export_asset_status_turnovered = getPostValue('asset_status'); // and asset_status fields as the above
$export_department_turnovered = getPostValue('departmentID');
$export_site_location_turnovered = getPostValue('site_id');
$export_asset_brand_order_turnovered = getPostValue('export_asset_brand_turnovered', 'ASC');

function generateExportSQL(
    string $tableType,  // 'registered' or 'turnovered'
    string $filter,
    string $asset_type,
    string $asset_status,
    string $department,
    string $site_location,
    string $asset_brand_order
): string {
    $conn = db_connect();
    if (!$conn) {
        return false;
    }

    $sql = "";

    if ($tableType == 'registered') {
        $sql = "SELECT
                a.asset_brand AS Asset_Name,
                at.type_name AS Asset_Type,
                o.owner_name AS Owner_Name,
                ast.status_name AS Asset_Status,
                DATE(a.asset_register_date) AS Date_Registered,
                sl.site_name AS Site_Location,
                d.department_name AS Department
            FROM
                assets a
            JOIN
                asset_type at ON a.type_id = at.type_id
            JOIN
                asset_status ast ON a.status_id = ast.status_id
            JOIN
                owners o ON a.owner_id = o.owner_id
            JOIN
                site_locations sl ON a.site_id = sl.site_id
            JOIN
                departments d ON o.department_id = d.department_id
            WHERE NOT EXISTS (SELECT 1 FROM asset_turnover_log atl WHERE atl.asset_id = a.asset_id)";
    } elseif ($tableType == 'turnovered') {
        $sql = "SELECT
                a.asset_brand AS Asset_Name,
                at.type_name AS Asset_Type,
                po.owner_name AS Previous_Owner,
                ast.status_name AS Asset_Status,
                DATE(atl.turnover_date) AS Date_Turnovered,
                sl.site_name AS Previous_Site,
                d.department_name AS Department
            FROM
                assets a
            JOIN
                asset_type at ON a.type_id = at.type_id
            JOIN
                asset_status ast ON a.status_id = ast.status_id
            JOIN
                asset_turnover_log atl ON a.asset_id = atl.asset_id
            JOIN
                owners po ON atl.previous_owner_id = po.owner_id
            JOIN
                site_locations sl ON atl.previous_site_id = sl.site_id
             JOIN
                departments d ON po.department_id = d.department_id";
    } else {
        $conn->close();
        return false;  // Or throw an exception, depending on your error handling
    }


    if ($filter == 'asset_type' && !empty($asset_type)) {
        $sql .= " AND at.type_id = '" . $conn->real_escape_string($asset_type) . "'";
    } elseif ($filter == 'asset_status' && !empty($asset_status)) {
        $sql .= " AND ast.status_id = '" . $conn->real_escape_string($asset_status) . "'";
    } elseif ($filter == 'department' && !empty($department)) {
        $sql .= " AND d.department_id = '" . $conn->real_escape_string($department) . "'";
    } elseif ($filter == 'site_location' && !empty($site_location)) {
        $sql .= " AND sl.site_id = '" . $conn->real_escape_string($site_location) . "'";
    }

    if($filter == 'asset_brand'){
        $sql .= " ORDER BY a.asset_brand " . ($asset_brand_order == 'DESC' ? 'DESC' : 'ASC');
    }
    $sql .= ";";

    $conn->close();
    return $sql;
}


function hasDataToExport(
    string $tableType,
    string $filter,
    string $asset_type,
    string $asset_status,
    string $department,
    string $site_location,
    string $asset_brand_order
): bool {
    $sql = generateExportSQL($tableType, $filter, $asset_type, $asset_status, $department, $site_location, $asset_brand_order);

    if (!$sql) {
        return false;
    }

    $conn = db_connect();
    if (!$conn) {
        return false;
    }

    $result = $conn->query($sql);

    if (!$result) {
        $conn->close();
        return false;
    }

    $hasData = ($result->num_rows > 0);

    $conn->close();
    return $hasData;
}


function exportToCSV(
    string $tableType,
    string $filter,
    string $asset_type,
    string $asset_status,
    string $department,
    string $site_location,
    string $asset_brand_order
): string {
    $sql = generateExportSQL($tableType, $filter, $asset_type, $asset_status, $department, $site_location, $asset_brand_order);

    if (!$sql) {
        return "Error generating export SQL.";
    }

    $conn = db_connect();
    if (!$conn) {
        return "Error: Database connection failed.";
    }

    $result = $conn->query($sql);

    if (!$result) {
        $conn->close();
        return "Error: Could not execute export query. " . $conn->error;
    }

    if ($result->num_rows == 0) {
        $conn->close();
        return "No data to export.";
    }

    $filename = ($tableType == 'registered') ? "SLI_REGISTERED_ASSETS.csv" : "SLI_TURNOVERED_ASSETS.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    $fields = array_keys($result->fetch_assoc());
    fputcsv($output, $fields);

    $result->data_seek(0);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    $conn->close();

    exit;
}


// Handle Registered Assets Export
if (isset($_POST['export_csv_registered'])) {
    $message = "";

    if (hasDataToExport(
        'registered',
        $export_filter,
        $export_asset_type,
        $export_asset_status,
        $export_department,
        $export_site_location,
        $export_asset_brand_order
    )) {
        ob_start();
        exportToCSV(
            'registered',
            $export_filter,
            $export_asset_type,
            $export_asset_status,
            $export_department,
            $export_site_location,
            $export_asset_brand_order
        );
        ob_end_clean();
    } else {
        $message = "No data to be exported for the selected filter criteria (Registered Assets).";
        $redirectURL = 'report_management.php?export_message=' . urlencode($message);
        header("Location: " . $redirectURL);
        exit();
    }
}

// Handle Turnovered Assets Export
if (isset($_POST['export_csv_turnovered'])) {
    $message = "";

    if (hasDataToExport(
        'turnovered',
        $export_filter_turnovered,
        $export_asset_type_turnovered,
        $export_asset_status_turnovered,
        $export_department_turnovered,
        $export_site_location_turnovered,
        $export_asset_brand_order_turnovered
    )) {
        ob_start();
        exportToCSV(
            'turnovered',
            $export_filter_turnovered,
            $export_asset_type_turnovered,
            $export_asset_status_turnovered,
            $export_department_turnovered,
            $export_site_location_turnovered,
            $export_asset_brand_order_turnovered
        );
        ob_end_clean();
    } else {
        $message = "No data to be exported for the selected filter criteria (Turnovered Assets).";
        $redirectURL = 'report_management.php?export_message=' . urlencode($message);
        header("Location: " . $redirectURL);
        exit();
    }
}

// If no export button was pressed
header("Location: report_management.php");
exit;
?>