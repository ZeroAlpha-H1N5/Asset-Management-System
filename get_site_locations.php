<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();
$result = null; // Initialize result

if ($conn) {
    $sql = "SELECT site_id, site_name FROM site_locations ORDER BY site_name";
    $result = $conn->query($sql);
}

// Determine the prefix. If not set, default to an empty string.
$prefix = isset($prefix) ? $prefix : '';
$commonSelectId   = $prefix . 'site_id'; 
$commonSelectName = 'site_id';          
$commonLabelText  = "Site Location:";

// --- Generate the core <option> tags once to reuse them ---
$optionsHtml = '<option value="">-- Select a Site Location --</option>'; // Default option
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $site_id_val = $row['site_id']; 
        $site_name = htmlspecialchars($row['site_name']);  // Escape for HTML
        $optionsHtml .= "<option value=\"" . htmlspecialchars($site_id_val) . "\">$site_name</option>";
    }
} else {
    // This message will appear if the query failed or returned no rows
    $optionsHtml .= '<option value="">No site locations found</option>';
}

// --- 1. Generate HTML for the version WITH the custom-select-wrapper ---
$html_custom = '<div class="custom-select-wrapper">';
$html_custom .= '  <span class="select-label">' . htmlspecialchars($commonLabelText) . '</span>';
$html_custom .= '  <select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_custom .= $optionsHtml; // Use the pre-generated options
$html_custom .= '  </select>';
$html_custom .= '</div>';
$siteLocationDropdownCustom = $html_custom; // Variable for the wrapped version


// --- 2. Generate HTML for the original version (WITHOUT wrapper, with a separate label) ---
$html_original = '<label for="' . htmlspecialchars($commonSelectId) . '">' . htmlspecialchars($commonLabelText) . ' </label>'; 
$html_original .= '<select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_original .= $optionsHtml; 
$html_original .= '</select>';
$siteLocationDropdown = $html_original; // Variable for the original version


// Close the database connection if it was opened
if ($conn) {
    $conn->close();
}

?>