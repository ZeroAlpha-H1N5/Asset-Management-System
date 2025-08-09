<?php
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Access user information from the session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Create connection
$conn = db_connect();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the asset type from the query string
$asset_type_name = isset($_GET['type']) ? $_GET['type'] : null;

// Validate the asset type (important for security!)
$allowed_asset_types = ["Computers", "Phones", "Devices", "Material Handling Equipment", "Tables", "Chairs", "Cabinets", "Others"]; // List of allowed asset types
if (!in_array($asset_type_name, $allowed_asset_types)) {
    die("Invalid asset type."); // Prevent arbitrary queries
}
if ($asset_type_name === null) {
    die("Asset Type must be specified in the URL (e.g. asset_list.php?type=Computers)");
}

// --- Get Search and Sort Parameters ---
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';

// Define allowed sort fields and default
$allowed_sort_fields = [
    'a.asset_brand' => 'Asset Name', // Assuming asset_brand holds the name
    'a.asset_tag' => 'Asset Tag'
];
// Get selected sort field, default to asset name (brand)
$selected_sort_by = isset($_GET['sort_by']) && array_key_exists($_GET['sort_by'], $allowed_sort_fields) ? $_GET['sort_by'] : 'a.asset_brand';
// Get sort order, default to ASC
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'ASC';

// --- Pagination settings ---
$results_per_page = 16;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Ensure page >= 1
$start_from = ($current_page - 1) * $results_per_page;

// --- Prepare SQL Query ---
$sql_select = "SELECT
                a.asset_id AS asset_id, a.asset_brand AS asset_name, a.asset_tag AS asset_tag,
                a.asset_model AS asset_model, a.asset_serial_num AS serial_num, at.type_name AS asset_type,
                ast.status_name AS status_name, DATE(a.asset_purchase_date) AS date_purchased,
                DATE(a.asset_register_date) AS date_registered, o.owner_name AS assigned_to,
                o.owner_date_hired AS date_hired, o.owner_phone_num AS phone_num, sl.site_name AS site_location,
                d.department_name AS department, o.owner_position AS position,
                a.asset_purchase_cost AS est_cost, a.image_path AS image_path";
$sql_from_joins = " FROM assets a
                    JOIN asset_type at ON a.type_id = at.type_id
                    JOIN asset_status ast ON a.status_id = ast.status_id
                    JOIN owners o ON a.owner_id = o.owner_id
                    JOIN site_locations sl ON a.site_id = sl.site_id
                    JOIN departments d ON o.department_id = d.department_id ";
$sql_where_base = " WHERE at.type_name = ? "; // Base filter by type

$where_clauses = []; // For search conditions
$params = [$asset_type_name]; // Start params with type name
$types = "s";       // Start types with string for type name

// --- Add search condition ---
if ($search_term !== '') {
    $like_search_term = '%' . $search_term . '%';
    // Search only Asset Name (brand) and Asset Tag
    $search_or_clauses = [];
    $search_or_clauses[] = "a.asset_brand LIKE ?";
    $params[] = $like_search_term;
    $types .= "s";
    $search_or_clauses[] = "a.asset_tag LIKE ?";
    $params[] = $like_search_term;
    $types .= "s";
    // Combine the OR conditions
    $where_clauses[] = "(" . implode(" OR ", $search_or_clauses) . ")";
}

// Construct the final SQL
$sql = $sql_select . $sql_from_joins . $sql_where_base;
if (!empty($where_clauses)) {
    $sql .= " AND " . implode(" AND ", $where_clauses); // Add search conditions
}

// Add ORDER BY using validated parameters
$sql .= " ORDER BY " . $selected_sort_by . " " . $sort_order;

// Add LIMIT
$sql .= " LIMIT ?, ?";
$params[] = $start_from;
$params[] = $results_per_page;
$types .= "ii";

// Prepare and execute
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error preparing statement: " . $conn->error . " | SQL: " . $sql);
    die("Error preparing statement.");
}
if (!empty($types)) { // Bind only if types exist
    $stmt->bind_param($types, ...$params);
}
if (!$stmt->execute()) {
    error_log("Error executing statement: " . $stmt->error);
    $stmt_close_success = $stmt->close(); // Attempt to close before dying
    // $conn->close(); // Connection closed later
    die("Error executing statement.");
}
$result = $stmt->get_result();

// --- Generate Asset Cards ---
$asset_cards = "";
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $asset_id = $row["asset_id"];
        $asset_name = $row["asset_name"];
        $asset_tag = $row["asset_tag"];
        $site_location = $row["site_location"];
        $image_path = $row["image_path"];
        $asset_id_encoded = urlencode($asset_id);
        $details_url = "view_asset_details.php?asset_id=" . $asset_id_encoded;

        $asset_cards .= "<a href='" . htmlspecialchars($details_url) . "' class='asset-card-link'>";
        $asset_cards .= "<div class='asset-card'>";
        $asset_cards .= "<img src='" . htmlspecialchars($image_path ?: 'path/to/default/image.png') . "' alt='" . htmlspecialchars($asset_name) . "' class='asset-card-image'/>";
        
        $asset_cards .= "<div class='asset-card-content'>";
        
        // Left side: Asset Name
        $asset_cards .= "<div class='asset-info-left'>";
        $asset_cards .= "<span class='asset-name'>" . htmlspecialchars($asset_name) . "</span>";
        $asset_cards .= "</div>"; // End asset-info-left
        
        // Right side: Asset Tag and Site Location
        $asset_cards .= "<div class='asset-info-right'>";
        $asset_cards .= "<span class='asset-tag'>" . htmlspecialchars($asset_tag) . "</span>";
        $asset_cards .= "<span class='asset-location'>" . htmlspecialchars($site_location) . "</span>";
        $asset_cards .= "</div>"; // End asset-info-right
        
        $asset_cards .= "</div>"; // End asset-card-content
        $asset_cards .= "</div>"; // End asset-card
        $asset_cards .= "</a>";
    }
} else {
    $message = "No " . htmlspecialchars(isset($asset_type_name) ? $asset_type_name : "assets") . " found";
    if (isset($search_term) && $search_term !== '') {
        $message .= " matching your search.";
    } else {
        $message .= ".";
    }
    $asset_cards = "<p class='no-assets-message'>" . $message . "</p>";
}

$stmt->close(); // Close the main statement HERE

// --- Function to get total asset count ---
function getTotalAssetCount($conn, $asset_type_name, $search_term = '')
{
    // Re-use FROM/JOINs and base WHERE
    $sql_from_joins = " FROM assets a
                        JOIN asset_type at ON a.type_id = at.type_id
                        JOIN asset_status ast ON a.status_id = ast.status_id
                        JOIN owners o ON a.owner_id = o.owner_id
                        JOIN site_locations sl ON a.site_id = sl.site_id
                        JOIN departments d ON o.department_id = d.department_id ";
    $sql_where_base = " WHERE at.type_name = ? ";
    $sql_count_base = "SELECT COUNT(DISTINCT a.asset_id) AS total " . $sql_from_joins . $sql_where_base;

    $where_clauses_count = [];
    $params_count = [$asset_type_name];
    $types_count = "s";

    // Add search condition (Mirroring main query)
    if ($search_term !== '') {
        $like_search_term = '%' . $search_term . '%';
        $search_or_clauses = [];
        $search_or_clauses[] = "a.asset_brand LIKE ?"; // Search Name (brand)
        $params_count[] = $like_search_term;
        $types_count .= "s";
        $search_or_clauses[] = "a.asset_tag LIKE ?";   // Search Tag
        $params_count[] = $like_search_term;
        $types_count .= "s";
        $where_clauses_count[] = "(" . implode(" OR ", $search_or_clauses) . ")";
    }

    // Construct final count SQL
    $sql_count = $sql_count_base;
    if (!empty($where_clauses_count)) {
        $sql_count .= " AND " . implode(" AND ", $where_clauses_count);
    }

    $stmt_count = $conn->prepare($sql_count);
    if ($stmt_count === false) {
        error_log("Error preparing count statement: " . $conn->error . " | SQL: " . $sql_count);
        return 0;
    }
    if (!empty($types_count)) {
        $stmt_count->bind_param($types_count, ...$params_count);
    }
    if (!$stmt_count->execute()) {
        error_log("Error executing count statement: " . $stmt_count->error);
        $stmt_count->close();
        return 0;
    }
    $result_count = $stmt_count->get_result();
    $total = 0;
    if ($result_count) {
        $row_count = $result_count->fetch_assoc();
        $total = $row_count ? (int)$row_count['total'] : 0;
    }
    $stmt_count->close();
    return $total;
}

// --- Calculate Total Pages ---
$total_assets = getTotalAssetCount(
    $conn,
    $asset_type_name,
    $search_term
);
$total_pages = ($results_per_page > 0 && $total_assets > 0) ? ceil($total_assets / $results_per_page) : 0;

// --- Build Pagination URL ---
$base_url = $_SERVER['PHP_SELF'] . '?type=' . urlencode($asset_type_name); // Keep type
$params_pagination = $_GET; // Get current params
unset($params_pagination['page']); // Remove page for base URL
// filter_field is already gone, keep search_term, sort_by, sort_order
$query_string = http_build_query($params_pagination);
if (!empty($query_string)) {
    $base_url .= '&' . $query_string;
}

// --- Generate Pagination HTML ---
// Using the existing displayPagination function - assuming it's correct
function displayPagination($current_page, $total_pages, $base_url) {
   if ($total_pages <= 0) return '';
    $html = '<div class="pagination">';
    $separator = (strpos($base_url, '?') === false) ? '?' : '&'; // Simplified separator logic
    $page_url = function($page) use ($base_url, $separator) {
        return rtrim($base_url, '&') . $separator . 'page=' . $page;
    };
    // (Rest of pagination HTML generation remains the same)
    $html .= ($current_page > 1) ? '<a class="pagination-button" href="' . $page_url(1) . '"><<</a>' : '<span class="pagination-button disabled"><<</span>';
    $html .= ($current_page > 1) ? '<a class="pagination-button" href="' . $page_url($current_page - 1) . '"><</a>' : '<span class="pagination-button disabled"><</span>';
    $range = 2;
    $start_page = max(1, $current_page - $range);
    $end_page = min($total_pages, $current_page + $range);
    if ($start_page > 1) {
         $html .= '<a class="pagination-button" href="' . $page_url(1) . '">1</a>';
         if ($start_page > 2) $html .= '<span class="pagination-ellipsis">...</span>';
    }
    for ($i = $start_page; $i <= $end_page; $i++) {
        $html .= ($i == $current_page) ? '<span class="pagination-button current">' . $i . '</span>' : '<a class="pagination-button" href="' . $page_url($i) . '">' . $i . '</a>';
    }
     if ($end_page < $total_pages) {
         if ($end_page < $total_pages - 1) $html .= '<span class="pagination-ellipsis">...</span>';
         $html .= '<a class="pagination-button" href="' . $page_url($total_pages) . '">' . $total_pages . '</a>';
     }
    $html .= ($current_page < $total_pages) ? '<a class="pagination-button" href="' . $page_url($current_page + 1) . '">></a>' : '<span class="pagination-button disabled">></span>';
    $html .= ($current_page < $total_pages) ? '<a class="pagination-button" href="' . $page_url($total_pages) . '">>></a>' : '<span class="pagination-button disabled">>></span>';
    $html .= '</div>';
    return $html;
}

$paginationHtml = displayPagination($current_page, $total_pages, $base_url);

// Close the DB connection
$conn->close();
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assets - <?php echo htmlspecialchars($asset_type_name); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/functions.js"></script>
</head>
<body>
<aside class="sidebar">
            <div class="logo-container">
                <a href="dashboard.php" style="text-decoration: none;">
                    <img src="icons/safexpress_logo.png" alt="SafeXpress Logistics Logo" style="cursor: pointer;">
                </a>
            </div>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="assets.php" class="active">Assets</a></li>
                <li><a href="history.php">History</a></li>
                <li><a href="report_management.php">Export Reports</a></li>
                <li><a href="credits.php">Credits</a></li>
                <li><a href="#" id="logoutLink">Logout</a></li>
            </ul>
    </aside>
    <main class="content">
        <div class="main-header">
            <h1><?php echo htmlspecialchars($asset_type_name); ?></h1>
            <a href="assets.php" class="backButton">
            <span class="back-icon">↺</span> Back
            </a>
        </div>
        <div class="header-right">
            <form id="assetTypeSearchSortForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($asset_type_name ?? ''); ?>">
                <div class="search-container">
                    <i class="fas fa-search search-icon-fa"></i> <!-- Font Awesome Icon -->
                    <input type="text" name="search_term" id="asset_search_term" value="<?php echo htmlspecialchars($search_term ?? ''); ?>" placeholder="Search Name or Tag">
                </div>

                <select name="sort_by" id="asset_sort_by_select">
                    <?php
                    // Use the $allowed_sort_fields array defined in PHP
                    foreach ($allowed_sort_fields as $field_value => $field_label) {
                        // Use $selected_sort_by from PHP to mark the current selection
                        $selected_attr = ($selected_sort_by == $field_value) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($field_value) . '" ' . $selected_attr . '>Sort by ' . htmlspecialchars($field_label) . '</option>';
                    }?>
                </select>

                <input type="hidden" name="sort_order" id="asset_sort_order_hidden" value="<?php echo htmlspecialchars($sort_order); ?>">
                <button type="button" id="asset_sort_toggle_button" class="sort-button" title="Toggle Sort Order">
                    <span class="sort-icon <?php echo ($sort_order == 'DESC') ? 'sort-desc' : 'sort-asc'; ?>"></span>
                </button>
            </form>
        </div>
        <div class="cards-container">
            <?php echo $asset_cards; ?>
        </div>
        <div id="pagination-buttons">
            <?php echo $paginationHtml; ?>
        </div>
    </main>
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