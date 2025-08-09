<?php
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["new_status_name"]) && !empty($_POST["new_status_name"])) {
        // --- INSERT Logic ---
        $new_status_name = sanitize_input($_POST["new_status_name"]);
        $conn = db_connect();
        if ($conn === false) {
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO asset_status (status_name) VALUES (?)");
        $stmt->bind_param("s", $new_status_name);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>New asset status added successfully!</p>";
            //header("Location: settings.php");  //No redirect for AJAX
            //exit();
        } else {
            echo "<p style='color: red;'>Error adding asset status: " . $stmt->error . "</p>";
        }
        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["status_name"]) && is_array($_POST["status_name"]) && isset($_POST["selected_status_id"])) {
         // --- UPDATE Logic ---
        $selected_status_id = (int)$_POST["selected_status_id"]; // Ensure it's an integer for security
        $status_name = sanitize_input($_POST["status_name"][$selected_status_id]);

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("UPDATE asset_status SET status_name = ? WHERE status_id = ?");
        $stmt->bind_param("si", $status_name, $selected_status_id); //s = string, i=integer

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Asset status updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error updating asset status: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["selected_status_id"]) && !isset($_POST["status_name"])) {
        // --- DELETE Logic ---
        $selected_status_id = (int)$_POST["selected_status_id"]; // Ensure it's an integer for security

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM asset_status WHERE status_id = ?");
        $stmt->bind_param("i", $selected_status_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Asset status deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting asset status: " . $stmt->error . "</p>";
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