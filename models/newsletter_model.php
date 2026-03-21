<?php
require_once __DIR__ . '/../config/db_connect.php';

function subscribeNewsletter(string $email): array
{
    $conn = db_connect();

    // Check if already subscribed
    $stmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'You\'re already subscribed!'];
    }
    $stmt->close();

    // Insert
    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->bind_param('s', $email);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Thanks for subscribing!'];
    }

    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'Something went wrong. Try again.'];
}