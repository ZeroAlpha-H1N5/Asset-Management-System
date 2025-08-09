<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();
$result = null; 

if ($conn) {
    $sql = "SELECT department_id, department_name FROM departments ORDER BY department_name";
    $result = $conn->query($sql);
}

// Determine the prefix. If not set, default to an empty string.
$prefix = isset($prefix) ? $prefix : '';
$commonSelectId   = $prefix . 'departmentID'; 
$commonSelectName = 'departmentID';          
$commonLabelText  = "Department:";

// --- Generate the core <option> tags once to reuse them ---
$optionsHtml = '<option value="">-- Select a Department --</option>'; // Default option
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $department_id = $row['department_id'];
        $department_name = htmlspecialchars($row['department_name']);
        $optionsHtml .= "<option value=\"" . htmlspecialchars($department_id) . "\">$department_name</option>";
    }
} else {
    $optionsHtml .= '<option value="">No departments found</option>';
}

// --- 1. Generate HTML for the version WITH the custom-select-wrapper ---
$html_custom = '<div class="custom-select-wrapper">';
$html_custom .= '  <span class="select-label">' . htmlspecialchars($commonLabelText) . '</span>';
$html_custom .= '  <select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_custom .= $optionsHtml; // Use the pre-generated options
$html_custom .= '  </select>';
$html_custom .= '</div>';
$departmentDropdownCustom = $html_custom; // Variable for the wrapped version


// --- 2. Generate HTML for the original version (WITHOUT wrapper, with a separate label) ---
$html_original = '<label for="' . htmlspecialchars($commonSelectId) . '">' . htmlspecialchars($commonLabelText) . ' </label>'; 
$html_original .= '<select id="' . htmlspecialchars($commonSelectId) . '" name="' . htmlspecialchars($commonSelectName) . '">';
$html_original .= $optionsHtml; 
$html_original .= '</select>';
$departmentDropdown = $html_original; // Variable for the original version


// Close the database connection if it was opened
if ($conn) {
    $conn->close();
}

?>