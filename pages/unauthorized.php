<?php
include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";

require_once __DIR__ .'/../security/session.php';
require_once __DIR__ .'/../security/sanitization.php';

$session = new SessionManager();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
</head>
<body>
    <h1>Access Denied</h1>
    
    <?php if ($session->isAuthenticated()): ?>
        <p>Hello <?php echo Sanitizer::escape($session->getlname()); ?>,</p>
        <p>You do not have permission to access this page.</p>
        <p>This page requires admin access.</p>
        
        <a href="/admin/dashboard.php">Go to Dashboard</a> | 
        <a href="/auth/logout.php">Logout</a>
    <?php else: ?>
        <p>You must be logged in to access this page.</p>
        <a href="/auth/login.php">Go to Login</a>
    <?php endif; ?>
</body>
</html>