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

    // Check if user is a newsletter subscriber and hasn't already used a coupon
    if ($userEmail !== '') {
        $subStmt = $conn->prepare("SELECT id, coupon_redeemed FROM newsletter_subscribers WHERE email = ?");
        $subStmt->bind_param('s', $userEmail);
        $subStmt->execute();
        $subResult = $subStmt->get_result();
        $subscriber = $subResult ? $subResult->fetch_assoc() : null;
        $subStmt->close();

        if (!$subscriber) {
            $conn->close();
            return ['valid' => false, 'message' => 'Invalid or expired coupon code.'];
        }

        if ((int)$subscriber['coupon_redeemed'] === 1) {
            $conn->close();
            return ['valid' => false, 'message' => 'This coupon has already been used.'];
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

function markCouponRedeemed(string $email): void
{
    $conn = db_connect();
    $stmt = $conn->prepare("UPDATE newsletter_subscribers SET coupon_redeemed = 1 WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
    }
    $conn->close();
}