<?php
require_once 'functions.php';
if (!is_logged_in()) {header("Location: login.php");exit;}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Asset Status Fetch
$asset_statuses = [];
$conn = db_connect();
if ($conn) {
    $sql = "SELECT status_id, status_name FROM asset_status";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {$asset_statuses[] = $row;}
    } $conn->close();
} else {
    error_log("Database connection failed in settings.php");
    echo "<p style='color: red;'>Failed to load asset statuses due to a database error.</p>";
}
// Departments Fetch
$departments = [];
$conn = db_connect();
if ($conn) {
    $sql = "SELECT department_id, department_name FROM departments";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {$departments[] = $row;}
    } $conn->close();
} else {
    error_log("Database connection failed in settings.php when fetching department data.");
    echo "<p style='color: red;'>Failed to load department data due to a database error.</p>";
}
// Asset Type Fetch
$asset_types = [];
$conn = db_connect();
if ($conn) {
    $sql = "SELECT type_id, type_name, type_code FROM asset_type";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {$asset_types[] = $row;}
    } $conn->close();
} else {
    error_log("Database connection failed in settings.php when fetching asset type data.");
    echo "<p style='color: red;'>Failed to load asset type data due to a database error.</p>";
}
// Site Location Fetch
$site_locations = [];
$conn = db_connect();
if ($conn) {
    $sql = "SELECT site_id, site_name, site_region FROM site_locations";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $site_locations[] = $row;
        }
    }
    $conn->close();
} else {
    error_log("Database connection failed in settings.php when fetching site location data.");
    echo "<p style='color: red;'>Failed to load site location data due to a database error.</p>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/functions.js"></script>
    <script src="js/settings.js"></script>
    <style>
  .card-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
    margin-top: 20px;
  }

  .card {
    width: 180px;
    height: 180px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 10px;
    text-align: center;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
    transition: 0.3s;
  }

  .card:hover {
    box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
  }

  .card img {
    max-width: 60px;
    max-height: 60px;
    margin-bottom: 10px;
  }

   /* Style for the form container */
  #assetStatusFormContainer, #departmentFormContainer, #assetTypeFormContainer, #siteLocationFormContainer {
    display: none; /* Initially hidden */
    margin-top: 20px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

    /* Edit Table Styling */
    #editAssetStatusTable, #editDepartmentTable, #editAssetTypeTable, #editSiteLocationTable {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    #editAssetStatusTable th, #editAssetStatusTable td, #editDepartmentTable th, #editDepartmentTable td, #editAssetTypeTable th, #editAssetTypeTable td, #editSiteLocationTable th, #editSiteLocationTable td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    #editAssetStatusTable th, #editDepartmentTable th, #editAssetTypeTable th, #editSiteLocationTable th {
        background-color: #f2f2f2;
    }

    #editAssetStatusTable input[type="text"], #editDepartmentTable input[type="text"], #editAssetTypeTable input[type="text"], #editSiteLocationTable input[type="text"] {
        width: 100%;
        padding: 5px;
        box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
    /* Delete Table Styling */
    #deleteAssetStatusTable, #deleteDepartmentTable, #deleteAssetTypeTable, #deleteSiteLocationTable {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    #deleteAssetStatusTable th, #deleteAssetStatusTable td, #deleteDepartmentTable th, #deleteDepartmentTable td, #deleteAssetTypeTable th, #deleteAssetTypeTable td, #deleteSiteLocationTable th, #deleteSiteLocationTable td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    #deleteAssetStatusTable th, #deleteDepartmentTable th, #deleteAssetTypeTable th, #deleteSiteLocationTable th {
        background-color: #f2f2f2;
    }

    #deleteAssetStatusTable input[type="text"], #deleteDepartmentTable input[type="text"], #deleteAssetTypeTable input[type="text"], #deleteSiteLocationTable input[type="text"] {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
    }

    /* Add Site Location Form Styling */
    #addSiteLocationForm label {
        display: block;
        margin-bottom: 5px;
    }

    #addSiteLocationForm input[type="text"] {
        width: 100%;
        padding: 5px;
        margin-bottom: 10px;
        box-sizing: border-box;
    }
</style>
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
    <h1>Manage Data</h1>
    <div class="datetime-container">
            <span id="datetime"></span>
    </div>
    <a href="assets.php" class="backButton">
        <span class="back-icon">↺</span> Back
    </a>
    <div class="card-container">
        <div class="card" data-card-type="asset_type">
            <img src="asset_type_icon.png" alt="Asset Type">
            <h3>Asset Type</h3>
        </div>
        <div class="card" data-card-type="asset_status">
            <img src="asset_status_icon.png" alt="Asset Status">
            <h3>Asset Status</h3>
        </div>
        <div class="card" data-card-type="departments">
            <img src="departments_icon.png" alt="Departments">
            <h3>Departments</h3>
        </div>
        <div class="card" data-card-type="site_locations">
            <img src="site_locations_icon.png" alt="Site Locations">
            <h3>Site Locations</h3>
        </div>
    </div>
    <div id="actionModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <div id="actionButtons">
                <h2>Choose Action</h2>
                <button class="action-button" data-action="add">Add</button>
                <button class="action-button" data-action="edit">Edit</button>
                <button class="action-button" data-action="delete">Delete</button>
            </div>
            <div id="assetStatusFormContainer">
              <h3>Add New Asset Status</h3>
                <form id="addAssetStatusForm" action="process_asset_status_data.php" method="POST">
                    <input type="text" id="new_status_name" name="new_status_name"><br><br>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <div id="editAssetStatusFormContainer">
                 <h3>Which entry would you like to edit?</h3>
                <form id="editAssetStatusForm" method="POST" action="process_asset_status_data.php">
                    <table id="editAssetStatusTable">
                        <thead><tr><th></th><th>Status</th></tr></thead>
                        <tbody><?php foreach ($asset_statuses as $status): ?>
                            <tr>
                                <td><input type="radio" name="selected_status_id" value="<?php echo $status['status_id']; ?>"></td>
                                <td><input type="text" name="status_name[<?php echo $status['status_id']; ?>]" value="<?php echo htmlspecialchars($status['status_name']); ?>"></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="saveChangesButton">Save Changes</button>
                </form>
            </div>
            <div id="deleteAssetStatusFormContainer">
                 <h3>Which entry would you like to delete?</h3>
                <form id="deleteAssetStatusForm" method="POST" action="process_asset_status_data.php"">
                    <table id="deleteAssetStatusTable">
                        <thead><tr><th></th><th>Status</th></tr></thead>
                        <tbody><?php foreach ($asset_statuses as $status): ?>
                            <tr>
                                <td><input type="radio" name="selected_status_id" value="<?php echo $status['status_id']; ?>"></td>
                                <td><?php echo htmlspecialchars($status['status_name']); ?></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="deleteButton">Delete</button>
                </form>
            </div>
            <div id="departmentFormContainer">
                <form id="addDepartmentForm" action="process_departments_data.php" method="POST">
                  <h3>Add New Department</h3>
                    <input type="text" id="new_department_name" name="new_department_name"><br><br>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <div id="editDepartmentFormContainer">
                <h3>Which department would you like to edit?</h3>
                <form id="editDepartmentForm" method="POST" action="process_departments_data.php">
                    <table id="editDepartmentTable">
                        <thead><tr><th></th><th>Department</th></tr></thead>
                        <tbody><?php foreach ($departments as $department): ?>
                            <tr>
                                <td><input type="radio" name="selected_department_id" value="<?php echo $department['department_id']; ?>"></td>
                                <td><input type="text" name="department_name[<?php echo $department['department_id']; ?>]" value="<?php echo htmlspecialchars($department['department_name']); ?>"></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="saveDepartmentChangesButton">Save Changes</button>
                </form>
            </div>
            <div id="deleteDepartmentFormContainer">
                <h3>Which department would you like to delete?</h3>
                <form id="deleteDepartmentForm" method="POST" action="process_departments_data.php">
                    <table id="deleteDepartmentTable">
                        <thead><tr><th></th><th>Department</th></tr></thead>
                        <tbody><?php foreach ($departments as $department): ?>
                            <tr>
                                <td><input type="radio" name="selected_department_id" value="<?php echo $department['department_id']; ?>"></td>
                                <td><?php echo htmlspecialchars($department['department_name']); ?></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="deleteDepartmentButton">Delete</button>
                </form>
            </div>
            <div id="assetTypeFormContainer">
                <form id="addAssetTypeForm" action="process_asset_type_data.php" method="POST">
                  <h3>Add New Asset Type</h3>
                    <label for="new_type_name">Insert new type name:</label>
                    <input type="text" id="new_type_name" name="new_type_name"><br><br>
                    <label for="new_type_code">Insert new type code:</label>
                    <input type="text" id="new_type_code" name="new_type_code"><br><br>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <div id="editAssetTypeFormContainer">
                <h3>Which asset type would you like to edit?</h3>
                <form id="editAssetTypeForm" method="POST" action="process_asset_type_data.php">
                    <table id="editAssetTypeTable">
                        <thead><tr>
                            <th></th>
                            <th>Type Name</th>
                            <th>Type Code</th>
                        </tr></thead>
                        <tbody><?php foreach ($asset_types as $type): ?>
                            <tr>
                                <td><input type="radio" name="selected_type_id" value="<?php echo $type['type_id']; ?>"></td>
                                <td><input type="text" name="type_name[<?php echo $type['type_id']; ?>]" value="<?php echo htmlspecialchars($type['type_name']); ?>"></td>
                                <td><input type="text" name="type_code[<?php echo $type['type_id']; ?>]" value="<?php echo htmlspecialchars($type['type_code']); ?>"></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="saveAssetTypeChangesButton">Save Changes</button>
                </form>
            </div>
            <div id="deleteAssetTypeFormContainer">
                <h3>Which asset type would you like to delete?</h3>
                <form id="deleteAssetTypeForm" method="POST" action="process_asset_type_data.php">
                    <table id="deleteAssetTypeTable">
                        <thead><tr>
                            <th></th>
                            <th>Type Name</th>
                            <th>Type Code</th>
                        </tr></thead>
                        <tbody><?php foreach ($asset_types as $type): ?>
                            <tr>
                                <td><input type="radio" name="selected_type_id" value="<?php echo $type['type_id']; ?>"></td>
                                <td><?php echo htmlspecialchars($type['type_name']); ?></td>
                                <td><?php echo htmlspecialchars($type['type_code']); ?></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="deleteAssetTypeButton">Delete</button>
                </form>
            </div>
            <div id="siteLocationFormContainer">
                <form id="addSiteLocationForm" action="process_site_locations_data.php" method="POST">
                  <h3>Add New Site</h3>
                    <label for="new_site_name">Insert new site name:</label>
                    <input type="text" id="new_site_name" name="new_site_name"><br><br>
                    <label for="new_site_region">Insert new site region:</label>
                    <input type="text" id="new_site_region" name="new_site_region"><br><br>
                    <button type="submit">Submit</button>
                </form>
            </div>
            <div id="editSiteLocationFormContainer">
                <h3>Which site location would you like to edit?</h3>
                <form id="editSiteLocationForm" method="POST" action="process_site_locations_data.php">
                    <table id="editSiteLocationTable">
                        <thead><tr>
                            <th></th>
                            <th>Site Name</th>
                            <th>Site Region</th>
                        </tr></thead>
                        <tbody><?php foreach ($site_locations as $location): ?>
                            <tr>
                                <td><input type="radio" name="selected_site_id" value="<?php echo $location['site_id']; ?>"></td>
                                <td><input type="text" name="site_name[<?php echo $location['site_id']; ?>]" value="<?php echo htmlspecialchars($location['site_name']); ?>"></td>
                                <td><input type="text" name="site_region[<?php echo $location['site_id']; ?>]" value="<?php echo htmlspecialchars($location['site_region']); ?>"></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="saveSiteLocationChangesButton">Save Changes</button>
                </form>
            </div>
            <div id="deleteSiteLocationFormContainer">
                <h3>Which site location would you like to delete?</h3>
                <form id="deleteSiteLocationForm" method="POST" action="process_site_locations_data.php">
                    <table id="deleteSiteLocationTable">
                        <thead><tr>
                            <th></th>
                            <th>Site Name</th>
                            <th>Site Region</th>
                        </tr></thead>
                        <tbody><?php foreach ($site_locations as $location): ?>
                            <tr>
                                <td><input type="radio" name="selected_site_id" value="<?php echo $location['site_id']; ?>"></td>
                                <td><?php echo htmlspecialchars($location['site_name']); ?></td>
                                <td><?php echo htmlspecialchars($location['site_region']); ?></td>
                            </tr>
                        <?php endforeach; ?></tbody>
                    </table>
                    <button type="submit" id="deleteSiteLocationButton">Delete</button>
                </form>
            </div>
        </div>
    </div>
    </main>
</body>
</html>