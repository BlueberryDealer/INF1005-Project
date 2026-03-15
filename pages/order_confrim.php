<?php
// -------------------------------------------------------
// order_confirm.php  –  Order confirmation / summary page
// -------------------------------------------------------

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/auth_guard.php';

$orderId = (int)($_GET['order_id'] ?? 0);

// Security: only allow access if it matches session or URL
if ($orderId <= 0) {
    header('Location: index.php');
    exit;
}

$order = getOrderById($orderId, (int)$_SESSION['user_id']);

if (!$order) {
    // Order doesn't exist or doesn't belong to this user
    header('Location: index.php');
    exit;
}

// Clear the last_order_id from session once page is loaded
unset($_SESSION['last_order_id']);

$pageTitle = 'Order Confirmed – Quench';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="container my-5" id="main-content">

    <!-- ===== SUCCESS BANNER ===== -->
    <div class="text-center mb-5">
        <div class="mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;" aria-hidden="true"></i>
        </div>
        <h1 class="h2">Order Confirmed!</h1>
        <p class="text-muted">
            Thank you, <strong><?= htmlspecialchars($order['full_name']) ?></strong>!
            Your order has been placed successfully.
        </p>
        <p class="text-muted">
            A confirmation will be sent to
            <strong><?= htmlspecialchars($order['email']) ?></strong>.
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- ===== ORDER META ===== -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Order #<?= (int)$order['id'] ?></span>
                    <span class="badge bg-success text-capitalize">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <h2 class="h6 text-muted mb-1">Order Date</h2>
                            <p class="mb-0">
                                <?= htmlspecialchars(
                                    date('d M Y, g:i A', strtotime($order['created_at']))
                                ) ?>
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <h2 class="h6 text-muted mb-1">Order Total</h2>
                            <p class="mb-0 fw-bold">
                                $<?= number_format($order['total_amount'], 2) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== SHIPPING DETAILS ===== -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header fw-semibold">Shipping Address</div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong><?= htmlspecialchars($order['full_name']) ?></strong><br>
                        <?= htmlspecialchars($order['address_line']) ?><br>
                        <?= htmlspecialchars($order['city']) ?>,
                        <?= htmlspecialchars($order['postal_code']) ?><br>
                        <?= htmlspecialchars($order['country']) ?><br>
                        <abbr title="Phone">P:</abbr> <?= htmlspecialchars($order['phone']) ?>
                    </address>
                </div>
            </div>

            <!-- ===== ORDER ITEMS ===== -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header fw-semibold">Items Ordered</div>
                <div class="card-body p-0">
                    <table class="table mb-0" aria-label="Items in order #<?= (int)$order['id'] ?>">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col" class="text-center">Qty</th>
                                <th scope="col" class="text-end">Unit Price</th>
                                <th scope="col" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td class="text-center"><?= (int)$item['quantity'] ?></td>
                                <td class="text-end">$<?= number_format($item['unit_price'], 2) ?></td>
                                <td class="text-end">$<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">$<?= number_format($order['total_amount'], 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- ===== CTA BUTTONS ===== -->
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="products.php" class="btn btn-primary">
                    <i class="bi bi-lightning-fill me-1" aria-hidden="true"></i>
                    Continue Shopping
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house me-1" aria-hidden="true"></i>
                    Back to Home
                </a>
            </div>

        </div>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>