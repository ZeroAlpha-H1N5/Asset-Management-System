<?php
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php"); // Redirect if not logged in
    exit;
}

// Access user information from the session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome - Safexpress Logistics Inc.</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="icons/sx_logo.png">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/functions.js"></script>

    <style>
    /* Credits Styles */
    .dedication {
        text-align: center;
        margin: 40px;
        font-size: larger;
    }

    .developers, .supervisors, .sponsors {
        margin-bottom: 20px;
        text-align: center;
    }

    .developer-list, .supervisor-list, .sponsor-list, .adviser {
        display: flex;
        justify-content: center;
        gap: 250px;
        margin-bottom: 15px;
    }

    .developer-item, .supervisor-item, .sponsor-item, .adviser-item {
        text-align: center;
        justify-content: center;
        display: inline-block;
        vertical-align: middle;
    }

    .image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #ddd;
        margin-bottom: 5px;
        text-align: center;
        display: inline-block;
        vertical-align: middle;
        overflow: hidden; /* Ensure image stays within the circle */
    }

    .image img {
        width: 100%;
        height: auto;
        display: block;
    }

    .title {
        font-weight: bold;
        font-size:larger;
    }

    hr {
        border: 0;
        border-top: 1px solid #ccc;
        margin: 20px 0;
    }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo-container">
                <a href="dashboard.php" style="text-decoration: none;">
                    <img src="icons/safexpress_logo.png" alt="SafeXpress Logistics Logo" style="cursor: pointer;">
                </a>
            </div>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="assets.php">Assets</a></li>
                <li><a href="history.php">Logs</a></li>
                <li><a href="report_management.php">Export Reports</a></li>
                <li><a href="credits.php" class="active">Credits</a></li>
                <li><a href="#" id="logoutLink">Logout</a></li>
            </ul>
        </aside>
    <main class="content">
        <h2>Credits</h2>
        <div class="datetime-container">
            <span id="datetime"></span>
        </div>
        <button id="toggleSidebarButton">☰</button>
        <main class="content">
            <div class="dedication">
                <p><i>This System is dedicated to the<br>
                    Business Development Department<br>
                    of Safexpress Logistics Inc.</i></p>
            </div>
            <hr>
            <div class="developers">
                <p><span class="title">Developed by: <br> UDM Students</span></p><br>
                <div class="developer-list">
                    <div class="developer-item">
                        <div class="image">
                            <img src="icons/pic.jpg" alt="Simon Quinzon">
                        </div>
                        <p>Simon Quinzon<br>IT Intern/Developer</p>
                    </div>
                    <div class="developer-item">
                        <div class="image">
                            <img src="icons/pic.jpg" alt="Irish Grace Blanco">
                        </div>
                        <p>Irish Grace Blanco<br>IT Intern/Designer</p>
                    </div>
                </div>
            </div>

            <div class="supervisors">
                <p><span class="title">Supervised by: <br> Business Development Department</span></p><br>
                <div class="supervisor-list">
                    <div class="supervisor-item">
                        <div class="image">
                            <img src="icons/Alexandra-Joyce-Albina.png" alt="Alexandra Joyce Albine">
                        </div>
                        <p>Alexandra Joyce Albina<br>Department Supervisor</p>
                    </div>
                    <div class="supervisor-item">
                        <div class="image">
                            <img src="icons/Ivanna-Samera.png" alt="Ivanna Samera">
                        </div>
                        <p>Ivanna Samera<br>Supervisor</p>
                    </div>
                </div>
            </div>

            <div class="sponsors">
                <p><span class="title">Sponsors: <br> Safexpress Logistics Inc Execom</span></p><br>
                <div class="sponsor-list">
                    <div class="sponsor-item">
                        <div class="image">
                            <img src="icons/Eden-Satinitigan.png" alt="Eden Satinitigan">
                        </div>
                        <p>Eden Satinitigan<br>President</p>
                    </div>
                    <div class="sponsor-item">
                        <div class="image">
                            <img src="icons/Richard-Cunanan.png" alt="Richard Cunanan">
                        </div>
                        <p>Richard Cunanan<br>CEO</p>
                    </div>
                </div>
            </div>
            
            <div class="adviser">
                <div class="adviser-item">
                        <p><span class="title">Project Manager / Adviser:</span></p><br>
                    <div class="image">
                        <img src="icons/Clarence-Lucido.png" alt="Clarence Lucido"><br>
                    </div>
                    <p>Clarence Lucido<br>BEQMS Supervisor</p>
                </div>  
            </div>  
        </div>
    </main>

    <!-- Logout Confirmation Popup -->
    <div id="logoutModal" class="modal">
        <div class="modal-content logout-style">
            <span class="close">×</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to log out?</p>
            <div class="modal-buttons">
                <button id="confirmLogout">Yes, Logout</button> <br> <br>
                <button id="cancelLogout">Cancel</button>
            </div>
        </div>
    </div>
</body>
</html>