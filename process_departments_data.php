<?php
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["new_department_name"]) && !empty($_POST["new_department_name"])) {
        // --- INSERT Logic ---
        $new_department_name = sanitize_input($_POST["new_department_name"]);
        $conn = db_connect();
        if ($conn === false) {
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO departments (department_name) VALUES (?)");
        $stmt->bind_param("s", $new_department_name);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>New department added successfully!</p>";
            //header("Location: settings.php");  //No redirect for AJAX
            //exit();
        } else {
            echo "<p style='color: red;'>Error adding department: " . $stmt->error . "</p>";
        }
        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["department_name"]) && is_array($_POST["department_name"]) && isset($_POST["selected_department_id"])) {
         // --- UPDATE Logic ---
        $selected_department_id = (int)$_POST["selected_department_id"]; // Ensure it's an integer for security
        $department_name = sanitize_input($_POST["department_name"][$selected_department_id]);

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("UPDATE departments SET department_name = ? WHERE department_id = ?");
        $stmt->bind_param("si", $department_name, $selected_department_id); //s = string, i=integer

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Department updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error updating department: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["selected_department_id"]) && !isset($_POST["department_name"])) {
        // --- DELETE Logic ---
        $selected_department_id = (int)$_POST["selected_department_id"]; // Ensure it's an integer for security

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
        $stmt->bind_param("i", $selected_department_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Department deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting department: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();

    }  else {
        echo "<p style='color: red;'>Invalid data received.</p>";
    }
} else {
    echo "<p style='color: red;'>Invalid request.</p>";
}
?>