<?php
require_once 'functions.php';

// Check if the user is already logged in, redirect to dashboard if yes
if (is_logged_in()) {
    header("Location: dashboard.php"); // Replace with your dashboard page
    exit();
}

$login_error = ""; // Variable to store login error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (authenticate_user($username, $password)) {
        // Authentication successful, redirect to dashboard
        header("Location: dashboard.php"); // Replace with your dashboard page
        exit();
    } else {
        // Authentication failed
        $login_error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
</head>
<body class="login-body">
    <div class="login-container"> 
        <?php if (!empty($login_error)) { ?>
            <p style="color: red;"><?php echo $login_error; ?></p>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="text" id="username" name="username" placeholder="Username"><br><br>

            <input type="password" id="password" name="password" placeholder="Password"><br><br>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>