<?php
/**
 * Sentinel Access - Administrator Dashboard
 * USC Department of Computer Engineering
 * 
 * Provides secure access to contact tracing registers with multi-faceted search.
 */

session_start();

// 1. Session Authorization Check
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

// 2. Action: Logout Session
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

// 3. Gather Dashboard Analytics Metrics
$metric_contacts = 0;
$metric_active = 0;
$metric_logs = 0;

$res1 = $conn->query("SELECT COUNT(*) as count FROM contacts");
if ($res1) { $metric_contacts = $res1->fetch_assoc()['count']; }

$res2 = $conn->query("SELECT COUNT(*) as count FROM logs WHERE datetime_logout IS NULL");
if ($res2) { $metric_active = $res2->fetch_assoc()['count']; }

$res3 = $conn->query("SELECT COUNT(*) as count FROM logs");
if ($res3) { $metric_logs = $res3->fetch_assoc()['count']; }


// 4. Secure Dynamic Search Construction
$where_clauses = [];
$params = [];
$types = "";

if (!empty($_GET['search_city'])) {
    $where_clauses[] = "c.city LIKE ?";
    $params[] = "%" . trim($_GET['search_city']) . "%";
    $types .= "s";
}
if (!empty($_GET['search_barangay'])) {
    $where_clauses[] = "c.barangay LIKE ?";
    $params[] = "%" . trim($_GET['search_barangay']) . "%";
    $types .= "s";
}
if (!empty($_GET['search_province'])) {
    $where_clauses[] = "c.province LIKE ?";
    $params[] = "%" . trim($_GET['search_province']) . "%";
    $types .= "s";
}
if (!empty($_GET['search_usc_id'])) {
    $where_clauses[] = "c.usc_id_number LIKE ?";
    $params[] = "%" . trim($_GET['search_usc_id']) . "%";
    $types .= "s";
}
if (!empty($_GET['search_name'])) {
    $where_clauses[] = "(c.first_name LIKE ? OR c.last_name LIKE ?)";
    $search_name = "%" . trim($_GET['search_name']) . "%";
    $params[] = $search_name;
    $params[] = $search_name;
    $types .= "ss";
}
if (!empty($_GET['search_date_start'])) {
    $where_clauses[] = "l.datetime_login >= ?";
    $params[] = trim($_GET['search_date_start']) . " 00:00:00";
    $types .= "s";
}
if (!empty($_GET['search_date_end'])) {
    $where_clauses[] = "l.datetime_login <= ?";
    $params[] = trim($_GET['search_date_end']) . " 23:59:59";
    $types .= "s";
}

// Build core log SELECT query
$sql = "
    SELECT 
        l.entry_id,
        c.first_name,
        c.middle_name,
        c.last_name,
        c.usc_id_number,
        c.barangay,
        c.city,
        c.province,
        c.phone_number,
        c.email,
        l.datetime_login,
        l.datetime_logout
    FROM logs l
    INNER JOIN contacts c ON l.id_number = c.id
";

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY l.datetime_login DESC";

// Execute secure query using prepared statements
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database syntax statement failure.");
}

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Portal - Sentinel Access</title>
    
    <!-- Design System Global Styling -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Custom Admin Specific Grid and Layout Style Rules -->
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            width: 95%;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .admin-header-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-standard);
            padding: 1.5rem 2rem;
        }

        .admin-welcome h1 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin-bottom: 0.25rem;
        }

        .admin-welcome p {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        
        .profile-info {
            text-align: right;
            font-size: 0.85rem;
        }
        
        .profile-name {
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .profile-role {
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        
        /* Metric Analytics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        
        .metric-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-standard);
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--accent-primary);
        }
        
        .metric-card.active-metric::before {
            background-color: var(--accent-success);
        }
        
        .metric-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: var(--text-muted);
        }
        
        .metric-value {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }
        
        /* Interactive Search Filter Bar */
        .filter-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-standard);
            padding: 2rem;
        }
        
        .filter-header {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 992px) {
            .filter-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .metrics-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
        
        @media (max-width: 640px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            .admin-header-panel {
                flex-direction: column;
                gap: 1.25rem;
                padding: 1.25rem;
                text-align: center;
            }
            .admin-profile {
                flex-direction: column;
                gap: 0.75rem;
            }
            .profile-info {
                text-align: center;
            }
        }
        
        /* Table Styles */
        .table-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-standard);
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        
        .table-scroll {
            overflow-x: auto;
            width: 100%;
        }
        
        .logs-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }
        
        .logs-table th {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-weight: 600;
            padding: 1rem 1.25rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        .logs-table tr {
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition-smooth);
        }
        
        .logs-table tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        
        .logs-table tr:last-child {
            border-bottom: none;
        }
        
        .logs-table td {
            padding: 1.1rem 1.25rem;
            color: var(--text-primary);
            vertical-align: middle;
        }
        
        .logs-table td.cell-muted {
            color: var(--text-secondary);
        }
        
        /* Badge UI */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.3rem 0.65rem;
            border-radius: 4px;
            letter-spacing: 0.025em;
        }
        
        .badge-success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }
        
        .badge-success .badge-pulse {
            width: 6px;
            height: 6px;
            background-color: var(--accent-success);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--accent-success);
            animation: pulse-green 2s infinite;
        }
        
        .badge-muted {
            background-color: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
        }
        
        .no-results-panel {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        
        .no-results-icon {
            font-size: 2.5rem;
        }
    </style>
</head>
<body>

    <!-- Header bar -->
    <header class="kiosk-header">
        <div class="brand-section">
            <div class="brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <span class="brand-name">SENTINEL ACCESS</span>
                <span class="brand-tag">SECURE CORE</span>
            </div>
        </div>
        <div class="system-status">
            <span>ADMINISTRATIVE CONSOLE</span>
        </div>
    </header>

    <!-- Administrative dashboard container -->
    <div class="admin-container">
        
        <!-- Header Info Panel -->
        <div class="admin-header-panel">
            <div class="admin-welcome">
                <h1>Classroom & Guardhouse Administration Portal</h1>
                <p>Monitor security parameters, trace contact vectors, and verify attendance logs in real time.</p>
            </div>
            <div class="admin-profile">
                <div class="profile-info">
                    <div class="profile-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?></div>
                    <div class="profile-role">USC CpE Secretary</div>
                </div>
                <a href="admin.php?action=logout" class="btn btn-secondary" style="width: auto; padding: 0.5rem 1.25rem; font-size: 0.85rem;">
                    Sign Out
                </a>
            </div>
        </div>

        <!-- Metrics widgets -->
        <div class="metrics-grid">
            <div class="metric-card">
                <span class="metric-label">Total Unique Registry</span>
                <span class="metric-value"><?= number_format($metric_contacts) ?></span>
            </div>
            <div class="metric-card active-metric">
                <span class="metric-label">Currently Inside Office</span>
                <span class="metric-value" style="color: var(--accent-success);"><?= number_format($metric_active) ?></span>
            </div>
            <div class="metric-card">
                <span class="metric-label">Total Entry Sessions</span>
                <span class="metric-value"><?= number_format($metric_logs) ?></span>
            </div>
        </div>

        <!-- Advanced multi-filter search panel -->
        <div class="filter-card">
            <div class="filter-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span>Filter Contact Tracing Logs</span>
            </div>
            
            <form method="GET" action="admin.php">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="search_name">Search Name</label>
                        <input type="text" id="search_name" name="search_name" placeholder="First or Last Name" value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="search_usc_id">USC ID Number</label>
                        <input type="text" id="search_usc_id" name="search_usc_id" placeholder="e.g. 20102345" value="<?= htmlspecialchars($_GET['search_usc_id'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="search_barangay">Barangay</label>
                        <input type="text" id="search_barangay" name="search_barangay" placeholder="e.g. Talamban" value="<?= htmlspecialchars($_GET['search_barangay'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="search_city">City or Town</label>
                        <input type="text" id="search_city" name="search_city" placeholder="e.g. Cebu City" value="<?= htmlspecialchars($_GET['search_city'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="filter-grid" style="margin-bottom: 2rem;">
                    <div class="form-group">
                        <label for="search_province">Province</label>
                        <input type="text" id="search_province" name="search_province" placeholder="e.g. Cebu" value="<?= htmlspecialchars($_GET['search_province'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="search_date_start">Date Entered (Start)</label>
                        <input type="date" id="search_date_start" name="search_date_start" value="<?= htmlspecialchars($_GET['search_date_start'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="search_date_end">Date Entered (End)</label>
                        <input type="date" id="search_date_end" name="search_date_end" value="<?= htmlspecialchars($_GET['search_date_end'] ?? '') ?>">
                    </div>
                    
                    <!-- Dynamic Alignment Block -->
                    <div style="display: flex; align-items: flex-end;">
                    </div>
                </div>
                
                <div class="button-row" style="grid-template-columns: auto auto 1fr;">
                    <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.85rem 2rem;">
                        Apply Search Parameters
                    </button>
                    <a href="admin.php" class="btn btn-secondary" style="width: auto; padding: 0.85rem 2rem;">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Attendance logs table data -->
        <div class="table-card">
            <div class="filter-header" style="border-bottom: 1px solid var(--border-color); padding: 1.5rem 2rem; margin: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <span>Attendance Logs (<?= number_format(count($logs)) ?> rows matching)</span>
            </div>
            
            <div class="table-scroll">
                <?php if (count($logs) > 0): ?>
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>USC ID</th>
                                <th>Complete Address</th>
                                <th>Contact Information</th>
                                <th>Checked In (Date/Time)</th>
                                <th>Checked Out (Date/Time)</th>
                                <th>Kiosk Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <?= htmlspecialchars($log['last_name'] . ', ' . $log['first_name'] . ($log['middle_name'] ? ' ' . $log['middle_name'] : '')) ?>
                                    </td>
                                    <td>
                                        <?php if ($log['usc_id_number']): ?>
                                            <span style="font-family: monospace; font-weight: 600; color: #60a5fa;"><?= htmlspecialchars($log['usc_id_number']) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;">Visitor / Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="cell-muted" style="font-size: 0.85rem;">
                                        <?= htmlspecialchars($log['barangay'] . ', ' . $log['city'] . ', ' . $log['province']) ?>
                                    </td>
                                    <td class="cell-muted" style="font-size: 0.85rem;">
                                        <div style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($log['phone_number']) ?></div>
                                        <div><?= htmlspecialchars($log['email']) ?></div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 500; font-size: 0.85rem;">
                                            <?= date('M d, Y', strtotime($log['datetime_login'])) ?>
                                        </span>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary); font-variant-numeric: tabular-nums;">
                                            <?= date('h:i:s A', strtotime($log['datetime_login'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($log['datetime_logout']): ?>
                                            <span style="font-weight: 500; font-size: 0.85rem;">
                                                <?= date('M d, Y', strtotime($log['datetime_logout'])) ?>
                                            </span>
                                            <div style="font-size: 0.75rem; color: var(--text-secondary); font-variant-numeric: tabular-nums;">
                                                <?= date('h:i:s A', strtotime($log['datetime_logout'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;">Still Logged In</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['datetime_logout']): ?>
                                            <span class="badge badge-muted">Checked Out</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">
                                                <span class="badge-pulse"></span>
                                                <span>In Office</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results-panel">
                        <div class="no-results-icon">📂</div>
                        <h3 style="color: var(--text-primary); font-size: 1.1rem; font-weight: 600;">No attendance records found</h3>
                        <p style="font-size: 0.85rem; max-width: 320px; line-height: 1.5; color: var(--text-muted);">There are no contact tracing sessions matching your current filter choices. Try widening your date parameters or clearing names.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Kiosk Footer -->
    <footer class="kiosk-footer">
        <div>Sentinel Access v1.0.0 &bull; USC Department of Computer Engineering</div>
        <div>SECURE ADMINISTRATIVE PORTAL</div>
    </footer>

</body>
</html>
