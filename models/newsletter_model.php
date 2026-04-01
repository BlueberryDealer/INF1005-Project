<?php
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/mailer.php';

function subscribeNewsletter(string $email, string $source = 'footer'): array
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

        // Send different emails based on source
        if ($source === 'popup') {
            sendNewsletterWithCode($email);
            return ['success' => true, 'message' => 'Check your inbox for your discount code!'];
        } else {
            sendNewsletterConfirmation($email);
            return ['success' => true, 'message' => 'Thanks for subscribing! Check your inbox for confirmation.'];
        }
    }

    $stmt->close();
    $conn->close();
    return ['success' => false, 'message' => 'Something went wrong. Try again.'];
}