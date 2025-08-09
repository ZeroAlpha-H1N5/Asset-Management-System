<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();
$result = null; 

if ($conn) {
    $sql = "SELECT type_id, type_name FROM asset_type ORDER BY type_name";
    $result = $conn->query($sql);
}

// Determine the prefix. If not set, default to an empty string.
$prefix = isset($prefix) ? $prefix : '';
$commonSelectId   = $prefix . 'asset_type'; 
$commonSelectName = 'asset_type';       
$commonLabelText  = "Asset Type:";

// --- Generate the core <option> tags once to reuse them ---
$optionsHtml = '<option value="">-- Select an Asset Type --</option>'; // Default option
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $type_id = $row['type_id'];
        $type_name = htmlspecialchars($row['type_name']);  // Escape for HTML
        $optionsHtml .= "<option value=\"" . htmlspecialchars($type_id) . "\">$type_name</option>";
    }
} else {
    $optionsHtml .= '<option value="">No asset types found</option>';
}

// --- 1. Generate HTML for the version WITH the custom-select-wrapper ---
$html_custom = '<div class="custom-select-wrapper">';
$html_custom .= '  <span class="select-label">' . htmlspecialchars($commonLabelText) . '</span>';
$html_custom .= '  <select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_custom .= $optionsHtml; 
$html_custom .= '  </select>';
$html_custom .= '</div>';
$assetTypeDropdownCustom = $html_custom; // Variable for the wrapped version


// --- 2. Generate HTML for the original version (WITHOUT wrapper, with a separate label) ---
$html_original = '<label for="' . htmlspecialchars($commonSelectId) . '">' . htmlspecialchars($commonLabelText) . ' </label>'; 
$html_original .= '<select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_original .= $optionsHtml; 
$html_original .= '</select>';
$assetTypeDropdown = $html_original; // Variable for the original version


// Close the database connection if it was opened
if ($conn) {
    $conn->close();
}

?>