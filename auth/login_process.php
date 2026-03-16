<?php
require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/passwordProtection.php';
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../config/db_connect.php';

$session = new SessionManager();

function fail(string $message): never {
    $_SESSION['flash_error'] = $message;
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!CSRFToken::validate($_POST['csrf_token'] ?? '', true)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    // Validate minimal fields
    $validator = new Sanitizer($_POST);
    $ok = $validator->validate([
        'email' => 'required|min:3|max:100',
        'password' => 'required|min:8',
    ]);

    if (!$ok) {
        fail($validator->firstError() ?? 'Please check your input.');
    }

    $email = Sanitizer::sanitizeEmail((string)$_POST['email']);
    $password  = (string)$_POST['password'];

    // Connect to DB
    try {
        $conn = db_connect();
    } catch (RuntimeException $e) {
        fail($e->getMessage());
    }

    // Prepare + execute
    $stmt = $conn->prepare("SELECT id, fname, lname, email, password_hash, role FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        $conn->close();
        fail('Database error. Please try again.');
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();             
    $user = $result ? $result->fetch_assoc() : null;

    $stmt->close();
    $conn->close();

    // Check user exists
    if (!$user) {
        fail("Invalid email or password.");
    }

    // Verify password
    if (!PasswordManager::verify($password, (string)$user['password_hash'])) {
        fail("Invalid email or password.");
    }

    // Create session 
    $session->createSession(
        (int)$user['id'],
        (string)$user['lname'],
        (string)$user['role'],
        (string)$user['email']
    );

    // Regenerate csrf after login
    CSRFToken::regenerate();

    // Redirect based on role
    $role = strtolower((string)$user['role']);
    header('Location: ' . ($role === 'admin' ? '/admin/dashboard.php' : '/pages/products.php'));
    exit;
}
?>
