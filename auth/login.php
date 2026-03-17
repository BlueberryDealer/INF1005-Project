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
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/../components/navbar.php"; ?>

<main id="maincontent" class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-card">

      <h1 class="auth-title">Sign In</h1>
      <p class="auth-subtitle">
        Welcome back. Log in to your QUENCH account.
      </p>

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

      <form method="post" action="/auth/login_process.php" autocomplete="off">
        <?= CSRFToken::field('csrf_token') ?>

        <div class="auth-field">
          <label for="email" class="auth-label">Email</label>
          <input maxlength="45" type="email" id="email" name="email" class="auth-input"
            placeholder="you@example.com" required>
        </div>

        <div class="auth-field">
          <label for="password" class="auth-label">Password</label>
          <input type="password" id="password" name="password" class="auth-input"
            placeholder="Enter your password" required>
        </div>

        <button type="submit" class="auth-btn">Sign In</button>
      </form>

      <p class="auth-switch">
        Don't have an account? <a href="/auth/register.php">Create one</a>
      </p>

    </div>
  </div>
</main>

<?php include __DIR__ . "/../components/footer.php"; ?>