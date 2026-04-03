<?php
// -------------------------------------------------------
// admin/order_history.php  –  View all orders (admin only)
// -------------------------------------------------------
require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/sanitization.php';

$orders    = getAllOrdersWithItems();
$pageTitle = 'Order History – Admin';

include __DIR__ . '/../components/header.php';
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main id="maincontent" class="oh-page">
  <div class="container">

    <!-- Header -->
    <div class="oh-header">
      <div>
        <p class="oh-breadcrumb"><a href="/admin/dashboard.php">Dashboard</a> / Order History</p>
        <h1 class="section-title-bold">Order History</h1>
      </div>
      <span class="oh-count"><?= count($orders) ?> order<?= count($orders) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($orders)): ?>
    <!-- Empty State -->
    <div class="oh-empty">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M21 8v13H3V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M1 3h22v5H1z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M10 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
      <h2>No orders yet</h2>
      <p>Orders will appear here once customers start checking out.</p>
    </div>

    <?php else: ?>
    <!-- Search & Filter Bar -->
    <div class="oh-toolbar">
      <input type="text" id="orderSearch" class="oh-search" placeholder="Search by name, email, or order #..." aria-label="Search orders">
    </div>

    <!-- Orders List -->
    <div class="oh-list" id="ordersList">
      <?php foreach ($orders as $order): ?>
        <?php
          $statusMap = [
            'pending'    => 'oh-status--pending',
            'processing' => 'oh-status--processing',
            'shipped'    => 'oh-status--shipped',
            'delivered'  => 'oh-status--delivered',
            'cancelled'  => 'oh-status--cancelled',
          ];
          $statusClass = $statusMap[$order['status']] ?? 'oh-status--pending';
        ?>
        <div class="oh-order" data-search="<?= Sanitizer::escape(strtolower($order['full_name'] . ' ' . $order['email'] . ' #' . $order['id'])) ?>">
          <!-- Order Header (clickable) -->
          <button class="oh-order-header" aria-expanded="false" aria-controls="order-detail-<?= (int)$order['id'] ?>">
            <span class="oh-order-left">
              <span class="oh-order-id">#<?= (int)$order['id'] ?></span>
              <span class="oh-order-customer">
                <span class="oh-customer-name"><?= Sanitizer::escape($order['full_name']) ?></span>
                <span class="oh-customer-email"><?= Sanitizer::escape($order['email']) ?></span>
              </span>
            </span>
            <span class="oh-order-right">
              <span class="oh-order-date">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <?= date('d M Y, g:i A', strtotime($order['created_at'])) ?>
              </span>
              <span class="oh-order-amount">$<?= number_format((float)$order['total_amount'], 2) ?></span>
              <span class="oh-status <?= $statusClass ?>"><?= Sanitizer::escape(ucfirst($order['status'])) ?></span>
              <svg class="oh-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
          </button>

          <!-- Order Detail (expandable) -->
          <div class="oh-order-detail" id="order-detail-<?= (int)$order['id'] ?>">
            <?php if (!empty($order['items'])): ?>
            <table class="oh-items-table" aria-label="Items in order #<?= (int)$order['id'] ?>">
              <thead>
                <tr>
                  <th>Product</th>
                  <th class="text-center">Qty</th>
                  <th class="text-end">Unit Price</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($order['items'] as $item): ?>
                <tr>
                  <td class="oh-item-name"><?= Sanitizer::escape($item['product_name']) ?></td>
                  <td class="text-center"><?= (int)$item['quantity'] ?></td>
                  <td class="text-end">$<?= number_format((float)$item['unit_price'], 2) ?></td>
                  <td class="text-end">$<?= number_format((float)$item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="text-end oh-total-label">Order Total</td>
                  <td class="text-end oh-total-value">$<?= number_format((float)$order['total_amount'], 2) ?></td>
                </tr>
              </tfoot>
            </table>
            <?php else: ?>
            <p class="oh-no-items">No items found for this order.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</main>



<?php include __DIR__ . '/../components/footer.php'; ?>