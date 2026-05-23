<?php
/**
 * Sentinel Access Database Connection Utility
 * 
 * Establishes a connection to the MySQL database using environment variables
 * set by Docker Compose, with standard XAMPP stack compatibility fallback.
 */

// Retrieve environment variables with fallback to docker-compose defaults
$db_host = getenv('DB_HOST') ?: 'db';
$db_port = getenv('DB_PORT') ?: 3306;
$db_name = getenv('DB_NAME') ?: 'pickleball';
$db_user = getenv('DB_USER') ?: 'pickleball';
$db_pass = getenv('DB_PASS') ?: 'pickleball_pw';

// Initialize MySQLi
$conn = mysqli_init();
if (!$conn) {
    die("MySQLi initialization failed.");
}

// Set connection timeout (5 seconds) to prevent infinite loading
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

// Establish connection safely
if (!@$conn->real_connect($db_host, $db_user, $db_pass, $db_name, $db_port)) {
    // Elegant fallback page in accordance with Sentinel Access Design System
    header('HTTP/1.1 500 Internal Server Error');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Error - Sentinel Access</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --bg-primary: #090d16; /* Deep architectural blue-black */
                --card-bg: #111827;
                --text-primary: #f8fafc;
                --text-secondary: #94a3b8;
                --accent-primary: #3b82f6;
                --accent-error: #ef4444;
                --border-color: #1e293b;
            }
            body {
                background-color: var(--bg-primary);
                color: var(--text-primary);
                font-family: 'Inter', sans-serif;
                margin: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 1rem;
            }
            .error-card {
                background-color: var(--card-bg);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 2.5rem;
                max-width: 450px;
                width: 100%;
                text-align: center;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            }
            .error-icon {
                color: var(--accent-error);
                font-size: 2.5rem;
                margin-bottom: 1.25rem;
            }
            h1 {
                font-size: 1.5rem;
                font-weight: 700;
                margin: 0 0 0.75rem 0;
                letter-spacing: -0.025em;
            }
            p {
                color: var(--text-secondary);
                font-size: 0.95rem;
                line-height: 1.6;
                margin: 0 0 1.75rem 0;
            }
            .btn-retry {
                background-color: var(--accent-primary);
                color: #ffffff;
                border: none;
                padding: 0.75rem 1.75rem;
                font-weight: 600;
                font-size: 0.9rem;
                border-radius: 6px;
                cursor: pointer;
                transition: background-color 0.2s, transform 0.1s;
            }
            .btn-retry:hover {
                background-color: #2563eb;
            }
            .btn-retry:active {
                transform: scale(0.98);
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">⚠️</div>
            <h1>Database Connectivity Error</h1>
            <p>Sentinel Access is currently unable to establish a secure link to the database. Please ensure your database services are running and try again.</p>
            <button class="btn-retry" onclick="window.location.reload();">Retry Connection</button>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Set connection charset to support full unicode names
$conn->set_charset("utf8mb4");

// Auto-seed default administrator if the admin table is empty
$admin_count_query = $conn->query("SELECT COUNT(*) as count FROM `admin`");
if ($admin_count_query) {
    $row = $admin_count_query->fetch_assoc();
    if ($row['count'] == 0) {
        $default_name = "System Administrator";
        $default_user = "admin";
        $default_pass = password_hash("admin123", PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO `admin` (`name`, `username`, `password`) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $default_name, $default_user, $default_pass);
            $stmt->execute();
            $stmt->close();
        }
    }
}

