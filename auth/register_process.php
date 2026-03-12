<?php
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/passwordProtection.php';
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

function fail(string $message): never {
    $_SESSION['flash_error'] = $message;
    header('Location: /auth/register.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF check (consume token for single-use)
    if (!CSRFToken::validate($_POST['csrf_token'] ?? '', true)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    // 2 Validate inputs (with rules)
    $validator = new Sanitizer($_POST);
    $ok = $validator->validate([
        'fname' => 'fname|min:0|max:50',
        'lname' => 'required|lname|min:1|max:50',
        'email' => 'required|email',
        'password' => 'required|password|min:8',
        'password_confirm' => 'required|match:password',
    ]);

    if (!$ok) {
        fail($validator->firstError() ?? 'Please check your input.');
    }

    // 3. Sanitize data before storing
    $fname = Sanitizer::sanitizeString((string)$_POST['fname']);
    $lname = Sanitizer::sanitizeString((string)$_POST['lname']);
    $email = Sanitizer::sanitizeEmail((string)$_POST['email']);

    // 4. Hash password 
    $hashedPassword = PasswordManager::hash((string)$_POST['password']);
    if ($hashedPassword === false) {
        http_response_code(500);
        exit('Password hashing failed');
    }

    // 5. Connect to DB 
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        fail('Failed to read database config file.');
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        fail('Database connection failed: ' . $conn->connect_error);
    }

    // 6. Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) { $conn->close(); fail('Database error.'); }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        fail('Email already taken.');
    }
    $stmt->close();

    // 7. Insert new user
    $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password_hash, role) VALUES (?, ?, ?, ?, 'user')");
    if (!$stmt) { $conn->close(); fail('Database error.'); }
    $stmt->bind_param("ssss", $fname, $lname, $email, $hashedPassword);

    if (!$stmt->execute()) {
        error_log("Execute failed: ({$stmt->errno}) {$stmt->error}");
        $stmt->close();
        $conn->close();
        fail('Registration failed. Please try again.');
    }

    $stmt->close();
    $conn->close();

    // Redirect after success
    $_SESSION['flash_success'] = 'Registration successful!';
    header('Location: /auth/login.php?registered=1');
    exit;
}
?>