<?php

require_once 'db_config.php';

session_start(); // Start the session

// Database Connection Function
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// --- Database Query Helper Function ---
function executeQuery(
    mysqli $conn,
    string $sql,
    array $params = [],
    string $types = ''
): mysqli_result|false {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error . " | SQL: " . $sql);
        return false;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        error_log("Error executing statement: " . $stmt->error);
        return false;
    }

    return $stmt->get_result();
}

// Sanitize User Input Function
function sanitize_input($data) {
    if ($data === null) {
        return null;
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateNumber($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = intval($data);
    return $data;
}

// User Authentication Function
function authenticate_user($username, $password) {
    $conn = db_connect();

    $username = sanitize_input($username);

    $sql = "SELECT user_id, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) { // Verify hashed password
            // Authentication successful
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $row['role']; // Store the user role in session
            $conn->close();
            return true;
        } else {
            $conn->close();
            return false; // Incorrect password
        }
    } else {
        $conn->close();
        return false; // User not found
    }
}

// Check if User is Logged In
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Role-Based Access Control (RBAC) Function
function has_permission($required_role) {
    if (is_logged_in()) {
        if ($_SESSION['role'] == 'Admin') { //Admins have full access
            return true;
        }
        return ($_SESSION['role'] === $required_role);
    }
    return false; // Not logged in, no permission
}

function getStatusClass($statusName) {
    $baseClass = 'status-label';
    switch (strtolower($statusName)) {
        case 'brand new':
        case 'new':
            return $baseClass . ' status-new';
        case 'used':
            return $baseClass . ' status-used';
        case 'defect':
        case 'for repair':
        case 'damaged':
            return $baseClass . ' status-defect';
        case 'disposed':
             return $baseClass . ' status-disposed';
        default:
            return $baseClass; // Default grey
    }
}
?>