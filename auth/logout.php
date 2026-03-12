<?php
require_once __DIR__ . '/../security/session.php';

$session = new SessionManager();
$session->logout();

header('Location: /auth/login.php');
exit;
?>