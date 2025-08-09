<?php
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Access user information from the session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// --- Available Sort Fields ---
$available_sort_fields = [
    'a.asset_brand'     => 'Asset Name',
    'a.asset_model'     => 'Asset Model',
    'at.type_name'      => 'Asset Type',
    'ast.status_name'   => 'Asset Status',
    'o.owner_name'      => 'Custodian',
    'd.department_name' => 'Department',
    'sl.site_name'     => 'Location',
    'a.asset_tag'       => 'Asset Tag',
    'a.asset_register_date' => 'Date Registered'
];

// --- Get Dropdown Options from Database ---
$conn = db_connect();
if (!$conn) { die("Database connection failed."); } //Fatal Error if DB connection fails.

// --- Asset Types ---
$asset_types = [];
$result = $conn->query("SELECT type_id, type_name FROM asset_type ORDER BY type_name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $asset_types[$row['type_id']] = $row['type_name'];
    }
    $result->free();
}
// --- Asset Statuses ---
$asset_statuses = [];
$result = $conn->query("SELECT status_id, status_name FROM asset_status ORDER BY status_name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $asset_statuses[$row['status_id']] = $row['status_name'];
    }
    $result->free();
}

// --- Departments ---
$departments = [];
$result = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[$row['department_id']] = $row['department_name'];
    }
    $result->free();
}

// --- Site Locations ---
$site_locations = [];
$result = $conn->query("SELECT site_id, site_name FROM site_locations ORDER BY site_name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $site_locations[$row['site_id']] = $row['site_name'];
    }
    $result->free();
}

// --- Selected Filter Values (Sanitized) ---
$selected_sort_by = isset($_GET['sort_by']) && array_key_exists($_GET['sort_by'], $available_sort_fields) ? $_GET['sort_by'] : 'a.asset_brand';
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';

// New Filter Variables
$selected_asset_type   = isset($_GET['asset_type'])   && array_key_exists($_GET['asset_type'], $asset_types)   ? $_GET['asset_type']   : '';
$selected_asset_status = isset($_GET['asset_status']) && array_key_exists($_GET['asset_status'], $asset_statuses) ? $_GET['asset_status'] : '';
$selected_department   = isset($_GET['department'])   && array_key_exists($_GET['department'], $departments)   ? $_GET['department']   : '';
$selected_site_location = isset($_GET['site_location']) && array_key_exists($_GET['site_location'], $site_locations) ? $_GET['site_location'] : '';

// --- Sorting Order ---
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'ASC';

// --- Pagination ---
$results_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$message = isset($_GET['export_message']) ? htmlspecialchars($_GET['export_message']) : ''; // Sanitized Message

// --- FUNCTIONS ---
function generateAssetTable(
    $search_term = '',
    $sort_by = 'a.asset_brand',
    $sort_order = 'ASC',
    $results_per_page = 10,
    $current_page = 1,
    $selected_asset_type = '',   //New
    $selected_asset_status = '', //New
    $selected_department = '',   //New
    $selected_site_location = ''  //New
) {
    $conn = db_connect();
    if (!$conn) { return "Error: Database connection failed."; } // Check connection
    $start_from = ($current_page - 1) * $results_per_page;

    // Base SQL query parts
    $sql_select_from = "SELECT
                a.asset_id AS id, a.asset_brand AS asset_name, a.asset_tag AS asset_tag,
                a.asset_model AS asset_model, a.asset_serial_num AS serial_num,
                at.type_name AS asset_type, ast.status_name AS status_name, a.asset_depreciation_period AS deprec_period,
                DATE(a.asset_purchase_date) AS date_purchased, DATE(a.asset_register_date) AS date_registered,
                o.owner_name AS assigned_to, o.owner_date_hired AS date_hired, o.owner_phone_num AS phone_num,
                d.department_name AS department, o.owner_position AS position, site_name AS site_location,
                sl.site_region AS region, a.asset_purchase_cost AS purchase_cost, a.asset_depreciated_cost AS deprec_cost
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
                site_locations sl ON a.site_id = sl.site_id";

    $where_clauses = [];
    $params = [];
    $types = "";

    // Build WHERE clause for the new filter options. The  '' check will exclude from where clause if not selected
    if ($selected_asset_type !== '') {
        $where_clauses[] = "a.type_id = ?";
        $params[] = $selected_asset_type;
        $types .= "i";
    }
    if ($selected_asset_status !== '') {
        $where_clauses[] = "a.status_id = ?";
        $params[] = $selected_asset_status;
        $types .= "i";
    }
    if ($selected_department !== '') {
        $where_clauses[] = "o.department_id = ?";
        $params[] = $selected_department;
        $types .= "i";
    }
    if ($selected_site_location !== '') {
        $where_clauses[] = "a.site_id = ?";
        $params[] = $selected_site_location;
        $types .= "i";
    }

    // Build WHERE clause ONLY for search_term
    if ($search_term !== '') {
        $like_search_term = '%' . $search_term . '%';
        $default_search_fields = [ // Define columns for general search
            'a.asset_brand', 'a.asset_model', 'a.asset_tag', 'a.asset_serial_num',
            'at.type_name', 'ast.status_name', 'o.owner_name', 'd.department_name',
            'sl.site_region'
        ];
        $search_or_clauses = [];
        foreach ($default_search_fields as $field) {
            $search_or_clauses[] = $field . " LIKE ?";
            $params[] = $like_search_term;
            $types .= "s";
        }
        if (!empty($search_or_clauses)) {
            $where_clauses[] = "(" . implode(" OR ", $search_or_clauses) . ")";
        }
    }

    // Construct final SQL
    $sql = $sql_select_from; // Start with SELECT/FROM
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    // Add ORDER BY clause
    $sql .= " ORDER BY " . $sort_by . " " . $sort_order;

    // Add LIMIT for pagination
    $sql .= " LIMIT ?, ?";
    $params[] = $start_from;
    $params[] = $results_per_page;
    $types .= "ii";

    // Prepare and Execute Statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparing statement (generateAssetTable): " . $conn->error . " | SQL: " . $sql);
        $conn->close(); // Close connection on error
        return "Error: Could not prepare statement.";
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Error executing statement (generateAssetTable): " . $stmt->error);
        $stmt->close();
        $conn->close();
        return "Error: Could not execute statement.";
    }
    $result = $stmt->get_result();

    //Build the HTML Table:
    $html = '<table class="asset-data-table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th><i class="fas fa-info-circle info-icon" title="Click the ID to view details"></i> ID</th>';
    $html .= '<th>Asset Name</th>';
    $html .= '<th>Asset Model</th>';
    $html .= '<th>Serial Number</th>';
    $html .= '<th>Asset Type</th>';
    $html .= '<th>Asset Tag</th>';
    $html .= '<th>Asset Status</th>';
    $html .= '<th>Date Registered</th>';
    $html .= '<th>Depreciation Period</th>';
    $html .= '<th>Purchase Cost</th>';
    $html .= '<th>Depreciated Cost</th>';
    $html .= '<th>Custodian</th>';
    $html .= '<th>Department</th>';
    $html .= '<th>Position</th>';
    $html .= '<th>Site Location</th>';
    $html .= '<th>Region</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    if ($result && $result->num_rows > 0) {
        $display_id_start = $start_from + 1;
        $display_id = $display_id_start;
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            $details_url = 'view_asset_details.php?asset_id=' . htmlspecialchars($row['id']);
            $html .= '<td><a href="' . $details_url . '">' . $display_id . '</a></td>';
            $html .= '<td>' . htmlspecialchars($row['asset_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['asset_model']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['serial_num']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['asset_type']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['asset_tag']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['status_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['date_registered']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['deprec_period']) . ' years</td>';
            $html .= '<td>₱ ' . htmlspecialchars($row['purchase_cost']) . '</td>';
            $html .= '<td>₱ ' . htmlspecialchars($row['deprec_cost']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['assigned_to']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['department']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['position']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['site_location']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['region']) . '</td>';
            $html .= '</tr>';
            $display_id++;
        }
    } else {
        $html .= '<tr><td colspan="16">No Assets Found.</td></tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Close the statement and connection
    $stmt->close();
    $conn->close();
    return $html;
}

function getTotalAssetCount(
    $search_term = '',
    $selected_asset_type = '',   //New
    $selected_asset_status = '', //New
    $selected_department = '',   //New
    $selected_site_location = ''  //New
) {
    $conn = db_connect();
    if (!$conn) { return 0; } // Check connection

    // Base SQL for count
    $sql_count_base = "SELECT COUNT(DISTINCT a.asset_id) AS total
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
                site_locations sl ON a.site_id = sl.site_id"; // Base query

    $where_clauses_count = [];
    $params_count = [];
    $types_count = "";
  // Build WHERE clause for the new filter options. The  '' check will exclude from where clause if not selected
    if ($selected_asset_type !== '') {
        $where_clauses_count[] = "a.type_id = ?";
        $params_count[] = $selected_asset_type;
        $types_count .= "i";
    }
    if ($selected_asset_status !== '') {
        $where_clauses_count[] = "a.status_id = ?";
        $params_count[] = $selected_asset_status;
        $types_count .= "i";
    }
    if ($selected_department !== '') {
        $where_clauses_count[] = "o.department_id = ?";
        $params_count[] = $selected_department;
        $types_count .= "i";
    }
    if ($selected_site_location !== '') {
        $where_clauses_count[] = "a.site_id = ?";
        $params_count[] = $selected_site_location;
        $types_count .= "i";
    }

    // Build WHERE clause (MUST MATCH generateAssetTable logic)
    if ($search_term !== '') {
        $like_search_term = '%' . $search_term . '%';
        $default_search_fields = [
            'a.asset_brand', 'a.asset_model', 'a.asset_tag', 'a.asset_serial_num',
            'at.type_name', 'ast.status_name', 'o.owner_name', 'd.department_name',
            'sl.site_region'
        ];
        $search_or_clauses = [];
        foreach ($default_search_fields as $field) {
            // No need to check against $available_filter_fields
            $search_or_clauses[] = $field . " LIKE ?";
            $params_count[] = $like_search_term;
            $types_count .= "s";
        }
        if (!empty($search_or_clauses)) {
            $where_clauses_count[] = "(" . implode(" OR ", $search_or_clauses) . ")";
        }
    } // End of: if ($search_term !== '')

    // Construct final count SQL
    $sql_count = $sql_count_base; // Start with base SELECT/FROM/JOIN
    if (!empty($where_clauses_count)) {
        $sql_count .= " WHERE " . implode(" AND ", $where_clauses_count); // Add WHERE if needed
    }

    // Prepare and Execute Statement
    $stmt_count = $conn->prepare($sql_count);
    if ($stmt_count === false) {
        error_log("Error preparing statement (getTotalAssetCount): " . $conn->error . " | SQL: " . $sql_count);
        $conn->close();
        return 0;
    }

    if (!empty($types_count)) {
        $stmt_count->bind_param($types_count, ...$params_count);
    }

    if (!$stmt_count->execute()) {
        error_log("Error executing statement (getTotalAssetCount): " . $stmt_count->error);
        $stmt_count->close();
        $conn->close();
        return 0;
    }

    $result_count = $stmt_count->get_result();
    $total = 0;
    if ($result_count) {
        $row = $result_count->fetch_assoc();
        $total = $row ? (int)$row['total'] : 0;
    }

    $stmt_count->close();
    $conn->close();
    return $total;
}

function displayPagination($current_page, $total_pages, $base_url) {
    $html = '<div class="pagination">';

    // First page link
    if ($current_page > 1) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=1"><<</a>';
    } else {
        $html .= '<span class="pagination-button disabled"><<</span>'; // Disable if on the first page
    }

    // Previous page link
    if ($current_page > 1) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . ($current_page - 1) . '"><</a>';
    } else {
        $html .= '<span class="pagination-button disabled"><</span>'; // Disable if on the first page
    }

    // Display page numbers (you can adjust the range as needed)
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="pagination-button current">' . $i . '</span>';
        } else {
            $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . $i . '">' . $i . '</a>';
        }
    }

    // Next page link
    if ($current_page < $total_pages) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . ($current_page + 1) . '">></a>';
    } else {
        $html .= '<span class="pagination-button disabled">></span>'; // Disable if on the last page
    }

    // Last page link
    if ($current_page < $total_pages) {
        $html .= '<a class="pagination-button" href="' . $base_url . '&page=' . $total_pages . '">>></a>';
    } else {
        $html .= '<span class="pagination-button disabled">>></span>'; // Disable if on the last page
    }

    $html .= '</div>';
    return $html;
}

// --- Get the total number of assets matching filters (for pagination) ---
$total_assets = getTotalAssetCount(
    $search_term,
    $selected_asset_type,
    $selected_asset_status,
    $selected_department,
    $selected_site_location
);
$total_pages = ($results_per_page > 0) ? ceil($total_assets / $results_per_page) : 0;

// --- Get the filtered assets for the current page ---
$TableHtml = generateAssetTable(
    $search_term,
    $selected_sort_by,
    $sort_order,
    $results_per_page,
    $current_page,
    $selected_asset_type,
    $selected_asset_status, //New
    $selected_department,  //New
    $selected_site_location //New
);

// --- Build the base URL for pagination links, preserving existing filter and sort parameters ---
$base_url = $_SERVER['PHP_SELF'] . '?';
$params = $_GET;  // Get all existing GET parameters
$base_url .= http_build_query($params);

$query_string = http_build_query($params);
if (!empty($query_string)) {
    $base_url .= $query_string;
}

$paginationHtml = displayPagination($current_page, $total_pages, $base_url);

// Ensure the database connection is closed
if (isset($conn) && $conn) {
  $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assets</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/functions.js"></script>
    <script src="js/assets.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo-container">
                <a href="dashboard.php" style="text-decoration: none;">
                    <img src="icons/safexpress_logo.png" alt="SafeXpress Logistics Logo" style="cursor: pointer;">
                </a>
            </div>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="assets.php" class="active">Assets</a></li>
                <li><a href="history.php">Logs</a></li>
                <li><a href="report_management.php">Export Reports</a></li>
                <li><a href="credits.php">Credits</a></li>
                <li><a href="#" id="logoutLink">Logout</a></li>
            </ul>
        </aside>
    <main class="content">
        <h2>Assets</h2>
        <div class="datetime-container">
            <span id="datetime"></span>
        </div>
        <button id="toggleSidebarButton">☰</button>
        <div class="category-cards-container">
            <?php
                $categories = [
                    "Computers" => "asset_selection.php?type=Computers",
                    "Phones" => "asset_selection.php?type=Phones",
                    "Devices" => "asset_selection.php?type=Devices",
                    "MHE" => "asset_selection.php?type=Material Handling Equipment",
                    "Tables" => "asset_selection.php?type=Tables",
                    "Chairs" => "asset_selection.php?type=Chairs",
                    "Cabinets" => "asset_selection.php?type=Cabinets",
                    "Others" => "asset_selection.php?type=Others"
                ];

                foreach ($categories as $category => $page) {
                    $imageName = strtolower(str_replace(' ', '_', $category));
                    $imagePath = "/SLI_ASSET/icons/" . $imageName . ".png";

                    echo '<a href="' . htmlspecialchars($page) . '" class="asset-category-link">';
                    echo '<div class="asset-category-card">';
                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($category) . '">';
                    echo '<p>' . htmlspecialchars($category) . '</p>';
                    echo '</div>';
                    echo '</a>';
                }
            ?>
        </div>

        <div class="asset-table">
            <div class="asset-table-header">
                <div class="header-left">
                    <h3>Asset List</h3>
                    <button id="openRegister" class="form-buttons">Register Asset</button>
                    <a href="settings.php" class="form-buttons">Settings</a>
                </div>
                <div class="header-right">
                    <form id="filterForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">

                        <div class="search-container">
                            <i class="fas fa-search search-icon-fa"></i>
                            <input type="text" name="search_term" id="search_term" value="<?php echo htmlspecialchars($search_term ?? ''); ?>" placeholder="Find an Asset">
                        </div>

                        <label for="asset_type">Asset Type:</label>
                        <select name="asset_type" id="asset_type">
                            <option value="">All Types</option>
                            <?php foreach ($asset_types as $type_id => $type_name): ?>
                                <option value="<?php echo htmlspecialchars($type_id); ?>" <?php if ($selected_asset_type == $type_id) echo 'selected'; ?>><?php echo htmlspecialchars($type_name); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="asset_status">Asset Status:</label>
                        <select name="asset_status" id="asset_status">
                            <option value="">All Statuses</option>
                            <?php foreach ($asset_statuses as $status_id => $status_name): ?>
                                <option value="<?php echo htmlspecialchars($status_id); ?>" <?php if ($selected_asset_status == $status_id) echo 'selected'; ?>><?php echo htmlspecialchars($status_name); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="department">Department:</label>
                        <select name="department" id="department">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $department_id => $department_name): ?>
                                <option value="<?php echo htmlspecialchars($department_id); ?>" <?php if ($selected_department == $department_id) echo 'selected'; ?>><?php echo htmlspecialchars($department_name); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="site_location">Site Location:</label>
                        <select name="site_location" id="site_location">
                            <option value="">All Sites</option>
                            <?php foreach ($site_locations as $site_id => $site_name): ?>
                                <option value="<?php echo htmlspecialchars($site_id); ?>" <?php if ($selected_site_location == $site_id) echo 'selected'; ?>><?php echo htmlspecialchars($site_name); ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div class="sort-select-wrapper">
                            <select name="sort_by" id="sort_by_select">
                                <?php
                                foreach ($available_sort_fields as $field_value => $field_label) {
                                    $selected_attr = ($selected_sort_by == $field_value) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($field_value) . '" ' . $selected_attr . '>' . htmlspecialchars($field_label) . '</option>';
                                }?>
                            </select>

                            <span class="sort-select-display" aria-hidden="true">
                                Sort by <?php echo htmlspecialchars($available_sort_fields[$selected_sort_by] ?? 'Asset Name'); ?>
                            </span>
                        </div>

                        <input type="hidden" name="sort_order" id="sort_order_hidden" value="<?php echo ($sort_order == 'DESC') ? 'DESC' : 'ASC'; ?>">
                        <button type="button" id="sort_toggle_button" class="sort-button" title="Toggle Sort Order">
                            <span class="sort-icon <?php echo ($sort_order == 'DESC') ? 'sort-desc' : 'sort-asc'; ?>"></span>
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-container">
                <?php echo $TableHtml; ?>
            </div>
        </div>

        <div id="pagination-buttons">
            <?php echo $paginationHtml; ?>
        </div>

        <div id="registerAssetModal" class="modal">
            <div class="modal-content">
                <span class="close">×</span>
                <form id="registerAssetForm" method="POST" enctype="multipart/form-data">
                    <h3>Asset Details</h3> 
                    <div>
                        <label for="asset_name">Asset Brand:</label>
                        <input type="text" id="asset_name" name="asset_name" required>
                    </div>

                    <div>
                        <label for="asset_model">Asset Model:</label>
                        <input type="text" id="asset_model" name="asset_model" required>
                    </div>

                    <div>
                        <label for="serial_num">Serial Number:</label>
                        <input type="text" id="serial_num" name="serial_num" required> 
                    </div>

                    <div>
                        <!-- Type Dropdown List -->
                        <?php include 'get_asset_type.php'; echo $assetTypeDropdown; ?>
                    </div>

                    <div>
                        <!-- Status Dropdown List -->
                        <?php include 'get_asset_status.php'; echo $assetStatusDropdown; ?>
                    </div>

                    <div>
                        <label for="date_purchased">Date Purchased:</label>
                        <input type="date" id="date_purchased" name="date_purchased" required> 
                    </div>

                    <div>
                        <label for="date_registered">Date Registered:</label>
                        <input type="date" id="date_registered" name="date_registered" required> 
                    </div>

                    <div>
                        <label for="deprec_period">Depreciation Period (Years):</label>
                        <input type="text" id="deprec_period" name="deprec_period" required> 
                    </div>

                    <div>
                        <label for="purchase_cost">Buying Cost:</label>
                        <input type="text" id="purchase_cost" name="purchase_cost" required> 
                    </div>

                    <div>
                        <label for="deprec_cost">Depreciated Cost:</label>
                        <input type="text" id="deprec_cost" name="deprec_cost" readonly required> 
                    </div>

                    <div>
                        <!-- Site Location Dropdown List -->
                        <?php include 'get_site_locations.php'; echo $siteLocationDropdown; ?>
                    </div>

                    <div>
                        <!-- Upload Button -->
                        <label>Upload Asset Image (5MB Max.):</label>
                        <div class="upload-container">
                            <label for="imageUpload" class="upload-label">
                                <span>Click to <b>Browse</b> your files</span>
                            </label>
                            <input type="file" id="imageUpload" name="imageUpload" accept="image/*" style="display:none;" required>
                            <div id="imagePreviewContainer" style="display:none;">
                                <img id="imagePreview" src="#" alt="Image Preview">
                                <div id="imageInfo">
                                    <span id="imageFilename"></span>
                                    <span id="imageFilesize"></span>
                                </div>
                                <button id="changePictureButton" type="button">Change Picture</button>
                            </div>
                        </div>
                    </div>

                    <h3>Custodian Details</h3> 
                    <div>
                        <label for="assigned_to">Name:</label>
                        <input type="text" id="assigned_to" name="assigned_to" required>
                    </div>

                    <div>
                        <!-- Department Dropdown List -->
                        <?php include 'get_departments.php'; echo $departmentDropdown; ?>
                    </div>
                    
                    <div>
                        <label for="position">Position:</label>
                        <input type="text" id="position" name="position" required> 
                    </div>
                    
                    <div>
                        <label for="date_hired">Date Hired:</label>
                        <input type="date" id="date_hired" name="date_hired"> 
                    </div>

                    <div>
                        <label for="phone_num">Phone Number:</label>
                        <input type="text" id="phone_num" name="phone_num"> 
                    </div>

                    <div class="modal-buttons">
                        <button type="submit" id="registerAssetButton">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2>Asset registered successfully!</h2>
            <p>Asset Tag Preview:</p>
            <div id="tag-preview">
                <!-- Iframe to display the PDF -->
                <iframe id="pdfPreviewIframe" style="width:500px; height:200px; display:none;"></iframe>
            </div>
            <div class="modal-buttons">
                <button id="downloadPdfButton">Download PDF</button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Popup -->
    <div id="logoutModal" class="modal">
        <div class="modal-content logout-style">
            <span class="close">×</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to log out?</p>
            <div class="modal-buttons">
                <button id="confirmLogout">Yes, Logout</button> <br> <br>
                <button id="cancelLogout">Cancel</button>
            </div>
        </div>
    </div>
</body>
</html>