<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();

// Fetch the asset types from the database
$sql = "SELECT type_id, type_name FROM asset_type ORDER BY type_name"; // Order by name for readability
$result = $conn->query($sql);

// Generate the HTML for the dropdown
$html = '<label for="editTypeID">Asset Type:</label>';
$html .= '<select id="editTypeID" name="typeID" required>';
$html .= '<option value="">-- Select an Asset Type --</option>'; // Default option

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $type_id = $row['type_id'];
        $type_name = htmlspecialchars($row['type_name']);  // Escape for HTML
        $html .= "<option value=\"$type_id\">$type_name</option>";
    }
} else {
    $html .= '<option value="">No asset types found</option>';
}

$html .= '</select>';

$conn->close();

// Store the HTML in a variable to be used later
$assetTypeDropdownEdit = $html;
?>