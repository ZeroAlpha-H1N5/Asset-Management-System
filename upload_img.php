<?php
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/SLI_ASSET/assets/";

// Check if the file was uploaded with no errors:
if (isset($_FILES["imageUpload"]) && $_FILES["imageUpload"]["error"] == 0) {
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $filename = $_FILES["imageUpload"]["name"];
    $filetype = $_FILES["imageUpload"]["type"];
    $filesize = $_FILES["imageUpload"]["size"];
    $temp_name = $_FILES["imageUpload"]["tmp_name"];

    // Verify file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!array_key_exists($ext, $allowed)) {
        die("Error: Please select a valid file format.");
    }

    $maxsize = 5 * 1024 * 1024;
    if ($filesize > $maxsize) {
        die("Error: File size is larger than the allowed limit.");
    }

    // Verify MIME type
    if (!in_array($filetype, $allowed)) {
        die("Error: Please select a valid file format.");
    }

    // Create the upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Verify directory permissions
    if (!is_writable($uploadDir)) {
        die("Error: Upload directory is not writable. Check permissions.");
    }

    // Verify filename for security
    $filename = basename($filename);
    $filename = preg_replace("/[^a-zA-Z0-9._-]/", "", $filename);
    if (empty($filename)) {
        die("Error: Invalid filename after sanitization.");
    }

    // Generate a unique filename to avoid overwrites
    $uniqueFilename = uniqid() . "_" . $filename;

    // Where to save the file on the server
    $path = $uploadDir . $uniqueFilename;

    // Attempt to move the uploaded file to its designated place
    if (move_uploaded_file($temp_name, $path)) {
        $newImagePath = "/SLI_ASSET/assets/" . $uniqueFilename; // Store the relative path
    } else {
        echo "<p>Error uploading file</p>";
    }
} else {
    echo "<p>No file was uploaded or there was an error.</p>";
}
?>