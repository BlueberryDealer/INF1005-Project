<?php

require_once __DIR__ . '/../config/db_connect.php';

function createOrder(int $userId, array $shipping, array $cartItems, float $total): int|false
{
    $conn = db_connect();

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            INSERT INTO orders
                (user_id, full_name, email, phone, address_line, city, postal_code, country, total_amount)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new RuntimeException('Failed to prepare order insert.');
        }

        $stmt->bind_param(
            'isssssssd',
            $userId,
            $shipping['full_name'],
            $shipping['email'],
            $shipping['phone'],
            $shipping['address_line'],
            $shipping['city'],
            $shipping['postal_code'],
            $shipping['country'],
            $total
        );
        $stmt->execute();
        $orderId = (int)$conn->insert_id;
        $stmt->close();

        $itemStmt = $conn->prepare("
            INSERT INTO order_items
                (order_id, product_id, product_name, unit_price, quantity, subtotal)
            VALUES
                (?, ?, ?, ?, ?, ?)
        ");

        if (!$itemStmt) {
            throw new RuntimeException('Failed to prepare order item insert.');
        }

        foreach ($cartItems as $item) {
            $productId = (int)$item['product_id'];
            $productName = (string)$item['name'];
            $unitPrice = (float)$item['price'];
            $quantity = (int)$item['quantity'];
            $subtotal = $unitPrice * $quantity;

            $itemStmt->bind_param('iisdid', $orderId, $productId, $productName, $unitPrice, $quantity, $subtotal);
            $itemStmt->execute();
        }

        $itemStmt->close();
        $conn->commit();
        $conn->close();

        return $orderId;
    } catch (Throwable $e) {
        $conn->rollback();
        $conn->close();
        error_log('createOrder failed: ' . $e->getMessage());

        return false;
    }
}

function getOrderById(int $orderId, int $userId): ?array
{
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT * FROM orders
        WHERE id = ? AND user_id = ?
    ");

    if (!$stmt) {
        $conn->close();
        return null;
    }

    $stmt->bind_param('ii', $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$order) {
        $conn->close();
        return null;
    }

    $itemStmt = $conn->prepare("
        SELECT * FROM order_items
        WHERE order_id = ?
    ");

    if (!$itemStmt) {
        $conn->close();
        return null;
    }

    $itemStmt->bind_param('i', $orderId);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();
    $order['items'] = $itemResult ? $itemResult->fetch_all(MYSQLI_ASSOC) : [];
    $itemStmt->close();
    $conn->close();

    return $order;
}

function getOrdersByUserId(int $userId): array
{
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT id, total_amount, status, created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC, id DESC
    ");

    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();

    return $orders;
}

function getProductsByIds(array $ids): array
{
    if (empty($ids)) {
        return [];
    }

    $conn = db_connect();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("
        SELECT product_id, name, price, image_url
        FROM products
        WHERE product_id IN ($placeholders)
    ");

    if (!$stmt) {
        $conn->close();
        return [];
    }

    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
    $conn->close();

    return $products;
}
