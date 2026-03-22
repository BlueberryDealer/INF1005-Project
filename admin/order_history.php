<?php
// -------------------------------------------------------
// admin/order_history.php  –  View all orders (admin only)
// -------------------------------------------------------
require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';

$orders    = getAllOrdersWithItems();
$pageTitle = 'Order History – Admin';
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
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="container my-5" id="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Order History</h1>
        <span class="badge bg-secondary fs-6">
            <?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?>
        </span>
    </div>

    <?php if (empty($orders)): ?>
    <!-- ===== NO ORDERS STATE ===== -->
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 3.5rem; color: #adb5bd;" aria-hidden="true"></i>
        <h2 class="mt-3 h5">No orders yet</h2>
        <p class="text-muted">Orders will appear here once customers start checking out.</p>
    </div>

    <?php else: ?>
    <!-- ===== ORDERS ACCORDION ===== -->
    <div class="accordion" id="ordersAccordion">

        <?php foreach ($orders as $index => $order): ?>
        <div class="accordion-item border mb-2 rounded shadow-sm">

            <!-- ===== ORDER HEADER (always visible) ===== -->
            <h2 class="accordion-header" id="heading-<?= (int)$order['id'] ?>">
                <button class="accordion-button collapsed rounded"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-<?= (int)$order['id'] ?>"
                        aria-expanded="false"
                        aria-controls="collapse-<?= (int)$order['id'] ?>">

                    <div class="d-flex w-100 justify-content-between align-items-center flex-wrap gap-2 pe-3">

                        <!-- Left: Order ID + Customer -->
                        <div>
                            <span class="fw-bold me-2">
                                Order #<?= (int)$order['id'] ?>
                            </span>
                            <span class="text-muted small">
                                <i class="bi bi-person me-1" aria-hidden="true"></i>
                                <?= htmlspecialchars($order['full_name']) ?>
                                &lt;<?= htmlspecialchars($order['email']) ?>&gt;
                            </span>
                        </div>

                        <!-- Right: Date + Total + Status badge -->
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="text-muted small">
                                <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                                <?= htmlspecialchars(date('d M Y, g:i A', strtotime($order['created_at']))) ?>
                            </span>
                            <span class="fw-semibold">
                                $<?= number_format($order['total_amount'], 2) ?>
                            </span>
                            <?php
                            $statusColors = [
                                'pending'    => 'warning text-dark',
                                'processing' => 'info text-dark',
                                'shipped'    => 'primary',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                            ];
                            $badgeClass = $statusColors[$order['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badgeClass ?> text-capitalize">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </div>

                    </div>
                </button>
            </h2>

            <!-- ===== ORDER DETAIL (expandable) ===== -->
            <div id="collapse-<?= (int)$order['id'] ?>"
                 class="accordion-collapse collapse"
                 aria-labelledby="heading-<?= (int)$order['id'] ?>"
                 data-bs-parent="#ordersAccordion">

                <div class="accordion-body pt-0">
                    <hr class="mt-0">

                    <!-- Items table -->
                    <h3 class="h6 text-muted mb-2">Items Ordered</h3>
                    <?php if (!empty($order['items'])): ?>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered mb-0"
                               aria-label="Items in order #<?= (int)$order['id'] ?>">
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
                                    <th colspan="3" class="text-end">Order Total</th>
                                    <th class="text-end">$<?= number_format($order['total_amount'], 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted small">No items found for this order.</p>
                    <?php endif; ?>

                </div>
            </div>

        </div><!-- /accordion-item -->
        <?php endforeach; ?>

    </div><!-- /accordion -->
    <?php endif; ?>

</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>