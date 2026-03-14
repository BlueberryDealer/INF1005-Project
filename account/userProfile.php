<?php
include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";

require_once __DIR__ . '/../security/auth_guard.php';

$session = new SessionManager();

if (!$session->isAuthenticated()) {
    $_SESSION['flash_error'] = 'Please log in to view your profile.';
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

// flash messages
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// DB connect
$config = parse_ini_file('/var/www/private/db-config.ini');
if (!$config) {
    http_response_code(500);
    exit('Failed to read database config file.');
}

$conn = new mysqli(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

if ($conn->connect_error) {
    http_response_code(500);
    exit('Database connection failed.');
}

$stmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE id = ? LIMIT 1");
if (!$stmt) {
    $conn->close();
    http_response_code(500);
    exit('Database error.');
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;

$stmt->close();
$conn->close();

if (!$user) {
    $session->logout();
    $_SESSION['flash_error'] = 'Account not found. Please log in again.';
    header('Location: /auth/login.php');
    exit;
}

$fname = (string)($user['fname'] ?? ''); // may be empty
$lname = (string)($user['lname'] ?? '');
$email = (string)($user['email'] ?? '');
?>

<!doctype html>
<html>
<head><meta charset="utf-8"><title>My Profile</title></head>
<body>
  <main class="container">
    <h1>My Profile</h1>

    <?php if ($success): ?>
      <div style="color:#060; background:#efe; border:1px solid #9f9; padding:8px; margin:8px 0;">
        <?= Sanitizer::escape((string)$success) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div style="color:#900; background:#fee; border:1px solid #f99; padding:8px; margin:8px 0;">
        <?= Sanitizer::escape((string)$error) ?>
      </div>
    <?php endif; ?>

    <div class="card" style="max-width: 720px;">
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">First name</dt>
          <dd class="col-sm-9">
            <?= trim($fname) !== '' ? Sanitizer::escape($fname) : 'N/A' ?>
          </dd>

          <dt class="col-sm-3">Last name</dt>
          <dd class="col-sm-9"><?= Sanitizer::escape($lname) ?></dd>

          <dt class="col-sm-3">Email</dt>
          <dd class="col-sm-9"><?= Sanitizer::escape($email) ?></dd>
        </dl>

        <div class="mt-3 d-flex gap-2">
        <a class="btn btn-primary" href="/account/edit_profile.php">Edit Profile</a>

        <form method="post" action="/account/deleteAccount_process.php" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
          <?= CSRFToken::field('csrf_token') ?>
          <button type="submit" class="btn btn-danger">Delete Account</button>
        </form>
        </div>
      </div>
    </div>
  </main>
</body>
</html>

<?php include __DIR__ . "/../components/footer.php"; ?>