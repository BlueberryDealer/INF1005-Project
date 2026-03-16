<?php
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/passwordProtection.php';
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$success = null;
if (isset($_GET['registered'])) {
    $success = 'Registration successful. Please log in.';
} elseif (isset($_GET['deleted'])) {
    $success = 'Your account has been deleted.';
}

include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";
?>
<main class="container py-4">
  <h1>Member Login</h1>
  <p>Existing members log in here. For new members, please go to the <a href="/auth/register.php">Member Registration page</a>.</p>

  <?php if ($success): ?>
    <div class="alert alert-success" role="alert">
      <?= Sanitizer::escape($success) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
      <?= Sanitizer::escape((string)$error) ?>
    </div>
  <?php endif; ?>
  
  <form method="post" action="/auth/login_process.php" autocomplete="off" style="max-width: 540px;">
    <?= CSRFToken::field('csrf_token') ?>
    <div class="mb-3">
      <label for="email" class="form-label">Email:</label>
      <input maxlength="45" type="email" id="email" name="email" class="form-control"
        placeholder="Enter email" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password:</label>
      <input type="password" id="password" name="password" class="form-control"
        placeholder="Enter password" required>
    </div>

    <div class="mb-3">
      <button type="submit" class="btn btn-primary">Submit</button>
    </div>
  </form>
</main>
<?php include __DIR__ . "/../components/footer.php"; ?>
