<?php

require_once __DIR__ . '/../config/db_connect.php';

function getStatisticsExcludedProductPatterns(): array
{
    return [
        'test%',
        'demo%',
        'sample%',
    ];
}

function buildStatisticsExclusionClause(string $column): string
{
    $clauses = [];

    foreach (getStatisticsExcludedProductPatterns() as $pattern) {
        $safePattern = addslashes($pattern);
        $clauses[] = "$column NOT LIKE '$safePattern'";
    }

    return implode(' AND ', $clauses);
}

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

function getTopSellingProducts(int $limit = 8): array
{
    $conn = db_connect();
    $limit = max(1, $limit);
    $excludeClause = buildStatisticsExclusionClause('oi.product_name');

    $sql = "
        SELECT
            oi.product_id,
            MAX(oi.product_name) AS product_name,
            SUM(oi.quantity) AS units_sold,
            SUM(oi.subtotal) AS revenue,
            COUNT(DISTINCT oi.order_id) AS order_count
        FROM order_items oi
        INNER JOIN orders o ON o.id = oi.order_id
        WHERE $excludeClause
        GROUP BY oi.product_id
        ORDER BY units_sold DESC, revenue DESC, product_name ASC
        LIMIT $limit
    ";

    $result = $conn->query($sql);
    $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();

    return $products;
}

function getSalesSummary(): array
{
    $conn = db_connect();
    $excludeClause = buildStatisticsExclusionClause('product_name');

    $summary = [
        'total_orders' => 0,
        'total_revenue' => 0.0,
        'units_sold' => 0,
        'top_product' => null,
    ];

    $result = $conn->query("
        SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(total_amount), 0) AS total_revenue
        FROM orders
    ");

    if ($result) {
        $row = $result->fetch_assoc();
        $summary['total_orders'] = (int)($row['total_orders'] ?? 0);
        $summary['total_revenue'] = (float)($row['total_revenue'] ?? 0);
    }

    $unitsResult = $conn->query("
        SELECT COALESCE(SUM(quantity), 0) AS units_sold
        FROM order_items
    ");

    if ($unitsResult) {
        $row = $unitsResult->fetch_assoc();
        $summary['units_sold'] = (int)($row['units_sold'] ?? 0);
    }

    $topResult = $conn->query("
        SELECT
            product_id,
            MAX(product_name) AS product_name,
            SUM(quantity) AS units_sold
        FROM order_items
        WHERE $excludeClause
        GROUP BY product_id
        ORDER BY units_sold DESC, product_name ASC
        LIMIT 1
    ");

    if ($topResult) {
        $topProduct = $topResult->fetch_assoc();
        if ($topProduct) {
            $summary['top_product'] = $topProduct;
        }
    }

    $conn->close();

    return $summary;
}

function getHomepageTopSellingProducts(int $limit = 4): array
{
    $conn = db_connect();
    $limit = max(1, $limit);

    $sql = "
        SELECT
            p.product_id,
            p.name,
            p.description,
            p.image_url,
            p.price,
            p.quantity,
            COALESCE(SUM(oi.quantity), 0) AS units_sold
        FROM products p
        LEFT JOIN order_items oi ON oi.product_id = p.product_id
        GROUP BY p.product_id, p.name, p.description, p.image_url, p.price, p.quantity
        ORDER BY units_sold DESC, p.name ASC
        LIMIT $limit
    ";

    $result = $conn->query($sql);
    $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();

    return $products;
}

function getAllOrdersWithItems(): array
{
    $conn = db_connect();

    // Fetch all orders, newest first
    $result = $conn->query("
        SELECT id, user_id, full_name, email, total_amount, status, created_at
        FROM orders
        ORDER BY created_at DESC
    ");

    $orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    if (empty($orders)) {
        $conn->close();
        return [];
    }

    // Fetch all order items in one query for efficiency
    $orderIds     = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $types        = str_repeat('i', count($orderIds));

    $itemStmt = $conn->prepare("
        SELECT order_id, product_name, unit_price, quantity, subtotal
        FROM order_items
        WHERE order_id IN ($placeholders)
        ORDER BY order_id
    ");

    if ($itemStmt) {
        $itemStmt->bind_param($types, ...$orderIds);
        $itemStmt->execute();
        $itemResult = $itemStmt->get_result();
        $allItems   = $itemResult ? $itemResult->fetch_all(MYSQLI_ASSOC) : [];
        $itemStmt->close();
    } else {
        $allItems = [];
    }

    $conn->close();

    // Group items by order_id
    $itemsByOrder = [];
    foreach ($allItems as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }

    // Attach items to each order
    foreach ($orders as &$order) {
        $order['items'] = $itemsByOrder[$order['id']] ?? [];
    }
    unset($order);

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

function getDailyRevenueTrend(int $days = 30): array
{
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT
            DATE(created_at) AS order_date,
            COUNT(*) AS order_count,
            SUM(total_amount) AS daily_revenue
        FROM orders
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(created_at)
        ORDER BY order_date ASC
    ");

    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->bind_param('i', $days);
    $stmt->execute();
    $result = $stmt->get_result();
    $trend = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();

    return $trend;
}

function getRevenueByProduct(int $limit = 6): array
{
    $conn = db_connect();

    $sql = "
        SELECT
            MAX(oi.product_name) AS product_name,
            SUM(oi.subtotal) AS revenue
        FROM order_items oi
        GROUP BY oi.product_id
        ORDER BY revenue DESC
        LIMIT $limit
    ";

    $result = $conn->query($sql);
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();

    return $data;
}