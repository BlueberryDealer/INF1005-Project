<?php
require_once __DIR__ . '/../config/db_connect.php';

function validateCoupon(string $code, string $userEmail = ''): array
{
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT coupon_id, code, discount_percent
        FROM coupons
        WHERE code = ? AND is_active = 1
    ");

    if (!$stmt) {
        $conn->close();
        return ['valid' => false, 'message' => 'Server error.'];
    }

    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $coupon = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$coupon) {
        $conn->close();
        return ['valid' => false, 'message' => 'Invalid or expired coupon code.'];
    }

    // Check if user is a newsletter subscriber
    if ($userEmail !== '') {
        $subStmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $subStmt->bind_param('s', $userEmail);
        $subStmt->execute();
        $subStmt->store_result();
        $isSubscribed = $subStmt->num_rows > 0;
        $subStmt->close();

        if (!$isSubscribed) {
            $conn->close();
            return ['valid' => false, 'message' => 'Invalid or expired coupon code.'];
        }
    }

    $conn->close();

    return [
        'valid' => true,
        'code' => $coupon['code'],
        'discount_percent' => (float) $coupon['discount_percent'],
        'message' => $coupon['discount_percent'] . '% discount applied!'
    ];
}