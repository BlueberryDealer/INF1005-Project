<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/roleBasedAuth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/sanitization.php';

$session = new SessionManager();
$roles   = new RoleManager($session);

// Require user to log in
$roles->requireAuthenticated();

// Refresh session activity timestamp
$session->refreshSession();