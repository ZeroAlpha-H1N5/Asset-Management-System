<?php
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["new_site_name"]) && !empty($_POST["new_site_name"]) && isset($_POST["new_site_region"]) && !empty($_POST["new_site_region"])) {
        // --- INSERT Logic ---
        $new_site_name = sanitize_input($_POST["new_site_name"]);
        $new_site_region = sanitize_input($_POST["new_site_region"]);

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO site_locations (site_name, site_region) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_site_name, $new_site_region);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>New site location added successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error adding site location: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["site_name"]) && is_array($_POST["site_name"]) && isset($_POST["site_region"]) && is_array($_POST["site_region"]) && isset($_POST["selected_site_id"])) {
        // --- UPDATE Logic ---
        $selected_site_id = (int)$_POST["selected_site_id"];
        $site_name = sanitize_input($_POST["site_name"][$selected_site_id]);
        $site_region = sanitize_input($_POST["site_region"][$selected_site_id]);

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("UPDATE site_locations SET site_name = ?, site_region = ? WHERE site_id = ?");
        $stmt->bind_param("ssi", $site_name, $site_region, $selected_site_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Site location updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error updating site location: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["selected_site_id"]) && !isset($_POST["site_name"]) && !isset($_POST["site_region"])) {
        // --- DELETE Logic ---
        $selected_site_id = (int)$_POST["selected_site_id"];

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM site_locations WHERE site_id = ?");
        $stmt->bind_param("i", $selected_site_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Site location deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting site location: " . $stmt->error . "</p>";
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