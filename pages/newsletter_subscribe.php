<?php
require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../models/newsletter_model.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email.']);
    exit;
}

$result = subscribeNewsletter($email);
echo json_encode($result);