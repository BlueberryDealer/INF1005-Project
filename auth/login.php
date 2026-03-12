<?php
include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";

require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/passwordProtection.php';
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title></head>
<body>
  <main class="container"> 

    <h1>Member Login</h1> 
      <p>Existing members log in here. For new members, please go to the <a href="register.php">
        Member Registration page</a>.</p>

    <?php if ($error): ?>
    <div style="color:#900; background:#fee; border:1px solid #f99; padding:8px; margin:8px 0;">
        <?= Sanitizer::escape((string)$error) ?>
    </div>
    <?php endif; ?>
    
    <form method="post" action="/auth/login_process.php" autocomplete="off">
      <?= CSRFToken::field('csrf_token') ?>
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label> 
        <input maxlength="45" type="email" id="email" name="email" class="form-control" 
          placeholder="Enter email">  
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
</body>
</html>
 