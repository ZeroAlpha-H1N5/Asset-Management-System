<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();

// Fetch the site locations from the database
$sql = "SELECT site_id, site_name FROM site_locations ORDER BY site_name";
$result = $conn->query($sql);

// Generate the HTML for the dropdown
$html = '<label for="editSiteID">Site Location:</label>';
$html .= '<select id="editSiteID" name="siteID" required>';
$html .= '<option value="">-- Select a Site Location --</option>';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $site_id = $row['site_id'];
        $site_name = htmlspecialchars($row['site_name']);  // Escape for HTML
        $html .= "<option value=\"$site_id\">$site_name</option>";
    }
} else {
    $html .= '<option value="">No site locations found</option>';
}

$html .= '</select>';

$conn->close();

// Store the HTML in a variable to be used later
$siteLocationDropdownEdit = $html;
?>