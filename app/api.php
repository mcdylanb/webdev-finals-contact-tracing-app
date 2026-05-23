<?php
/**
 * Sentinel Access - Kiosk Backend API Controller
 * 
 * Processes dynamic AJAX transactions for user identification, check-in,
 * registration, check-out, and administrative authentication.
 */

header('Content-Type: application/json');
session_start();

require_once 'db.php';

// Retrieve JSON input payload
$inputRaw = file_get_contents('php://input');
$data = json_decode($inputRaw, true);

if (!$data || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid API payload or action missing.']);
    exit;
}

$action = $data['action'];

switch ($action) {

    // 1. Identify User (USC ID, Phone, or Email)
    case 'identify':
        if (!isset($data['identity']) || empty(trim($data['identity']))) {
            echo json_encode(['error' => 'Credential input is required.']);
            exit;
        }

        $identity = trim($data['identity']);

        // Query database to check if contact exists
        $stmt = $conn->prepare("
            SELECT id, last_name, first_name, middle_name, barangay, city, province, phone_number, email, usc_id_number 
            FROM contacts 
            WHERE usc_id_number = ? OR phone_number = ? OR email = ? 
            LIMIT 1
        ");
        
        if (!$stmt) {
            echo json_encode(['error' => 'Database prepare statement error.']);
            exit;
        }

        $stmt->bind_param("sss", $identity, $identity, $identity);
        $stmt->execute();
        $result = $stmt->get_result();
        $contact = $result->fetch_assoc();
        $stmt->close();

        // Case A: No record exists
        if (!$contact) {
            echo json_encode(['status' => 'new']);
            exit;
        }

        // Case B: Record exists. Check if there is an active session (datetime_logout IS NULL)
        $logStmt = $conn->prepare("
            SELECT entry_id 
            FROM logs 
            WHERE id_number = ? AND datetime_logout IS NULL 
            LIMIT 1
        ");
        
        $logStmt->bind_param("i", $contact['id']);
        $logStmt->execute();
        $logResult = $logStmt->get_result();
        $activeSession = $logResult->fetch_assoc();
        $logStmt->close();

        if ($activeSession) {
            // Automatic Logout Flow
            $logoutStmt = $conn->prepare("
                UPDATE logs 
                SET datetime_logout = NOW() 
                WHERE entry_id = ?
            ");
            $logoutStmt->bind_param("i", $activeSession['entry_id']);
            $logoutStmt->execute();
            $logoutStmt->close();

            echo json_encode([
                'status' => 'checkout',
                'name' => $contact['first_name'] . ' ' . $contact['last_name']
            ]);
        } else {
            // Returning User - Requires details verification on Slide 3
            echo json_encode([
                'status' => 'returning',
                'contact' => $contact
            ]);
        }
        break;

    // 2. Check-In Returning Verified User
    case 'checkin':
        if (!isset($data['contact_id'])) {
            echo json_encode(['error' => 'Missing contact ID for sign-in.']);
            exit;
        }

        $contactId = intval($data['contact_id']);

        // Insert new check-in record
        $stmt = $conn->prepare("
            INSERT INTO logs (id_number, datetime_login) 
            VALUES (?, NOW())
        ");
        
        if (!$stmt) {
            echo json_encode(['error' => 'Database log statement failed.']);
            exit;
        }

        $stmt->bind_param("i", $contactId);
        $stmt->execute();
        $stmt->close();

        // Fetch name for thank you display
        $nameStmt = $conn->prepare("SELECT first_name, last_name FROM contacts WHERE id = ? LIMIT 1");
        $nameStmt->bind_param("i", $contactId);
        $nameStmt->execute();
        $nameRes = $nameStmt->get_result()->fetch_assoc();
        $nameStmt->close();

        echo json_encode([
            'status' => 'success',
            'name' => $nameRes['first_name'] . ' ' . $nameRes['last_name']
        ]);
        break;

    // 3. Register New User & Automatically Check-in
    case 'register':
        // Require fields matching DATABASE_SCHEM.md
        $required = ['first_name', 'last_name', 'barangay', 'city', 'province', 'phone_number', 'email'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                echo json_encode(['error' => 'All address, name, and contact details are required.']);
                exit;
            }
        }

        $firstName = trim($data['first_name']);
        $middleName = isset($data['middle_name']) && !empty(trim($data['middle_name'])) ? trim($data['middle_name']) : null;
        $lastName = trim($data['last_name']);
        $barangay = trim($data['barangay']);
        $city = trim($data['city']);
        $province = trim($data['province']);
        $phoneNumber = trim($data['phone_number']);
        $email = trim($data['email']);
        $uscId = isset($data['usc_id_number']) && !empty(trim($data['usc_id_number'])) ? trim($data['usc_id_number']) : null;

        // Insert new contact
        $stmt = $conn->prepare("
            INSERT INTO contacts (first_name, middle_name, last_name, barangay, city, province, phone_number, email, usc_id_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            echo json_encode(['error' => 'Failed to prepare contact registration statement.']);
            exit;
        }

        $stmt->bind_param("sssssssss", $firstName, $middleName, $lastName, $barangay, $city, $province, $phoneNumber, $email, $uscId);
        
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Failed to register contact record. This contact info might already exist.']);
            $stmt->close();
            exit;
        }

        $newContactId = $stmt->insert_id;
        $stmt->close();

        // Create log check-in record
        $logStmt = $conn->prepare("
            INSERT INTO logs (id_number, datetime_login) 
            VALUES (?, NOW())
        ");
        $logStmt->bind_param("i", $newContactId);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode([
            'status' => 'success',
            'name' => $firstName . ' ' . $lastName
        ]);
        break;

    // 4. Admin Portal Login Authentication
    case 'admin_login':
        if (!isset($data['username']) || !isset($data['password'])) {
            echo json_encode(['error' => 'Username and password are required.']);
            exit;
        }

        $username = trim($data['username']);
        $password = $data['password'];

        $stmt = $conn->prepare("
            SELECT admin_id, name, username, password 
            FROM admin 
            WHERE username = ? 
            LIMIT 1
        ");

        if (!$stmt) {
            echo json_encode(['error' => 'Admin prepare error.']);
            exit;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_name'] = $admin['name'];
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'failed']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Requested action not recognized.']);
        break;
}
