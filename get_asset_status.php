<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();
$result = null; // Initialize result

// Proceed with query only if connection is successful
if ($conn) {
    $sql = "SELECT status_id, status_name FROM asset_status ORDER BY status_name";
    $result = $conn->query($sql);
}

// Determine the prefix. If not set, default to an empty string.
$prefix = isset($prefix) ? $prefix : '';
$commonSelectId   = $prefix . 'asset_status'; 
$commonSelectName = 'asset_status';          
$commonLabelText  = "Asset Status:";

// --- Generate the core <option> tags once to reuse them ---
$optionsHtml = '<option value="">-- Select an Asset Status --</option>'; // Default option
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_id = $row['status_id'];
        $status_name = htmlspecialchars($row['status_name']);  // Escape for HTML
        $optionsHtml .= "<option value=\"" . htmlspecialchars($status_id) . "\">$status_name</option>";
    }
} else {
    // This message will appear if the query failed or returned no rows
    $optionsHtml .= '<option value="">No asset statuses found</option>';
}

// --- 1. Generate HTML for the version WITH the custom-select-wrapper ---
$html_custom = '<div class="custom-select-wrapper">';
$html_custom .= '  <span class="select-label">' . htmlspecialchars($commonLabelText) . '</span>';
$html_custom .= '  <select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_custom .= $optionsHtml; 
$html_custom .= '  </select>';
$html_custom .= '</div>';
$assetStatusDropdownCustom = $html_custom; // Variable for the wrapped version


// --- 2. Generate HTML for the original version (WITHOUT wrapper, with a separate label) ---
$html_original = '<label for="' . htmlspecialchars($commonSelectId) . '">' . htmlspecialchars($commonLabelText) . ' </label>'; 
$html_original .= '<select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_original .= $optionsHtml; 
$html_original .= '</select>';
$assetStatusDropdown = $html_original; // Variable for the original version


// Close the database connection if it was opened
if ($conn) {
    $conn->close();
}
?>