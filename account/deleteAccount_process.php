<?php
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../config/db_connect.php';

$session = new SessionManager();

function fail(string $message): never {
    $_SESSION['flash_error'] = $message;
    header('Location: /account/userProfile.php');
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

// CSRF check 
if (!CSRFToken::validate($_POST['csrf_token'] ?? '', true)) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

// DB connect
try {
    $conn = db_connect();
} catch (RuntimeException $e) {
    fail($e->getMessage());
}

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ? LIMIT 1");
if (!$stmt) { $conn->close(); fail('Database error.'); }
$stmt->bind_param("i", $userId);

if (!$stmt->execute()) {
    error_log("Execute failed: ({$stmt->errno}) {$stmt->error}");
    $stmt->close();
    $conn->close();
    fail('Account deletion failed. Please try again.');
}

$stmt->close();
$conn->close();

// This clears the whole session (including cart)
$session->logout();

// Redirect with flag (since session is destroyed)
header('Location: /auth/login.php?deleted=1');
exit;
