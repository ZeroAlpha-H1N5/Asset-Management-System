<?php
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Export Reports</title>
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
                <li><a href="history.php">Logs</a></li>
                <li><a href="report_management.php" class="active">Export Reports</a></li>
                <li><a href="credits.php">Credits</a></li>
                <li><a href="#" id="logoutLink">Logout</a></li>
            </ul>
        </aside>
    <main class="content">
        <h2>Export Report Data</h2>
        <div class="datetime-container">
            <span id="datetime"></span>
        </div>
        <button id="toggleSidebarButton">☰</button>
        <?php if (isset($_GET['export_message'])): ?>
            <p class="export-message">
                <?php
                if (strpos($_GET['export_message'], '(Assets)') !== false) {
                    echo htmlspecialchars($_GET['export_message']); // Assets Table Message
                } elseif (strpos($_GET['export_message'], '(Registered Assets)') !== false) {
                    echo htmlspecialchars($_GET['export_message']); // Registered Assets Message
                } elseif (strpos($_GET['export_message'], '(Turnovered Assets)') !== false) {
                    echo htmlspecialchars($_GET['export_message']); // Turnovered Assets Message
                } else {
                    echo htmlspecialchars($_GET['export_message']); // Generic Message
                }
                ?>
            </p>
        <?php endif; ?>
        
        <!-- ====== EXPORT ASSETS DATA ====== -->
        <div class="export-form-groups">
            <form method="post" action="export_assets.php">
            <fieldset> <legend>Assets Data</legend>
                <div class="filter-group-container"> 
                    <div class="filters-row"> 
                        <div class="form-group main-filter-item">
                            <div class="custom-select-wrapper">
                                <span class="select-label">Filter By:</span>
                                <select name="export_filter" id="export_filter">
                                    <option value="">-- No Filter --</option>
                                    <option value="asset_type">Asset Type</option>
                                    <option value="asset_status">Asset Status</option>
                                    <option value="asset_brand">Asset Brand</option>
                                    <option value="department">Department</option>
                                    <option value="site_location">Site Location</option>
                                </select>
                            </div>
                        </div>

                        <div id="export_asset_type" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_asset_type.php'; echo $assetTypeDropdownCustom; ?> 

                        </div>

                        <div id="export_department" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_departments.php'; echo $departmentDropdownCustom; ?>
                        </div>

                        <div id="export_site_location" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_site_locations.php'; echo $siteLocationDropdownCustom; ?>
                        </div>

                        <div id="export_asset_status" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_asset_status.php'; echo $assetStatusDropdownCustom; ?>
                        </div>

                        <div id="export_asset_brand" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <div class="custom-select-wrapper">
                                <span class="select-label">Sort Asset Brand:</span>
                                <select name="export_asset_brand" id="export_asset_brand"> {/* ID remains the same */}
                                    <option value="ASC">Ascending</option>
                                    <option value="DESC">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group button-container-group">
                        <button type="submit" name="export_csv">Export to CSV</button>
                    </div>
                </div>
                    <?php if (isset($message)): ?>
                        <p class="export-message"><?php echo $message; ?></p>
                    <?php endif; ?>
            </fieldset>
            </form>

            <!-- ====== EXPORT TURNOVERED ====== -->
            <form method="post" action="export_logs.php">
            <fieldset> <legend>Turnovered Assets</legend>
                <div class="filter-group-container"> 
                    <div class="filters-row"> 
                        <div class="form-group main-filter-item">
                            <div class="custom-select-wrapper">
                                <span class="select-label">Filter By:</span>
                                <select name="export_filter_turnovered" id="export_filter_turnovered">
                                    <option value="">-- No Filter --</option>
                                    <option value="asset_type">Asset Type</option>
                                    <option value="asset_status">Asset Status</option>
                                    <option value="asset_brand">Asset Brand</option>
                                    <option value="department">Department</option>
                                    <option value="site_location">Site Location</option>
                                </select>
                            </div>
                        </div>

                        <div id="asset_type_turnovered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_asset_type.php'; echo $assetTypeDropdownCustom;?>
                        </div>

                        <div id="department_turnovered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_departments.php'; echo $departmentDropdownCustom; ?>
                        </div>

                        <div id="site_location_turnovered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_site_locations.php'; echo $siteLocationDropdownCustom; ?>
                        </div>

                        <div id="asset_status_turnovered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_asset_status.php'; echo $assetStatusDropdownCustom; ?>
                        </div>

                        <div id="asset_brand_turnovered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <div class="custom-select-wrapper">
                                <span class="select-label">Sort Asset Brand:</span>
                                <select name="export_asset_brand_turnovered" id="export_asset_brand_turnovered">
                                    <option value="ASC">Ascending</option>
                                    <option value="DESC">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group button-container-group">
                        <button type="submit" name="export_csv_turnovered">Export to CSV</button>
                    </div>
                </div>        
            </fieldset>
            </form>

            <!-- ====== EXPORT REGISTERED ====== -->
            <form method="post" action="export_logs.php">
            <fieldset> <legend>Registered Assets</legend>
                <div class="filter-group-container"> 
                    <div class="filters-row"> 
                        <div class="form-group main-filter-item">
                            <div class="custom-select-wrapper">
                                <span class="select-label">Filter By:</span>
                                <select name="export_filter_registered" id="export_filter_registered">
                                    <option value="">-- No Filter --</option>
                                    <option value="asset_type">Asset Type</option>
                                    <option value="asset_status">Asset Status</option>
                                    <option value="asset_brand">Asset Brand</option>
                                    <option value="department">Department</option>
                                    <option value="site_location">Site Location</option>
                                </select>
                            </div>
                        </div>

                        <div id="asset_type_registered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_asset_type.php'; echo $assetTypeDropdownCustom; ?>
                        </div>

                        <div id="department_registered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_departments.php'; echo $departmentDropdownCustom; ?>
                        </div>

                        <div id="site_location_registered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_site_locations.php'; echo $siteLocationDropdownCustom; ?>
                        </div>

                        <div id="asset_status_registered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <?php $prefix = 'export_'; include 'get_asset_status.php'; echo $assetStatusDropdownCustom; ?>
                        </div>

                        <div id="asset_brand_registered" style="display: none;" class="form-group conditional-export-field conditional-filter-item">
                            <div class="custom-select-wrapper">
                                <span class="select-label">Sort Asset Brand:</span>
                                <select name="asset_brand_registered" id="asset_brand_registered"> 
                                    <option value="ASC">Ascending</option>
                                    <option value="DESC">Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group button-container-group">
                        <button type="submit" name="export_csv_registered">Export to CSV</button>
                    </div>
                </div>
            </fieldset>
            </form>
        </div>
    </main>
</body>
</html>