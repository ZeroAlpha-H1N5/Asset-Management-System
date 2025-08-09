<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();

// Fetch the asset statuses from the database
$sql = "SELECT status_id, status_name FROM asset_status ORDER BY status_name";
$result = $conn->query($sql);

// Generate the HTML for the dropdown
$html = '<label for="editStatusID">Asset Status:</label>';
$html .= '<select id="editStatusID" name="statusID" required>';
$html .= '<option value="">-- Select an Asset Status --</option>'; // Default option

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_id = $row['status_id'];
        $status_name = htmlspecialchars($row['status_name']);  // Escape for HTML
        $html .= "<option value=\"$status_id\">$status_name</option>";
    }
} else {
    $html .= '<option value="">No asset statuses found</option>';
}

$html .= '</select>';

$conn->close();

// Store the HTML in a variable to be used later
$assetStatusDropdownEdit = $html;
?>