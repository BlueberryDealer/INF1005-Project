<?php
require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/sanitization.php';

$errorMsg = '';
$products = [];
$recentOrders = [];
$kpi = [
    'total_revenue' => 0,
    'total_orders' => 0,
    'total_customers' => 0,
    'total_products' => 0,
    'low_stock' => 0,
    'out_of_stock' => 0,
    'subscribers' => 0,
];

try {
    $conn = db_connect();

    // KPI: Revenue & orders
    $r = $conn->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(total_amount),0) AS rev FROM orders");
    if ($r) { $row = $r->fetch_assoc(); $kpi['total_orders'] = (int)$row['cnt']; $kpi['total_revenue'] = (float)$row['rev']; }

    // KPI: Customers
    $r = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'user'");
    if ($r) { $kpi['total_customers'] = (int)$r->fetch_assoc()['cnt']; }

    // KPI: Products & stock
    $r = $conn->query("SELECT COUNT(*) AS total, SUM(quantity <= 0) AS oos, SUM(quantity > 0 AND quantity <= 5) AS low FROM products");
    if ($r) { $row = $r->fetch_assoc(); $kpi['total_products'] = (int)$row['total']; $kpi['out_of_stock'] = (int)$row['oos']; $kpi['low_stock'] = (int)$row['low']; }

    // KPI: Newsletter subscribers
    $r = $conn->query("SELECT COUNT(*) AS cnt FROM newsletter_subscribers");
    if ($r) { $kpi['subscribers'] = (int)$r->fetch_assoc()['cnt']; }

    // Recent orders (last 10)
    $r = $conn->query("
        SELECT o.id, o.full_name, o.email, o.total_amount, o.status, o.created_at,
               GROUP_CONCAT(oi.product_name SEPARATOR ', ') AS items
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    if ($r) { $recentOrders = $r->fetch_all(MYSQLI_ASSOC); }

    // All products for inventory table
    $r = $conn->query("SELECT product_id, name, price, description, image_url, quantity, category FROM products ORDER BY name");
    if ($r) { $products = $r->fetch_all(MYSQLI_ASSOC); }

    $conn->close();
} catch (Throwable $e) {
    $errorMsg = 'Unable to load dashboard data: ' . $e->getMessage();
}

$adminName = $session->getlname() ?? 'Admin';

include __DIR__ . '/../components/header.php';
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main id="maincontent" class="dash-page">
  <div class="container">

    <!-- Header -->
    <div class="dash-header">
      <div>
        <p class="dash-greeting">Welcome back, <strong><?= Sanitizer::escape($adminName) ?></strong></p>
        <h1 class="section-title-bold">Dashboard</h1>
      </div>
      <div class="dash-actions">
        <a href="/admin/add_product.php" class="dash-btn dash-btn--primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
          Add Product
        </a>
        <a href="/admin/statistics.php" class="dash-btn dash-btn--outline">Statistics</a>
        <a href="/pages/products.php" class="dash-btn dash-btn--outline">View Store</a>
      </div>
    </div>

    <?php if ($errorMsg): ?>
      <div class="alert alert-danger" role="alert"><?= Sanitizer::escape($errorMsg) ?></div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="dash-kpi-grid">
      <div class="dash-kpi">
        <div class="dash-kpi-icon dash-kpi-icon--cyan">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-kpi-body">
          <span class="dash-kpi-label">Total Revenue</span>
          <span class="dash-kpi-value">$<?= number_format($kpi['total_revenue'], 2) ?></span>
        </div>
      </div>

      <div class="dash-kpi">
        <div class="dash-kpi-icon dash-kpi-icon--blue">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.8"/></svg>
        </div>
        <div class="dash-kpi-body">
          <span class="dash-kpi-label">Orders</span>
          <span class="dash-kpi-value"><?= number_format($kpi['total_orders']) ?></span>
        </div>
      </div>

      <div class="dash-kpi">
        <div class="dash-kpi-icon dash-kpi-icon--green">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-kpi-body">
          <span class="dash-kpi-label">Customers</span>
          <span class="dash-kpi-value"><?= number_format($kpi['total_customers']) ?></span>
        </div>
      </div>

      <div class="dash-kpi">
        <div class="dash-kpi-icon dash-kpi-icon--orange">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="4" y1="22" x2="4" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div class="dash-kpi-body">
          <span class="dash-kpi-label">Subscribers</span>
          <span class="dash-kpi-value"><?= number_format($kpi['subscribers']) ?></span>
        </div>
      </div>
    </div>

    <!-- Stock Alerts -->
    <?php if ($kpi['out_of_stock'] > 0 || $kpi['low_stock'] > 0): ?>
    <div class="dash-alert-bar">
      <?php if ($kpi['out_of_stock'] > 0): ?>
        <span class="dash-alert dash-alert--danger">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <?= $kpi['out_of_stock'] ?> product<?= $kpi['out_of_stock'] > 1 ? 's' : '' ?> out of stock
        </span>
      <?php endif; ?>
      <?php if ($kpi['low_stock'] > 0): ?>
        <span class="dash-alert dash-alert--warn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <?= $kpi['low_stock'] ?> product<?= $kpi['low_stock'] > 1 ? 's' : '' ?> low stock (&le;5)
        </span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Two Column: Recent Orders + Quick Links -->
    <div class="dash-row">

      <!-- Recent Orders -->
      <div class="dash-card dash-card--orders">
        <div class="dash-card-header">
          <h2 class="dash-card-title">Recent Orders</h2>
          <a href="/admin/order_history.php" class="dash-card-link">View All</a>
        </div>

        <?php if (empty($recentOrders)): ?>
          <div class="dash-empty">No orders yet. Orders will appear here once customers checkout.</div>
        <?php else: ?>
          <div class="dash-orders-wrap" tabindex="0" role="region" aria-label="Recent orders">
            <table class="dash-orders-table">
              <thead>
                <tr>
                  <th>Order</th>
                  <th>Customer</th>
                  <th>Items</th>
                  <th class="text-end">Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentOrders as $order): ?>
                  <?php
                    $statusMap = [
                      'pending' => 'dash-status--pending',
                      'processing' => 'dash-status--processing',
                      'shipped' => 'dash-status--shipped',
                      'delivered' => 'dash-status--delivered',
                      'cancelled' => 'dash-status--cancelled',
                    ];
                    $statusClass = $statusMap[$order['status']] ?? 'dash-status--pending';
                  ?>
                  <tr>
                    <td class="dash-order-id">#<?= (int)$order['id'] ?></td>
                    <td>
                      <div class="dash-customer-name"><?= Sanitizer::escape($order['full_name']) ?></div>
                      <div class="dash-customer-email"><?= Sanitizer::escape($order['email']) ?></div>
                    </td>
                    <td class="dash-order-items"><?= Sanitizer::escape(mb_strimwidth($order['items'] ?? '', 0, 40, '...')) ?></td>
                    <td class="text-end dash-order-amount">$<?= number_format((float)$order['total_amount'], 2) ?></td>
                    <td><span class="dash-status <?= $statusClass ?>"><?= Sanitizer::escape(ucfirst($order['status'])) ?></span></td>
                    <td class="dash-order-date"><?= date('d M, g:i A', strtotime($order['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Quick Actions Sidebar -->
      <div class="dash-sidebar">
        <div class="dash-card">
          <h2 class="dash-card-title" style="padding:20px 20px 0;">Quick Actions</h2>
          <div class="dash-quick-links">
            <a href="/admin/add_product.php" class="dash-quick-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              Add New Product
            </a>
            <a href="/admin/order_history.php" class="dash-quick-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Order History
            </a>
            <a href="/admin/statistics.php" class="dash-quick-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 20V10M12 20V4M6 20v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Sales Statistics
            </a>
            <a href="/pages/products.php" class="dash-quick-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
              View Store Front
            </a>
          </div>
        </div>

        <!-- Stock Summary Mini Card -->
        <div class="dash-card dash-stock-summary">
          <h2 class="dash-card-title" style="padding:20px 20px 12px;">Inventory Snapshot</h2>
          <div class="dash-stock-rows">
            <div class="dash-stock-row">
              <span class="dash-stock-dot dash-stock-dot--green"></span>
              <span>In Stock</span>
              <strong><?= $kpi['total_products'] - $kpi['out_of_stock'] - $kpi['low_stock'] ?></strong>
            </div>
            <div class="dash-stock-row">
              <span class="dash-stock-dot dash-stock-dot--yellow"></span>
              <span>Low Stock</span>
              <strong><?= $kpi['low_stock'] ?></strong>
            </div>
            <div class="dash-stock-row">
              <span class="dash-stock-dot dash-stock-dot--red"></span>
              <span>Out of Stock</span>
              <strong><?= $kpi['out_of_stock'] ?></strong>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Inventory Table -->
    <div class="dash-card">
      <div class="dash-card-header">
        <h2 class="dash-card-title">Product Inventory</h2>
        <div class="dash-inv-actions">
          <input type="text" id="productSearch" class="dash-search" placeholder="Search products..." aria-label="Search products">
          <a href="/admin/add_product.php" class="dash-btn dash-btn--primary dash-btn--sm">+ Add Product</a>
        </div>
      </div>

      <?php if (empty($products)): ?>
        <div class="dash-empty">No products in the database.</div>
      <?php else: ?>
        <div class="dash-inv-wrap">
          <table class="dash-inv-table" id="productsTable">
            <thead>
              <tr>
                <th>Product</th>
                <th>Category</th>
                <th class="text-end">Price</th>
                <th>Stock</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $row): ?>
                <tr class="product-row">
                  <td>
                    <div class="dash-product-cell">
                      <img src="/images/<?= Sanitizer::escape($row['image_url']) ?>" alt="<?= Sanitizer::escape($row['name']) ?>" class="dash-product-thumb" onerror="this.src='/images/placeholder.png'" loading="lazy">
                      <div>
                        <div class="dash-product-name"><?= Sanitizer::escape($row['name']) ?></div>
                        <div class="dash-product-id">#<?= (int)$row['product_id'] ?></div>
                      </div>
                    </div>
                  </td>
                  <td><span class="dash-category"><?= Sanitizer::escape($row['category'] ?? '—') ?></span></td>
                  <td class="text-end dash-price">$<?= number_format((float)$row['price'], 2) ?></td>
                  <td>
                    <?php if ($row['quantity'] <= 0): ?>
                      <span class="dash-stock-badge dash-stock-badge--out">Out of Stock</span>
                    <?php elseif ($row['quantity'] <= 5): ?>
                      <span class="dash-stock-badge dash-stock-badge--low"><?= (int)$row['quantity'] ?> left</span>
                    <?php else: ?>
                      <span class="dash-stock-badge dash-stock-badge--ok"><?= (int)$row['quantity'] ?> in stock</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <div class="dash-action-btns">
                      <a href="edit_product.php?id=<?= (int)$row['product_id'] ?>" class="dash-action-btn" aria-label="Edit <?= Sanitizer::escape($row['name']) ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </a>
                      <a href="delete_product.php?id=<?= (int)$row['product_id'] ?>" class="dash-action-btn dash-action-btn--danger" aria-label="Delete <?= Sanitizer::escape($row['name']) ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>


<?php include __DIR__ . '/../components/footer.php'; ?>