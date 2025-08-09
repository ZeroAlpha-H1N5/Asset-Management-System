<?php
header('Content-Type: application/json');

require_once 'functions.php';

$conn = db_connect();

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Sanitize and validate the input data
$assetId = isset($_POST['assetID']) ? validateNumber($_POST['assetID']) : 0;
$ownerTo = isset($_POST['ownerTo']) ? sanitize_input($_POST['ownerTo']) : '';
$departmentId = isset($_POST['departmentID']) ? validateNumber($_POST['departmentID']) : 0;
$ownerPosition = isset($_POST['ownerPosition']) ? sanitize_input($_POST['ownerPosition']) : '';
$ownerDateHired = isset($_POST['ownerDateHired']) ? sanitize_input($_POST['ownerDateHired']) : '';
$ownerPhoneNum = isset($_POST['ownerPhoneNum']) ? validateNumber($_POST['ownerPhoneNum']) : 0;
$turnoverDate = isset($_POST['turnoverDate']) ? sanitize_input($_POST['turnoverDate']) : '';
$turnoverReason = isset($_POST['turnoverReason']) ? sanitize_input($_POST['turnoverReason']) : '';
$turnoverDetails = isset($_POST['turnoverDetails']) ? sanitize_input($_POST['turnoverDetails']) : '';
$siteId = isset($_POST['site_id']) ? validateNumber($_POST['site_id']) : 0;  // Retrieve site_id

// Basic validation (strict)
if ($assetId <= 0 || empty($turnoverDate) || empty($turnoverReason) || $siteId <= 0) { //Include siteId here
    echo json_encode(['success' => false, 'message' => 'Missing or invalid required fields.']);
    exit;
}

$newOwnerId = 0;

// Get the CURRENT owner_id and site_id BEFORE updating the assets table
$sql_get_current_details = "SELECT owner_id, site_id FROM assets WHERE asset_id = ?";
$stmt_get_current_details = $conn->prepare($sql_get_current_details);

if ($stmt_get_current_details === false) {
    echo json_encode(['success' => false, 'message' => "Error preparing select current details statement: " . $conn->error]);
    exit;
}

$stmt_get_current_details->bind_param("i", $assetId);
$stmt_get_current_details->execute();
$result_current_details = $stmt_get_current_details->get_result();

if ($result_current_details->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => "Error: Could not find current details for asset."]);
    exit;
}

$row_current_details = $result_current_details->fetch_assoc();
$previousOwnerId = intval($row_current_details['owner_id']);
$previousSiteId = intval($row_current_details['site_id']); // Get previous site ID
$stmt_get_current_details->close();

// Determine the owner_id based on the ownerTo value
if ($ownerTo == 'Safexpress Logistics Inc.') {
    $safexDeptId = 10;
    $safexName = 'Safexpress Logistics Inc.';
    $safexPosition = 'Company';
    $safexDateHired = null;
    $safexPhoneNum = null;

    // Check if it exists, get its ID, or Create it with the relevant data
    $sql_select_owner = "SELECT owner_id FROM owners WHERE owner_name = 'Safexpress Logistics Inc.'";
    $result_owner = $conn->query($sql_select_owner);

        if ($result_owner && $result_owner->num_rows > 0) {   //If SQL has valid result to assign the safex owner with default company.
            $row_owner = $result_owner->fetch_assoc();
            $newOwnerId = intval($row_owner['owner_id']);   // the owners owner id

        $safexDeptId = 10;  //Create the variables to assign
        $safexName = 'Safexpress Logistics Inc.';
        $safexPosition = 'Company';
        $safexDateHired = null;
        $safexPhoneNum = null;
    $sqlUpdateOwner = "UPDATE owners SET department_id = ?, owner_position = ?, owner_date_hired = ?, owner_phone_num = ? WHERE owner_id = ?";

    $stmt = $conn->prepare($sqlUpdateOwner);

        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => "Error preparing turnover log insert statement: " . $conn->error]); // Show message to developer for troubleshooting
        exit; //Exit the statement to prevent further errors
    }

            $stmt->bind_param("isssi", $safexDeptId, $safexName, $safexPosition, $safexDateHired, $safexPhoneNum);    //bind the following parameters from this function.
            if ($stmt->execute() === TRUE) {      //If the statement has executed with no problems.
        } else {
            echo json_encode(['success' => false, 'message' => "Error inserting new Safexpress Logistics Inc. owner: " . $stmt->error]);       //error statement to show that the program didn't run as intended.
            exit;
        }
    } else {
          $sql_insert_owner = "INSERT INTO owners (department_id, owner_name, owner_position, owner_date_hired, owner_phone_num)  VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql_insert_owner);   //prepares the statement

        if ($stmt === false) {     //displays any error messages
            echo json_encode(['success' => false, 'message' => "Error preparing insert statement: " . $conn->error]);       //error statement to check for SQL errors.
            exit;       //stop to prevent any future errors.
        }

        $stmt->bind_param("isssi", $safexDeptId, $safexName, $safexPosition, $safexDateHired, $safexPhoneNum);     //bind the parameters to ensure everything is proper.

        if ($stmt->execute() === TRUE) {        //IF statement to execute SQL.
            $newOwnerId = $conn->insert_id;   //Get the newly inserted owner value with no errors
        } else {
            echo json_encode(['success' => false, 'message' => "Error inserting new Safexpress Logistics Inc. owner: " . $stmt->error]);       //More error messages.
            exit;       //stop
        }
    }

   $stmt->close(); //close statement with no errors to report.
} else {
    //Logic For new Custodian insert and old custodians values:
        $sql_insert_owner = "INSERT INTO owners (department_id, owner_name, owner_position, owner_date_hired, owner_phone_num)  VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql_insert_owner);   //prepares the statement

        if ($stmt === false) {     //displays any error messages
            echo json_encode(['success' => false, 'message' => "Error preparing insert statement: " . $conn->error]);       //error statement to check for SQL errors.
            exit;       //stop to prevent any future errors.
        }

        $stmt->bind_param("isssi", $departmentId, $ownerTo, $ownerPosition, $ownerDateHired, $ownerPhoneNum);     //bind the parameters to ensure everything is proper.

        if ($stmt->execute() === TRUE) {        //IF statement to execute SQL.
            $newOwnerId = $conn->insert_id;   //Get the newly inserted owner value with no errors
        } else {
            echo json_encode(['success' => false, 'message' => "Error inserting new custodian: " . $stmt->error]);       //More error messages.
            exit;       //stop
        }

        $stmt->close();  //close

    }


// More validation (check $newOwnerId)
if ($newOwnerId <= 0) {
    echo json_encode(['success' => false, 'message' => "Could not determine new owner ID."]); // error
    exit;
}

// Validate department ID
$sql_validate_dept = "SELECT department_id FROM departments WHERE department_id = $departmentId";
$result_validate_dept = $conn->query($sql_validate_dept);

if (!$result_validate_dept || $result_validate_dept->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid department selected.']);    //SQL has problem.
    exit;
}

// Update the assets table with the new owner_id AND site_id
$sql_update_asset = "UPDATE assets SET owner_id = $newOwnerId, site_id = $siteId WHERE asset_id = $assetId";   //update the assets

if ($conn->query($sql_update_asset) === TRUE) {     //Success
    // Insert the turnover log with the previous_owner_id AND previous_site_id
$sql_insert_log = "INSERT INTO asset_turnover_log (asset_id, previous_owner_id, previous_site_id, site_id, turnover_date, turnover_reason, turnover_details)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert_log = $conn->prepare($sql_insert_log);   //PREPARE THE TABLE

        if ($stmt_insert_log === false) {     //IF SQL statement doesn't follow what SQL wants.
            echo json_encode(['success' => false, 'message' => "Error preparing turnover log insert statement: " . $conn->error]);       //SQL problem is shown.
            exit;       //stop program to let admin fix it.
        }

        $stmt_insert_log->bind_param("iiiisss", $assetId, $previousOwnerId, $previousSiteId, $siteId, $turnoverDate, $turnoverReason, $turnoverDetails);     //Bind the value in the function

        if ($stmt_insert_log->execute() === TRUE) {        //if there are no errors then SQL will insert and run with no problem
            // Success!  Create a detailed response
        $response = [       //show what it is all added to
            'success' => true,
            'message' => 'Asset turnover completed successfully.',
            'assetId' => $assetId,
            'previousOwnerId' => $previousOwnerId,
            'newOwnerId' => $newOwnerId,
            'previousSiteId' => $previousSiteId, // Include previousSiteId in the response
            'siteId' => $siteId, // Include siteId in the response
            'turnoverDate' => $turnoverDate,
            'turnoverReason' => $turnoverReason,
            'turnoverDetails' => $turnoverDetails
        ];
        echo json_encode($response);       //show

    } else {        //If SQL can't add.
        echo json_encode(['success' => false, 'message' => "Error inserting turnover log: " . $conn->error]);    //Trouble shooting.
    }

    $stmt_insert_log->close();       //Close code
} else {
    echo json_encode(['success' => false, 'message' => "Error updating asset: " . $conn->error]);     //State that it will create new row as SQL has a problem.
}

$conn->close();     //CLOSE CODE
?>