<?php
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/sanitization.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
  
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register</title></head>
<body>

<?php if ($error): ?>
  <div style="color:#900; background:#fee; border:1px solid #f99; padding:8px; margin:8px 0;">
    <?= Sanitizer::escape((string)$error) ?>
  </div>
<?php endif; ?>

<form method="post" action="/auth/register_process.php" autocomplete="off">
  <?php echo CSRFToken::field('csrf_token'); ?>

  <label>First name <input name="fname"></label><br>
  <label>Last name <input name="lname" required></label><br>
  <label>Email <input name="email" type="email" required></label><br>
  <label>Password <input name="password" type="password" required></label><br>
  <label>Confirm Password <input name="password_confirm" type="password" required></label><br>

  <button type="submit">Register</button>
</form>

</body>
</html>