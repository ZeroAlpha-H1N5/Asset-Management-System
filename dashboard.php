<?php
require_once 'functions.php';
$conn = db_connect();

if (!is_logged_in()) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Access user information from the session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$statuses = [];
$locations = [];
$departments = [];

// Fetch Statuses using the 'asset_status' table
$statusTable = 'asset_status';
$statusNameCol = 'status_name';

$status_sql = "SELECT DISTINCT {$statusNameCol} FROM {$statusTable} ORDER BY {$statusNameCol} ASC";
$status_result = $conn->query($status_sql);
if ($status_result && $status_result->num_rows > 0) {
    while($row = $status_result->fetch_assoc()) {
        $statuses[] = $row[$statusNameCol];
    }
}

// Fetch Locations using the 'site_locations' table
$location_sql = "SELECT site_id, site_name FROM site_locations ORDER BY site_name ASC";
$location_result = $conn->query($location_sql);
if ($location_result && $location_result->num_rows > 0) {
    while($row = $location_result->fetch_assoc()) {
        $locations[] = $row;
    }
}

// Fetch Departments using the 'departments' table
$department_sql = "SELECT department_id, department_name FROM departments ORDER BY department_name ASC";
$department_result = $conn->query($department_sql);
if ($department_result && $department_result->num_rows > 0) {
    while($row = $department_result->fetch_assoc()) {
        $departments[] = $row;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome - Safexpress Logistics Inc.</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="js/functions.js"></script>
    <script src="js/dashboard.js"></script>
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="assets.php">Assets</a></li>
                <li><a href="history.php">Logs</a></li>
                <li><a href="report_management.php">Export Reports</a></li>
                <li><a href="credits.php">Credits</a></li>
                <li><a href="#" id="logoutLink">Logout</a></li>
            </ul>
        </aside>
    <main class="content">
        <h2>Dashboard</h2>
        <div class="datetime-container">
            <span id="datetime"></span>
        </div>
        <button id="toggleSidebarButton">☰</button>
        <div class="graphs-container">
            <div class="asset-category-container">
                <h3>Asset by Category</h3>
                <div class="filters-bar">
                    <div class="custom-select-wrapper">
                        <label for="statusFilter" class="select-label">Filter by Status:</label>
                        <select id="statusFilter" name="status">
                            <option value="all">All Statuses</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="custom-select-wrapper">
                        <label for="locationFilter" class="select-label">Filter by Location:</label>
                        <select id="locationFilter" name="location">
                            <option value="all">All Locations</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location['site_id']); ?>">
                                    <?php echo htmlspecialchars($location['site_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="custom-select-wrapper">
                        <label for="departmentFilter" class="select-label">Filter by Department:</label>
                        <select id="departmentFilter" name="department">
                            <option value="all">All Departments</option>
                             <?php foreach ($departments as $department): ?>
                                <option value="<?php echo htmlspecialchars($department['department_id']); ?>">
                                    <?php echo htmlspecialchars($department['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="graph-rectangle">
                    <canvas id="assetChart" width="400" height="500"></canvas>
                </div>
            </div>

            <div class="asset-status-container">
                <h3>Asset by Status</h3>
                <div class="graph-square">
                    <canvas id="statusDonutChart" width="300" height="200"></canvas>
                </div>
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