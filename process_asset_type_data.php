<?php
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["new_type_name"]) && !empty($_POST["new_type_name"]) && isset($_POST["new_type_code"]) && !empty($_POST["new_type_code"])) {
        // --- INSERT Logic ---
        $new_type_name = sanitize_input($_POST["new_type_name"]);
        $new_type_code = sanitize_input($_POST["new_type_code"]);

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO asset_type (type_name, type_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_type_name, $new_type_code); // Two strings

        if ($stmt->execute()) {
            echo "<p style='color: green;'>New asset type added successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error adding asset type: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["type_name"]) && is_array($_POST["type_name"]) && isset($_POST["type_code"]) && is_array($_POST["type_code"]) && isset($_POST["selected_type_id"])) {
        // --- UPDATE Logic ---
        $selected_type_id = (int)$_POST["selected_type_id"];
        $type_name = sanitize_input($_POST["type_name"][$selected_type_id]);
        $type_code = sanitize_input($_POST["type_code"][$selected_type_id]);

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("UPDATE asset_type SET type_name = ?, type_code = ? WHERE type_id = ?");
        $stmt->bind_param("ssi", $type_name, $type_code, $selected_type_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Asset type updated successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error updating asset type: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();

    } elseif (isset($_POST["selected_type_id"]) && !isset($_POST["type_name"]) && !isset($_POST["type_code"])) {
        // --- DELETE Logic ---
        $selected_type_id = (int)$_POST["selected_type_id"];

        $conn = db_connect();
        if ($conn === false) {
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM asset_type WHERE type_id = ?");
        $stmt->bind_param("i", $selected_type_id);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Asset type deleted successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error deleting asset type: " . $stmt->error . "</p>";
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