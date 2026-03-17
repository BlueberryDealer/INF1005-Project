<?php
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

include __DIR__ . "/../components/header.php";
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/../components/navbar.php"; ?>

<main id="maincontent" class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-card">

      <h1 class="auth-title">Create Account</h1>
      <p class="auth-subtitle">
        Join QUENCH and start shopping your favorite drinks.
      </p>

      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
          <?= Sanitizer::escape((string)$error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/auth/register_process.php" autocomplete="off">
        <?php echo CSRFToken::field('csrf_token'); ?>

        <div class="auth-row">
          <div class="auth-field">
            <label for="fname" class="auth-label">First Name</label>
            <input maxlength="50" type="text" id="fname" name="fname" class="auth-input"
              placeholder="John" required>
          </div>

          <div class="auth-field">
            <label for="lname" class="auth-label">Last Name</label>
            <input maxlength="50" type="text" id="lname" name="lname" class="auth-input"
              placeholder="Doe" required>
          </div>
        </div>

        <div class="auth-field">
          <label for="email" class="auth-label">Email</label>
          <input maxlength="50" type="email" id="email" name="email" class="auth-input"
            placeholder="you@example.com" required>
        </div>

        <div class="auth-field">
          <label for="password" class="auth-label">Password</label>
          <input type="password" id="password" name="password" class="auth-input"
            placeholder="Create a password" required>
        </div>

        <div class="auth-field">
          <label for="password_confirm" class="auth-label">Confirm Password</label>
          <input type="password" id="password_confirm" name="password_confirm" class="auth-input"
            placeholder="Confirm your password" required>
        </div>

        <button type="submit" class="auth-btn">Create Account</button>
      </form>

      <p class="auth-switch">
        Already have an account? <a href="/auth/login.php">Sign in</a>
      </p>

    </div>
  </div>
</main>

<?php include __DIR__ . "/../components/footer.php"; ?>