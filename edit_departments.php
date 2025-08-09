<?php
require_once 'functions.php'; // Include your database connection function

$conn = db_connect();

// Fetch the departments from the database
$sql = "SELECT department_id, department_name FROM departments ORDER BY department_name";
$result = $conn->query($sql);

// Generate the HTML for the dropdown
$html = '<label for="editDepartmentID">Department:</label><br>';
$html .= '<select id="editDepartmentID" name="departmentID" required>';
$html .= '<option value="">-- Select a Department --</option>'; // Default option

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $department_id = $row['department_id'];
        $department_name = htmlspecialchars($row['department_name']);  // Escape for HTML
        $html .= "<option value=\"$department_id\">$department_name</option>";
    }
} else {
    $html .= '<option value="">No departments found</option>';
}

$html .= '</select><br><br>';

$conn->close();

// Store the HTML in a variable to be used later
$departmentDropdownEdit = $html;
?>