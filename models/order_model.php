<?php
// -------------------------------------------------------
// models/order_model.php
// Handles all DB operations for orders and order_items.
// -------------------------------------------------------

require_once __DIR__ . '/../config/db_connect.php';

/**
 * Creates a new order and its line items in a single transaction.
 *
 * @param int   $userId   Logged-in user's ID from $_SESSION['user_id']
 * @param array $shipping Validated shipping fields from checkout form
 * @param array $cartItems Array of cart rows (each with product details)
 * @param float $total    Grand total of the order
 * @return int|false      The new order ID on success, false on failure
 */
function createOrder(int $userId, array $shipping, array $cartItems, float $total): int|false
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // 1. Insert the order header
        $stmt = $pdo->prepare("
            INSERT INTO orders
                (user_id, full_name, email, phone, address_line, city, postal_code, country, total_amount)
            VALUES
                (:user_id, :full_name, :email, :phone, :address_line, :city, :postal_code, :country, :total_amount)
        ");
        $stmt->execute([
            ':user_id'      => $userId,
            ':full_name'    => $shipping['full_name'],
            ':email'        => $shipping['email'],
            ':phone'        => $shipping['phone'],
            ':address_line' => $shipping['address_line'],
            ':city'         => $shipping['city'],
            ':postal_code'  => $shipping['postal_code'],
            ':country'      => $shipping['country'],
            ':total_amount' => $total,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        // 2. Insert each order line item
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items
                (order_id, product_id, product_name, unit_price, quantity, subtotal)
            VALUES
                (:order_id, :product_id, :product_name, :unit_price, :quantity, :subtotal)
        ");

        foreach ($cartItems as $item) {
            $itemStmt->execute([
                ':order_id'     => $orderId,
                ':product_id'   => $item['product_id'],
                ':product_name' => $item['name'],
                ':unit_price'   => $item['price'],
                ':quantity'     => $item['quantity'],
                ':subtotal'     => $item['price'] * $item['quantity'],
            ]);
        }

        $pdo->commit();
        return $orderId;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('createOrder failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Fetches a single order with all its line items.
 *
 * @param int $orderId
 * @param int $userId  Used to ensure users can only see their own orders
 * @return array|null
 */
function getOrderById(int $orderId, int $userId): ?array
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM orders
        WHERE id = :order_id AND user_id = :user_id
    ");
    $stmt->execute([':order_id' => $orderId, ':user_id' => $userId]);
    $order = $stmt->fetch();

    if (!$order) return null;

    $itemStmt = $pdo->prepare("
        SELECT * FROM order_items WHERE order_id = :order_id
    ");
    $itemStmt->execute([':order_id' => $orderId]);
    $order['items'] = $itemStmt->fetchAll();

    return $order;
}

/**
 * Fetches product rows from the DB for all product IDs in the session cart.
 * Returns only products that actually exist.
 *
 * @param array $productIds  Array of product IDs
 * @return array
 */
function getProductsByIds(array $ids): array {
    global $conn;

    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("
        SELECT product_id,
               name,
               price,
               image_url
        FROM products
        WHERE product_id IN ($placeholders)
    ");

    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);

    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();

    return $products;
}