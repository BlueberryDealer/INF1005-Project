<?php

require_once __DIR__ .'/../security/session.php';
require_once __DIR__ .'/../security/sanitization.php';

$session = new SessionManager();
include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";

?>
<main class="container py-4">
    <h1>Access Denied</h1>
    
    <?php if ($session->isAuthenticated()): ?>
        <p>Hello <?= Sanitizer::escape((string)$session->getlname()); ?>,</p>
        <p>You do not have permission to access this page.</p>
        <p>This page requires admin access.</p>
        
        <a href="/admin/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        <a href="/auth/logout.php" class="btn btn-outline-secondary">Logout</a>
    <?php else: ?>
        <p>You must be logged in to access this page.</p>
        <a href="/auth/login.php" class="btn btn-primary">Go to Login</a>
    <?php endif; ?>
<<<<<<< Updated upstream
</body>
</html>
<?php include __DIR__ . "/components/footer.php"; ?>
=======
</main>
<?php include __DIR__ . "/../components/footer.php"; ?>
>>>>>>> Stashed changes
