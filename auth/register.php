<?php
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";

?>
<main class="container py-4">
  <h1>Member Registration</h1> 
  <p> 
      For existing members, please go to the 
      <a href="/auth/login.php">Sign In page</a>. 
  </p> 

  <?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
      <?= Sanitizer::escape((string)$error) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="/auth/register_process.php" autocomplete="off" style="max-width: 540px;">
    <?php echo CSRFToken::field('csrf_token'); ?>
    
    <div class="mb-3">
      <label for="fname" class="form-label">First Name:</label>
      <input maxlength="50" type="text" id="fname" name="fname" class="form-control" placeholder="Enter first name">
    </div>

    <div class="mb-3">
      <label for="lname" class="form-label">Last Name:</label>
      <input required maxlength="50" type="text" id="lname" name="lname" class="form-control" placeholder="Enter last name" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email:</label> 
      <input required maxlength="50" type="email" id="email" name="email" class="form-control" 
        placeholder="Enter email">  
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password:</label>
      <input required type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
    </div>

    <div class="mb-3">
      <label for="password_confirm" class="form-label">Confirm Password:</label>
      <input required type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Confirm password" required>
    </div>

    <div class="mb-3"> 
        <button type="submit" class="btn btn-primary">Submit</button> 
    </div>
  </form>
</main>
<?php include __DIR__ . "/../components/footer.php"; ?>
