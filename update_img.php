<?php
require_once 'functions.php';
$conn = db_connect();

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/SLI_ASSET/assets/";

// Check if the file was uploaded with no errors:
if (isset($_FILES["modalImageUpload"]) && $_FILES["modalImageUpload"]["error"] == 0) {
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $filename = $_FILES["modalImageUpload"]["name"];
    $filetype = $_FILES["modalImageUpload"]["type"];
    $filesize = $_FILES["modalImageUpload"]["size"];
    $temp_name = $_FILES["modalImageUpload"]["tmp_name"];

    // Verify file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!array_key_exists($ext, $allowed)) {
        die("Error: Please select a valid file format.");
    }

    // Verify filesize - maximum 5MB
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
        mkdir($uploadDir, 0755, true); // Create recursively with appropriate permissions
    }

    // Verify directory permissions
    if (!is_writable($uploadDir)) {
        die("Error: Upload directory is not writable. Check permissions.");
    }

    // Verify filename for security
    $filename = basename($filename);
    $filename = preg_replace("/[^a-zA-Z0-9._-]/", "", $filename); // Remove invalid characters
    if (empty($filename)) {
        die("Error: Invalid filename after sanitization.");
    }

    // Generate a unique filename to avoid overwrites
    $uniqueFilename = uniqid() . "_" . $filename;

    // Where to save the file on the server
    $path = $uploadDir . $uniqueFilename;

    // Attempt to move the uploaded file to its designated place
    if (move_uploaded_file($temp_name, $path)) {
        // File was uploaded successfully.

        // Check if asset_id is provided
        if (isset($_POST['assetId']) && is_numeric($_POST['assetId'])) {
            $assetId = intval($_POST['assetId']);
            $newImagePath = "/SLI_ASSET/assets/" . $uniqueFilename; //relative path.

            //Update the assets table with the new path.
            $sqlUpdateAsset = "UPDATE assets SET image_path = ? WHERE asset_id = ?";  // Prepare the statement.

            if ($stmtUpdateAsset = $conn->prepare($sqlUpdateAsset)) {   //Prepare statement.
                $stmtUpdateAsset->bind_param("si", $newImagePath, $assetId);  //Bind the parameters

                if ($stmtUpdateAsset->execute()) {      //Execute statement.
                    echo htmlspecialchars($newImagePath);         //Success: show asset to client

                } else {
                    error_log("Error executing update asset statement: " . $stmtUpdateAsset->error);   //Error: show log to developer
                    die("Error executing update asset statement.");
                }

                $stmtUpdateAsset->close();
            } else {
                error_log("Error preparing update asset statement: " . $conn->error);  //Error: show to developer
                die("Error preparing update asset statement.");
            }

            $conn->close();
            exit; // Stop further execution
        } else {
            die("Error: assetId not provided or invalid.");
        }
    } else {
       echo "<p>Error uploading file</p>";
    }
} else {
    echo "<p>No file was uploaded or there was an error.</p>";
}
?>