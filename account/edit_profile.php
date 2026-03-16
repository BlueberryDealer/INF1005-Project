<?php
include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";

require_once __DIR__ . '/../security/auth_guard.php';

$session = new SessionManager();

if (!$session->isAuthenticated()) {
    $_SESSION['flash_error'] = 'Please log in to edit your profile.';
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

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

// Connect to DB
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

// Load current user data
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
    // Session says logged in, but DB row missing
    $session->logout();
    $_SESSION['flash_error'] = 'Account not found. Please log in again.';
    header('Location: /auth/login.php');
    exit;
}

$fname = (string)($user['fname'] ?? ''); // optional -> may be empty
$lname = (string)($user['lname'] ?? '');
$email = (string)($user['email'] ?? '');
?>

<!doctype html>
<html>
<head><meta charset="utf-8"><title>Edit Profile</title></head>
<body>
  <main class="container">
    <h1>Edit Profile</h1>

    <?php if ($error): ?>
      <div style="color:#900; background:#fee; border:1px solid #f99; padding:8px; margin:8px 0;">
        <?= Sanitizer::escape((string)$error) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="/account/editProfile_process.php" autocomplete="off">
      <?= CSRFToken::field('csrf_token') ?>

      <div class="mb-3">
        <label for="fname" class="form-label">First Name (optional)</label>
        <input maxlength="50" type="text" id="fname" name="fname" class="form-control"
               value="<?= Sanitizer::escape($fname) ?>">
      </div>

      <div class="mb-3">
        <label for="lname" class="form-label">Last Name</label>
        <input maxlength="50" type="text" id="lname" name="lname" class="form-control"
               value="<?= Sanitizer::escape($lname) ?>" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input maxlength="45" type="email" id="email" name="email" class="form-control"
               value="<?= Sanitizer::escape($email) ?>" required>
      </div>

      <div class="mb-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save</button>
        <a class="btn btn-secondary" href="/account/userProfile.php">Cancel</a>
      </div>
    </form>
  </main>
</body>
</html>

<?php include __DIR__ . "/../components/footer.php"; ?>