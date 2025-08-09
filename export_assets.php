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

// Retrieve export parameters from POST
$export_filter = getPostValue('export_filter');
$export_asset_type = getPostValue('asset_type');
$export_asset_status = getPostValue('asset_status');
$export_department = getPostValue('departmentID');
$export_site_location = getPostValue('site_id');
$export_asset_brand = getPostValue('export_asset_brand', 'ASC');

function generateExportSQL(
    $filter = '',
    $asset_type = '',
    $asset_status = '',
    $department = '',
    $site_location = '',
    $asset_brand = 'ASC'
) {
    $conn = db_connect();
    if (!$conn) { return false; }

    $sql = "SELECT
                a.asset_brand AS Asset_Name,
                a.asset_model AS Asset_Model,
                a.asset_serial_num AS Serial_Number,
                at.type_name AS Asset_Type,
                a.asset_tag AS Asset_Tag,
                ast.status_name AS Asset_Status,
                DATE(a.asset_register_date) AS Date_Registered,
                at.type_depreciation_period AS Depreciation_Period,
                a.asset_purchase_cost AS Purchase_Cost,
                a.asset_depreciated_cost AS Depreciated_Cost,
                o.owner_name AS Custodian,
                d.department_name AS Department,
                o.owner_position AS Position,
                sl.site_name AS Site_Location,
                sl.site_region AS Region
            FROM
                assets a
            JOIN
                asset_type at ON a.type_id = at.type_id
            JOIN
                asset_status ast ON a.status_id = ast.status_id
            JOIN
                owners o ON a.owner_id = o.owner_id
            JOIN
                departments d ON o.department_id = d.department_id
            JOIN
                site_locations sl ON a.site_id = sl.site_id
            WHERE 1=1"; // Start with a WHERE 1=1 so we can easily add more conditions

    // Apply filters based on what's selected
    if ($filter == 'asset_type' && !empty($asset_type)) {
        $sql .= " AND at.type_id = '" . $conn->real_escape_string($asset_type) . "'";
    } elseif ($filter == 'asset_status' && !empty($asset_status)) {
        $sql .= " AND ast.status_id = '" . $conn->real_escape_string($asset_status) . "'";
    } elseif ($filter == 'department' && !empty($department)) {
        $sql .= " AND d.department_id = '" . $conn->real_escape_string($department) . "'";
    } elseif ($filter == 'site_location' && !empty($site_location)) {
        $sql .= " AND sl.site_id = '" . $conn->real_escape_string($site_location) . "'";
    }

    // Always add asset_brand sorting if that's what's chosen
    if($filter == 'asset_brand'){
        $sql .= " ORDER BY a.asset_brand " . ($asset_brand == 'DESC' ? 'DESC' : 'ASC');
    }
    $sql .= ";";

    $conn->close();
    return $sql;
}


function hasDataToExport(
     $filter = '',
    $asset_type = '',
    $asset_status = '',
    $department = '',
    $site_location = '',
    $asset_brand = 'ASC'
) {
        $sql = generateExportSQL(
            $filter,
            $asset_type,
            $asset_status,
            $department,
            $site_location,
            $asset_brand
        );

    if (!$sql) { return false; }

    $conn = db_connect();
    if (!$conn) { return false; }

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
    $filter = '',
    $asset_type = '',
    $asset_status = '',
    $department = '',
    $site_location = '',
    $asset_brand = 'ASC'
) {
      $sql = generateExportSQL(
            $filter,
            $asset_type,
            $asset_status,
            $department,
            $site_location,
            $asset_brand
        );

    if (!$sql) { return "Error generating export SQL."; }

    $conn = db_connect();
    if (!$conn) { return "Error: Database connection failed."; }

    $result = $conn->query($sql);

    if (!$result) {
        $conn->close();
        return "Error: Could not execute export query. " . $conn->error;
    }

    if ($result->num_rows == 0) {
        $conn->close();
        return "No data to export.";
    }

     $csvFilename = "SLI-ASSETS-MONITORING.csv"; // Define filename

    // Set the headers
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"".$csvFilename."\"");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add column headers
    $fields = array_keys($result->fetch_assoc()); // Extract column names
    fputcsv($output, $fields);

    // Reset the pointer to the beginning of the result set
    $result->data_seek(0);

    // Output table rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output); // Close file pointer
    $conn->close(); // Close connection

    exit; // IMPORTANT: Stop further script execution
}

// Check for the Export
if (isset($_POST['export_csv'])) {
    // Local scope variables
    $export_filter = getPostValue('export_filter');
    $export_asset_type = getPostValue('asset_type');
    $export_asset_status = getPostValue('asset_status');
    $export_department = getPostValue('departmentID');
    $export_site_location = getPostValue('site_id');
    $export_asset_brand = getPostValue('export_asset_brand');
    $message = ""; // Local message

    if(hasDataToExport(
        $export_filter,
        $export_asset_type,
        $export_asset_status,
        $export_department,
        $export_site_location,
        $export_asset_brand
    )) {
        exportToCSV(
            $export_filter,
            $export_asset_type,
            $export_asset_status,
            $export_department,
            $export_site_location,
            $export_asset_brand
        );
         exit(); // Terminate to prevent further execution
    } else{
        $message = "No data to be exported for the selected filter criteria (Assets).";
        $redirectURL = 'report_management.php?export_message=' . urlencode($message);
        header("Location: ".$redirectURL);
        exit();
    }

} else {
    // Redirect back to the asset list page if the export wasn't triggered correctly
    header("Location: report_management.php");
    exit();
}
?>