<?php
require_once __DIR__ . '/../security/session.php';
$session = new SessionManager();

$_SESSION['cart'] = [];
session_regenerate_id(true);

header('Location: /pages/cart.php');
exit;