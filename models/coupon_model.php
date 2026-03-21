<?php
require_once __DIR__ . '/../config/db_connect.php';

function validateCoupon(string $code): array
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
    $conn->close();

    if ($coupon) {
        return [
            'valid' => true,
            'code' => $coupon['code'],
            'discount_percent' => (float) $coupon['discount_percent'],
            'message' => $coupon['discount_percent'] . '% discount applied!'
        ];
    }

    return ['valid' => false, 'message' => 'Invalid or expired coupon code.'];
}