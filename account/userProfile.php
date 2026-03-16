<?php
require_once __DIR__ . '/../security/auth_guard.php';
require_once __DIR__ . '/../config/db_connect.php';

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

try {
    $conn = db_connect();
} catch (RuntimeException $e) {
    http_response_code(500);
    exit($e->getMessage());
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

include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";
?>
<main class="container py-4">
  <h1>My Profile</h1>

  <?php if ($success): ?>
    <div class="alert alert-success" role="alert">
      <?= Sanitizer::escape((string)$success) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
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

<?php include __DIR__ . "/../components/footer.php"; ?>
