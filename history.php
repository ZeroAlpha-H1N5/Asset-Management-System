<?php
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// --- Shared Configuration ---
$results_per_page = 10;
$sort_order_options = ['ASC', 'DESC'];

// --- Registered Assets ---
$registered_search_term = isset($_GET['registered_search_term']) ? trim($_GET['registered_search_term']) : '';
$registered_sort_order = isset($_GET['registered_sort_order']) && in_array(strtoupper($_GET['registered_sort_order']), $sort_order_options) ? strtoupper($_GET['registered_sort_order']) : 'DESC';
$registered_current_page = isset($_GET['registered_page']) && is_numeric($_GET['registered_page']) ? (int)$_GET['registered_page'] : 1;

// --- Turnovered Assets ---
$turnovered_search_term = isset($_GET['turnovered_search_term']) ? trim($_GET['turnovered_search_term']) : '';
$turnovered_sort_order = isset($_GET['turnovered_sort_order']) && in_array(strtoupper($_GET['turnovered_sort_order']), $sort_order_options) ? strtoupper($_GET['turnovered_sort_order']) : 'DESC';
$turnovered_current_page = isset($_GET['turnovered_page']) && is_numeric($_GET['turnovered_page']) ? (int)$_GET['turnovered_page'] : 1;

// --- Get the database connection
$conn = db_connect();


function generateAssetTable(
    string $tableType,
    string $search_term,
    string $sort_order,
    int $results_per_page,
    int $current_page,
    mysqli $conn
): string {
    $start_from = ($current_page - 1) * $results_per_page;

    $sql = "";
    $whereClauses = [];
    $params = [];
    $types = "";
    $defaultSearchColumns = [];

    if ($tableType === 'registered') {
        $sql = "SELECT
                a.asset_brand AS asset_name,
                at.type_name AS asset_type,
                o.owner_name AS owner_name,
                ast.status_name AS status_name,
                a.asset_register_date AS log_date,
                'Registered' AS activity,
                sl.site_name AS site_location,
                d.department_name AS department
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

        $defaultSearchColumns = [
            'a.asset_brand',
            'at.type_name',
            'o.owner_name',
            'ast.status_name'
        ];
    } elseif ($tableType === 'turnovered') {
        $sql = "SELECT
                a.asset_brand AS asset_name,
                at.type_name AS asset_type,
                po.owner_name AS owner_name,
                ast.status_name AS status_name,
                DATE(atl.turnover_date) AS log_date,
                sl.site_name AS previous_site,
                d.department_name AS department,
                'Turnover' AS activity
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

        $defaultSearchColumns = [
            'a.asset_brand',
            'at.type_name',
            'po.owner_name',
            'ast.status_name'
        ];
    } else {
        return "Error: Invalid table type.";
    }

    if ($search_term !== '') {
        $like_search_term = '%' . $search_term . '%';
        $search_or_clauses = [];
        foreach ($defaultSearchColumns as $col) {
            $search_or_clauses[] = $col . " LIKE ?";
            $params[] = $like_search_term;
            $types .= "s";
        }
        if (!empty($search_or_clauses)) {
            $whereClauses[] = "(" . implode(" OR ", $search_or_clauses) . ")";
        }
    }

    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(" AND ", $whereClauses); //Corrected WHERE to AND
    }

    $sortColumn = ($tableType === 'registered') ? 'a.asset_register_date' : 'atl.turnover_date';
    $sql .= " ORDER BY " . $sortColumn . " " . $sort_order . " LIMIT ?, ?";
    $params[] = $start_from;
    $params[] = $results_per_page;
    $types .= "ii";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error . " | SQL: " . $sql);
        return "Error preparing statement";
    }

    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Error executing statement: " . $stmt->error);
        return "Error executing statement";
    }

    $result = $stmt->get_result();


    $html = '<table class="asset-data-table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>' . ($tableType === 'registered' ? 'Register Date' : 'Last Turnover Date') . '</th>';
    $html .= '<th>Activity</th>';
    $html .= '<th>Asset Name</th>';
    $html .= '<th>Asset Type</th>';
    $html .= '<th>' . ($tableType === 'registered' ? 'Owner Name' : 'Previous Owner') . '</th>';
    $html .= '<th>Department</th>';
    $html .= '<th>Item Status</th>';
    $html .= '<th>' . ($tableType === 'registered' ? 'Site Location' : 'Previous Site') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['log_date']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['activity']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['asset_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['asset_type']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['owner_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['department']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['status_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars(($tableType === 'registered' ? $row['site_location'] : $row['previous_site'])) . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="8">No ' . ucfirst($tableType) . ' Assets Found.</td></tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    $result->free();
    $stmt->close();


    return $html;
}

function getTotalAssetCount(string $tableType, string $search_term, mysqli $conn): int {
    $sql = "";
    $whereClauses = [];
    $params = [];
    $types = "";
    $defaultSearchColumns = [];

    if ($tableType === 'registered') {
        $sql = "SELECT COUNT(DISTINCT a.asset_id) AS total
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
                WHERE NOT EXISTS (SELECT 1 FROM asset_turnover_log atl WHERE atl.asset_id = a.asset_id)"; //Added departments table
        $defaultSearchColumns = [
            'a.asset_brand',
            'at.type_name',
            'o.owner_name',
            'ast.status_name'
        ];
    } elseif ($tableType === 'turnovered') {
        $sql = "SELECT COUNT(*) AS total
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
        $defaultSearchColumns = [
            'a.asset_brand',
            'at.type_name',
            'po.owner_name',
            'ast.status_name'
        ];
    } else {
        return 0;
    }

    if ($search_term !== '') {
        $like_search_term = '%' . $search_term . '%';
        $search_or_clauses = [];
        foreach ($defaultSearchColumns as $col) {
            $search_or_clauses[] = $col . " LIKE ?";
            $params[] = $like_search_term;
            $types .= "s";
        }
        if (!empty($search_or_clauses)) {
            $whereClauses[] = "(" . implode(" OR ", $search_or_clauses) . ")";
        }
    }

    //Corrected AND WHERE to AND
    if (!empty($whereClauses)) {
        if (strpos($sql, 'WHERE') === false){
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        } else {
            $sql .= " AND " . implode(" AND ", $whereClauses);
        }

    }
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing count statement: " . $conn->error . " | SQL: " . $sql);
        return 0;
    }

    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Error executing count statement: " . $stmt->error);
        return 0;
    }

    $result = $stmt->get_result();

    $total = 0;
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total = (int)$row['total'];
    }

    $stmt->close();
    return $total;
}

function displayPagination(
    int $current_page,
    int $total_pages,
    string $base_url,
    string $page_param
): string {
    $html = '<div class="pagination">';

    $html .= ($current_page > 1) ?
        '<a class="pagination-button" href="' . $base_url . '&' . $page_param . '=1"><<</a>' .
        '<a class="pagination-button" href="' . $base_url . '&' . $page_param . '=' . ($current_page - 1) . '"><</a>' :
        '<span class="pagination-button disabled"><<</span><span class="pagination-button disabled"><</span>';

    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    for ($i = $start_page; $i <= $end_page; $i++) {
        $html .= ($i == $current_page) ?
            '<span class="pagination-button current">' . $i . '</span>' :
            '<a class="pagination-button" href="' . $base_url . '&' . $page_param . '=' . $i . '">' . $i . '</a>';
    }

    $html .= ($current_page < $total_pages) ?
        '<a class="pagination-button" href="' . $base_url . '&' . $page_param . '=' . ($current_page + 1) . '">></a>' .
        '<a class="pagination-button" href="' . $base_url . '&' . $page_param . '=' . $total_pages . '">>></a>' :
        '<span class="pagination-button disabled">></span><span class="pagination-button disabled">>></span>';

    $html .= '</div>';
    return $html;
}
// --- Registered Assets ---
$registered_total_assets = getTotalAssetCount('registered', $registered_search_term, $conn);
$registered_total_pages = ($results_per_page > 0) ? ceil($registered_total_assets / $results_per_page) : 0;
$registeredAssetTable = generateAssetTable(
    'registered',
    $registered_search_term,
    $registered_sort_order,
    $results_per_page,
    $registered_current_page,
    $conn
);
$registered_base_url = $_SERVER['PHP_SELF'] . '?' . http_build_query(array_diff_key($_GET, array_flip(['registered_page', 'turnovered_page'])));
$registeredPaginationHtml = displayPagination(
    $registered_current_page,
    $registered_total_pages,
    $registered_base_url,
    'registered_page'
);

// --- Turnovered Assets ---
$turnovered_total_assets = getTotalAssetCount('turnovered', $turnovered_search_term, $conn);
$turnovered_total_pages = ($results_per_page > 0) ? ceil($turnovered_total_assets / $results_per_page) : 0;
$turnoveredAssetTable = generateAssetTable(
    'turnovered',
    $turnovered_search_term,
    $turnovered_sort_order,
    $results_per_page,
    $turnovered_current_page,
    $conn
);
$turnovered_base_url = $_SERVER['PHP_SELF'] . '?' . http_build_query(array_diff_key($_GET, array_flip(['registered_page', 'turnovered_page'])));
$turnoveredPaginationHtml = displayPagination(
    $turnovered_current_page,
    $turnovered_total_pages,
    $turnovered_base_url,
    'turnovered_page'
);
$conn->close();
?>
<!DOCTYPE html>
<html>
<head> 
    <title>Logs</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/functions.js"></script>
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
                <li><a href="assets.php">Assets</a></li>
                <li><a href="history.php" class="active">Logs</a></li>
                <li><a href="report_management.php">Export Reports</a></li>
                <li><a href="credits.php">Credits</a></li>
                <li><a href="#" id="logoutLink">Logout</a></li>
            </ul>
        </aside>
    <main class="content">
        <h2>Asset Logs</h2>
        <div class="datetime-container">
            <span id="datetime"></span>
        </div>
        <button id="toggleSidebarButton">☰</button>
        <div class="asset-table">
            <div class="asset-table-header">
                <div class="header-left">
                    <h2>Registered Assets</h2>
                </div>
                <div class="header-right">
                    <form id="registeredFilterForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                        <input type="hidden" name="table" value="registered">

                        <div class="search-container">
                            <i class="fas fa-search search-icon-fa"></i> 
                            <input type="text" name="registered_search_term" id="registered_search_term" value="<?php echo htmlspecialchars($registered_search_term ?? ''); ?>" placeholder="Find in Registered">
                        </div>

                        <input type="hidden" name="registered_sort_order" id="registered_sort_order_hidden" value="<?php echo ($registered_sort_order == 'DESC') ? 'DESC' : 'ASC'; ?>">

                        <!-- Sort Toggle Button -->
                        <button type="button" id="registered_sort_toggle_button" class="sort-button" title="Toggle Sort Order">
                            <span class="sort-icon <?php echo ($registered_sort_order == 'DESC') ? 'sort-desc' : 'sort-asc'; ?>"></span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <?php echo $registeredAssetTable; ?>
            </div>
                            
            <div id="pagination-buttons">
                <?php echo $registeredPaginationHtml; ?>
            </div>
        </div>        
        <div class="asset-table">
            <div class="asset-table-header">
                <div class="header-left">
                    <h2>Turnovered Assets</h2>
                </div>
                <div class="header-right">
                    <form id="turnoveredFilterForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                        <input type="hidden" name="table" value="turnovered">

                        <div class="search-container">
                            <i class="fas fa-search search-icon-fa"></i> <!-- Requires Font Awesome -->
                            <input type="text" name="turnovered_search_term" id="turnovered_search_term" value="<?php echo htmlspecialchars($turnovered_search_term ?? ''); ?>" placeholder="Find in Turnovered">
                        </div>

                        <input type="hidden" name="turnovered_sort_order" id="turnovered_sort_order_hidden" value="<?php echo (($turnovered_sort_order ?? 'ASC') == 'DESC') ? 'DESC' : 'ASC'; ?>">

                        <button type="button" id="turnovered_sort_toggle_button" class="sort-button" title="Toggle Sort Order">
                            <span class="sort-icon <?php echo (($turnovered_sort_order ?? 'ASC') == 'DESC') ? 'sort-desc' : 'sort-asc'; ?>"></span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <?php echo $turnoveredAssetTable; ?>
            </div>
            
            <div id="pagination-buttons">
                <?php echo $turnoveredPaginationHtml; ?>
            </div>
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