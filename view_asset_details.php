<?php
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Access user information from the session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

if (!isset($_GET['asset_id']) || !is_numeric($_GET['asset_id'])) {
    echo "Error: Invalid asset ID.";
    exit;
}

$asset_id = (int)$_GET['asset_id'];

$conn = db_connect();

$sql = "SELECT
                a.asset_id AS assetID,
                a.asset_brand AS assetName,
                a.asset_model AS assetModel,
                a.asset_serial_num AS serialNum,
                at.type_id AS typeID,
                at.type_name AS assetType,
                a.asset_tag AS assetTag,
                ast.status_id AS statusID,
                ast.status_name AS statusName,
                DATE(a.asset_purchase_date) AS datePurchased,
                DATE(a.asset_register_date) AS dateRegistered,
                a.asset_depreciation_period AS deprecPeriod,
                d.department_id AS departmentID,
                d.department_name AS departmentName,
                o.owner_position AS ownerPosition,
                o.owner_phone_num AS phoneNum,
                a.asset_purchase_cost AS assetCost,
                a.asset_depreciated_cost AS deprecCost,
                a.image_path AS imagePath,
                o.owner_name AS ownerName,
                sl.site_id AS siteID,
                sl.site_name AS siteName,
                sl.site_region AS siteRegion,
                atl.turnover_reason AS turnoverReason,
                atl.turnover_details AS turnoverDetails
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
            LEFT JOIN
                asset_turnover_log atl ON a.asset_id = atl.asset_id
            WHERE a.asset_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error preparing statement: " . $conn->error);
    echo "Error: Could not prepare statement.";
    exit;
}

$stmt->bind_param("i", $asset_id);

if (!$stmt->execute()) {
    error_log("Error executing statement: " . $stmt->error);
    echo "Error: Could not execute statement.";
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Error: Asset not found.";
    exit;
}

$asset = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Asset Details - <?php echo htmlspecialchars($asset['assetTag'] ?? 'N/A'); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/functions.js"></script>
    <script src="js/view_details_buttons.js"></script>
</head>
<body class="asset-details-contents">
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
    <main class="content view-details-page">
        <button id="toggleSidebarButton">☰</button>
        
        <!-- Header and Action Buttons -->
        <div class="details-header">
            <a href="assets.php?type=<?php echo urlencode($asset['assetType']); ?>" class="backButton">
                <span class="btn-icon"><i class="fas fa-arrow-left"></i></span> Back
            </a>
            <div class="action-buttons">
                 <button id="turnoverAssetButton" class="btn btn-action btn-turnover" data-asset-id="<?php echo htmlspecialchars($asset['assetID']); ?>">
                     <span class="btn-icon"><i class="fas fa-pencil-alt"></i></span> Turnover
                 </button>
                 <button type="button" id="editAssetButton" class="btn btn-action btn-edit" data-asset-id="<?php echo htmlspecialchars($asset['assetID']); ?>">
                     <span class="btn-icon"><i class="fas fa-edit"></i></span> Edit
                 </button>
                 <button type="button" id="changeImageButton" class="btn btn-action btn-image" data-asset-id="<?php echo htmlspecialchars($asset['assetID']); ?>">
                    <span class="btn-icon"><i class="fas fa-image"></i></span> Change Image
                </button>
                 <button type="button" id="deleteAssetButton" class="btn btn-action btn-delete" data-asset-id="<?php echo htmlspecialchars($asset['assetID']); ?>">
                     <span class="btn-icon"><i class="fas fa-trash-alt"></i></span> Delete
                 </button>
            </div>
        </div>

        <div class="details-layout-grid">
            <!-- Left Column -->
            <div class="asset-info-column">
                <section class="details-section">
                    <h2>Asset details</h2>
                    <div class="detail-list">
                        <div class="detail-item">
                            <span class="detail-label">Asset Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['assetName']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Asset Type</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['assetType']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Asset Model</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['assetModel']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Asset Tag</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['assetTag']); ?></span>
                            <input type="hidden" id="AssetID" name="assetID" value="<?php echo htmlspecialchars($asset['assetID']); ?>" readonly> 
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Serial Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['serialNum']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Depreciation Period</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['deprecPeriod']); ?> years</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Purchase Cost</span>
                            <span class="detail-value"><?php echo '₱ ' . number_format(htmlspecialchars($asset['assetCost']), 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Depreciated Cost</span>
                            <span class="detail-value"><?php echo '₱ ' . number_format(htmlspecialchars($asset['deprecCost']), 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date Purchased</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['datePurchased'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date Registered</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['dateRegistered']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Site Location</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['siteName']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Region</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['siteRegion']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value <?php echo getStatusClass($asset['statusName']) ?? 'N/A'; ?>">
                                <?php echo htmlspecialchars($asset['statusName']); ?>
                            </span>
                        </div>
                        <!--
                        <h2>Turnover Details</h2>
                        <div class="detail-item">
                            <span class="detail-label">Reason</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['turnoverReason']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Additional Details</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['turnoverDetails']); ?></span>
                        </div>
                        -->
                        <button id="showPdfButton" class="button">Show Asset Tag</button>
                    </div>
                </section>
            </div>

            <!-- Right Column -->
            <div class="asset-media-owner-column">
                <div class="asset-image-container">
                    <?php
                    $image_path = !empty($asset['imagePath']) ? $asset['imagePath'] : '/SLI_ASSET/assets/default.jpg';
                    ?>
                    <img class="asset-image" id="assetImage" src="<?php echo htmlspecialchars($image_path); ?>" alt="Asset Image for <?php echo htmlspecialchars($asset['assetTag']); ?>">
                </div>

                <section class="details-section">
                    <h2>Custodian details</h2>
                    <div class="detail-list">
                        <div class="detail-item">
                            <span class="detail-label">Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['ownerName']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Position</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['ownerPosition']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Department</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['departmentName']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($asset['phoneNum']); ?></span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
    <!-- Turnover Modal Section-->
    <div id="turnoverModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2 id="turnoverModalTitle">Turnover For: <?php echo ($asset['assetTag']); ?></h2>
            <form id="turnoverForm">
            <div>
                <label for="turnoverTypeRadio">Select Turnover Type:</label> 
                <input type="radio" id="turnoverTypeCustodian" name="turnoverType" value="custodian" checked>
                <label for="turnoverTypeCustodian">To Custodian</label><br>

                <input type="radio" id="turnoverTypeCompany" name="turnoverType" value="company">
                <label for="turnoverTypeCompany">To Company</label><br>
            </div>

            <div class="owner-change">
                <div>
                    <label for="ownerFrom">Current Custodian:</label>
                    <input type="text" id="ownerFrom" name="ownerFrom" value="<?php echo ($asset['ownerName']); ?>" readonly>
                </div>
                
                <div>
                    <label for="ownerTo">New Custodian:</label>
                    <input type="text" id="ownerTo" name="ownerTo" placeholder="New Owner" required>
                </div>
            </div>

            <div class="custodianFields">
                <div>
                    <?php include "get_departments.php"; echo $departmentDropdown; ?>
                </div>
                    
                <div>
                    <label for="ownerPosition">Position:</label>
                    <input type="text" id="ownerPosition" name="ownerPosition" placeholder="New Position"> 
                </div>
                
                <div>
                    <label for="ownerDateHired">Date Hired:</label>
                    <input type="date" id="ownerDateHired" name="ownerDateHired">
                </div>

                <div>
                    <label for="ownerPhoneNum">Phone Number:</label>
                    <input type="number" id="ownerPhoneNum" name="ownerPhoneNum">
                </div>
            </div>

            <div>
                <!-- Site Locations Dropdown List -->
                <?php include "get_site_locations.php"; echo $siteLocationDropdown; ?> 
            </div>

            <div>
                <label for="turnoverDate">Turnover Date:</label>
                <input type="date" id="turnoverDate" name="turnoverDate" required>
            </div>

            <div>
                <label for="turnoverReason">Turnover Reason:</label>
                <input type="text" id="turnoverReason" name="turnoverReason"> 
            </div>

            <div>
                <label for="turnoverDetails">Details:</label>
                <textarea id="turnoverDetails" name="turnoverDetails" rows="4"></textarea> 
            </div>

                <input type="hidden" id="turnoverAssetID" name="assetID" value="<?php echo htmlspecialchars($asset['assetID']); ?>" readonly>

            <button type="submit">Confirm</button>
            </form>
        </div>
    </div>
    <!-- Edit Modal Section-->
    <div id="editModal" class="modal">
        <div class="modal-content">
                <span class="close">×</span>
                <h2 id="editModalTitle">Edit Asset Details -</h2>
                <form id="editForm">
                    <div>
                        <label for="editAssetName">Asset Name:</label>
                        <input type="text" id="editAssetName" name="assetName">
                    </div>

                    <div>
                        <label for="editAssetModel">Asset Model:</label>
                        <input type="text" id="editAssetModel" name="assetModel">
                    </div>

                    <div>
                        <label for="editAssetSerial">Serial Number:</label>
                        <input type="text" id="editAssetSerial" name="assetSerial">
                    </div>

                    <div>
                        <!-- Type Dropdown List -->
                        <?php include "edit_asset_type.php"; echo $assetTypeDropdownEdit; ?>
                    </div>

                    <div>
                        <!-- Status Dropdown List -->
                        <?php include "edit_asset_status.php"; echo $assetStatusDropdownEdit; ?>
                    </div>

                    <div>
                        <label for="editDeprecPeriod">Depreciation Period:</label>
                        <input type="text" id="editDeprecPeriod" name="deprecPeriod">
                    </div>

                    <div>
                        <label for="editAssetCost">Purchase Cost:</label>
                        <input type="text" id="editAssetCost" name="assetCost">
                    </div>

                    <div>
                        <label for="editDeprecCost">Depreciation Cost:</label>
                        <input type="text" id="editDeprecCost" name="deprecCost" readonly>
                    </div>

                    <div>
                        <label for="editAssetPurchased">Date Purchased (Optional):</label>
                        <input type="date" id="editDatePurchased" name="datePurchased">
                    </div>

                    <div>
                        <label for="editAssetRegistered">Date Registered:</label>
                        <input type="date" id="editDateRegistered" name="dateRegistered">
                    </div>

                    <div>
                        <!-- Site Locations Dropdown List -->
                        <?php include "edit_site_locations.php"; echo $siteLocationDropdownEdit; ?> 
                    </div>

                    <input type="hidden" id="editAssetID" name="assetID" value="" readonly>

                    <button type="submit">Save Changes</button>
                </form>
        </div>
    </div>
    <!-- Change Image Section-->
    <div id="imageUploadModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2>Change Asset Image</h2>

            <!-- Existing Image Preview -->
            <div id="existingImagePreviewContainer">
                <h3>Current Image:</h3>
                <img id="existingImagePreview" src="" alt="Current Asset Image" style="max-width: 200px; max-height: 200px;">
            </div>

            <!-- Upload Section -->
            <div class="upload-container">
                <label for="modalImageUpload" class="upload-label">
                    <span>Drag and drop or, <b>Browse</b> your files</span>
                </label>
                <input type="file" id="modalImageUpload" name="ImageUpload" accept="image/*" style="display:none;" required>
                <div id="modalImagePreviewContainer" style="display:none;">
                    <img id="modalImagePreview" src="#" alt="New Image Preview">
                    <div id="modalImageInfo">
                        <span id="modalImageFilename"></span>
                        <span id="modalImageFilesize"></span>
                    </div>
                </div>
            </div>

            <input type="hidden" id="imageAssetID" name="assetID" value="<?php echo htmlspecialchars($asset['assetID']); ?>" readonly>

            <button id="clearImageButton">Clear Image</button>
            <button id="saveImageButton">Save Image</button>
        </div>
    </div>

    <!-- Preview Asset Tag PDF Modal -->
    <div id="previewModal" class="modal">
        <div id="pdfpreviewmodal" class="modal-content">
            <span class="close" data-modal-id="previewModal">×</span>
            <p>Currently Viewing:</p>
            <h2>Asset Tag - <?php echo ($asset['assetTag']); ?></h2>

            <!-- ==== LOADING INDICATOR ==== -->
            <div id="pdfLoadingSpinner" class="loading-spinner-overlay"> <!-- Container DIV -->
                <div class="fa-3x">
                    <i class="fa-solid fa-spinner fa-spin-pulse"></i>
                </div>
                <p>Loading PDF Preview...</p> <!-- Optional text -->
            </div>

            <div id="tag-preview">
                <!-- Iframe to display the PDF -->
                <iframe id="pdfPreviewIframe"></iframe>
            </div>
            <div class="modal-buttons">
                <button id="downloadPdfButton">Download PDF</button>
                <button id="downloadPngButton">Download as PNG</button>
            </div>
        </div>
    </div>
</body>
</html>