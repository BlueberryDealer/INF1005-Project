<?php
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/session.php';
$session = new SessionManager();

header('Content-Type: application/json');

$items = [];
$total = 0.0;
$count = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $products = getProductsByIds($productIds);

    $productMap = [];
    foreach ($products as $p) {
        $productMap[$p['product_id']] = $p;
    }

    foreach ($_SESSION['cart'] as $pid => $qty) {
        if (!isset($productMap[$pid])) continue;

        $p = $productMap[$pid];
        $subtotal = $p['price'] * $qty;
        $total += $subtotal;
        $count += $qty;

        $items[] = [
            'product_id' => (int)$p['product_id'],
            'name' => $p['name'],
            'price' => (float)$p['price'],
            'image' => '/images/' . ltrim((string)$p['image_url'], '/'),
            'quantity' => (int)$qty,
            'subtotal' => $subtotal
        ];
    }
}

echo json_encode([
    'success' => true,
    'count' => $count,
    'total' => $total,
    'items' => $items
]);
exit;
