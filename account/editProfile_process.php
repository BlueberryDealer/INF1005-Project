<?php
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

function fail(string $message): never {
    $_SESSION['flash_error'] = $message;
    header('Location: /account/edit_profile.php');
    exit;
}

if (!$session->isAuthenticated()) {
    $_SESSION['flash_error'] = 'Please log in.';
    header('Location: /auth/login.php');
    exit;
}

$session->refreshSession();

$userId = $session->getUserId();
if (!$userId) {
    $_SESSION['flash_error'] = 'Please log in again.';
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// 1. CSRF check (consume token single-use)
if (!CSRFToken::validate($_POST['csrf_token'] ?? '', true)) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

// 2. Validate inputs
$validator = new Sanitizer($_POST);
$ok = $validator->validate([
    'fname' => 'fname|min:0|max:50',
    'lname' => 'required|lname|min:1|max:50',
    'email' => 'required|email',
]);

if (!$ok) {
    fail($validator->firstError() ?? 'Please check your input.');
}

// 3. Sanitize
$fname = Sanitizer::sanitizeString((string)($_POST['fname'] ?? ''));
$lname = Sanitizer::sanitizeString((string)$_POST['lname']);
$email = Sanitizer::sanitizeEmail((string)$_POST['email']);

// 4. Connect to DB
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

// 5. Check if email exists (excluding current user)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
if (!$stmt) { $conn->close(); fail('Database error.'); }
$stmt->bind_param("si", $email, $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    fail('Email already taken.');
}
$stmt->close();

// 6. Update
$stmt = $conn->prepare("UPDATE users SET fname = ?, lname = ?, email = ? WHERE id = ? LIMIT 1");
if (!$stmt) { $conn->close(); fail('Database error.'); }
$stmt->bind_param("sssi", $fname, $lname, $email, $userId);

if (!$stmt->execute()) {
    error_log("Execute failed: ({$stmt->errno}) {$stmt->error}");
    $stmt->close();
    $conn->close();
    fail('Profile update failed. Please try again.');
}

$stmt->close();
$conn->close();

$_SESSION['flash_success'] = 'Profile updated successfully!';
header('Location: /account/userProfile.php');
exit;