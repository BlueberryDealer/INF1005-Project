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
          <div class="dash-orders-wrap">
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

<style>
/* ── Dashboard Page ───────────────────────────── */
.dash-page { padding: 32px 0 80px; }

/* Header */
.dash-header {
  display: flex; justify-content: space-between; align-items: flex-end;
  flex-wrap: wrap; gap: 16px; margin-bottom: 28px;
}
.dash-greeting { font-size: 14px; color: #777; margin: 0 0 4px; }
[data-theme="dark"] .dash-greeting { color: rgba(255,255,255,0.4); }
.dash-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.dash-btn {
  display: inline-flex; align-items: center; gap: 6px; justify-content: center;
  padding: 10px 20px; font-size: 13px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.04em;
  border-radius: 8px; text-decoration: none; transition: all .2s ease; cursor: pointer; border: none;
}
.dash-btn--sm { padding: 8px 16px; font-size: 12px; }
.dash-btn--primary { background: var(--accent-bright); color: #000 !important; }
.dash-btn--primary:hover { filter: brightness(1.1); color: #000 !important; }
.dash-btn--outline { background: transparent; color: #555; border: 1.5px solid #ddd; }
.dash-btn--outline:hover { border-color: #bbb; color: #333; }
[data-theme="dark"] .dash-btn--outline { color: rgba(255,255,255,0.7); border-color: rgba(255,255,255,0.15); }
[data-theme="dark"] .dash-btn--outline:hover { border-color: rgba(255,255,255,0.3); color: #fff; }

/* KPI Grid */
.dash-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }

.dash-kpi {
  display: flex; align-items: center; gap: 16px;
  padding: 22px 24px;
  background: var(--white, #fff); border: 1px solid #eee;
  border-radius: 12px; transition: border-color .2s ease, transform .2s ease;
}
.dash-kpi:hover { border-color: var(--accent-bright); transform: translateY(-2px); }
[data-theme="dark"] .dash-kpi { background: #151515; border-color: rgba(255,255,255,0.06); }

.dash-kpi-icon {
  width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.dash-kpi-icon--cyan { background: rgba(34,211,238,0.12); color: #22d3ee; }
.dash-kpi-icon--blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
.dash-kpi-icon--green { background: rgba(34,197,94,0.12); color: #22c55e; }
.dash-kpi-icon--orange { background: rgba(251,146,60,0.12); color: #fb923c; }

.dash-kpi-body { display: flex; flex-direction: column; }
.dash-kpi-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #999; margin-bottom: 4px; }
[data-theme="dark"] .dash-kpi-label { color: rgba(255,255,255,0.4); }
.dash-kpi-value { font-family: 'Sora', sans-serif; font-size: 24px; font-weight: 800; color: #111; line-height: 1.2; }
[data-theme="dark"] .dash-kpi-value { color: #fff; }

/* Stock Alert Bar */
.dash-alert-bar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.dash-alert {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
}
.dash-alert--danger { background: rgba(239,68,68,0.1); color: #ef4444; }
.dash-alert--warn { background: rgba(245,158,11,0.1); color: #f59e0b; }

/* Card */
.dash-card {
  background: var(--white, #fff); border: 1px solid #eee;
  border-radius: 12px; margin-bottom: 20px; overflow: hidden;
}
[data-theme="dark"] .dash-card { background: #151515; border-color: rgba(255,255,255,0.06); }

.dash-card-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; flex-wrap: wrap; gap: 12px;
}
.dash-card-title { font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 700; margin: 0; color: #111; }
[data-theme="dark"] .dash-card-title { color: #fff; }
.dash-card-link {
  font-size: 13px; font-weight: 600; color: var(--accent-bright); text-decoration: none;
  text-transform: uppercase; letter-spacing: 0.04em;
}
.dash-card-link:hover { text-decoration: underline; }

.dash-empty { padding: 48px 24px; text-align: center; color: #999; font-size: 14px; }

/* Two-column layout */
.dash-row { display: grid; grid-template-columns: 1fr 320px; gap: 20px; margin-bottom: 0; }

/* Recent Orders Table */
.dash-orders-wrap { overflow-x: auto; }
.dash-orders-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.dash-orders-table thead th {
  padding: 10px 16px; text-align: left;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
  color: #999; border-bottom: 1px solid #eee;
}
[data-theme="dark"] .dash-orders-table thead th { color: rgba(255,255,255,0.35); border-color: rgba(255,255,255,0.06); }

.dash-orders-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f5f5f5; color: #333; }
[data-theme="dark"] .dash-orders-table tbody td { border-color: rgba(255,255,255,0.04); color: rgba(255,255,255,0.8); }
.dash-orders-table tbody tr:hover td { background: rgba(0,0,0,0.02); }
[data-theme="dark"] .dash-orders-table tbody tr:hover td { background: rgba(255,255,255,0.03); }

.dash-order-id { font-weight: 700; color: var(--accent-bright); white-space: nowrap; }
.dash-customer-name { font-weight: 600; font-size: 13px; }
.dash-customer-email { font-size: 11px; color: #999; }
[data-theme="dark"] .dash-customer-email { color: rgba(255,255,255,0.35); }
.dash-order-items { font-size: 12px; color: #777; max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
[data-theme="dark"] .dash-order-items { color: rgba(255,255,255,0.4); }
.dash-order-amount { font-weight: 700; white-space: nowrap; }
.dash-order-date { font-size: 12px; color: #999; white-space: nowrap; }
[data-theme="dark"] .dash-order-date { color: rgba(255,255,255,0.35); }

/* Status badges */
.dash-status {
  display: inline-block; padding: 3px 10px; border-radius: 50px;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
}
.dash-status--pending { background: rgba(245,158,11,0.12); color: #f59e0b; }
.dash-status--processing { background: rgba(59,130,246,0.12); color: #3b82f6; }
.dash-status--shipped { background: rgba(139,92,246,0.12); color: #8b5cf6; }
.dash-status--delivered { background: rgba(34,197,94,0.12); color: #22c55e; }
.dash-status--cancelled { background: rgba(239,68,68,0.12); color: #ef4444; }

/* Quick Actions Sidebar */
.dash-sidebar { display: flex; flex-direction: column; gap: 20px; }
.dash-quick-links { padding: 8px 12px 16px; }
.dash-quick-link {
  display: flex; align-items: center; gap: 12px;
  padding: 12px; border-radius: 8px;
  font-size: 14px; font-weight: 600; color: #333;
  text-decoration: none; transition: background .15s ease;
}
.dash-quick-link svg { color: var(--accent-bright); flex-shrink: 0; }
.dash-quick-link:hover { background: rgba(0,0,0,0.04); color: #111; }
[data-theme="dark"] .dash-quick-link { color: rgba(255,255,255,0.8); }
[data-theme="dark"] .dash-quick-link:hover { background: rgba(255,255,255,0.05); color: #fff; }

/* Inventory Snapshot */
.dash-stock-rows { padding: 0 20px 20px; }
.dash-stock-row {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 0; border-bottom: 1px solid #f5f5f5;
  font-size: 14px; color: #444;
}
.dash-stock-row:last-child { border-bottom: none; }
[data-theme="dark"] .dash-stock-row { border-color: rgba(255,255,255,0.04); color: rgba(255,255,255,0.7); }
.dash-stock-row strong { margin-left: auto; font-weight: 700; }
.dash-stock-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dash-stock-dot--green { background: #22c55e; }
.dash-stock-dot--yellow { background: #f59e0b; }
.dash-stock-dot--red { background: #ef4444; }

/* Inventory Table */
.dash-inv-actions { display: flex; gap: 10px; align-items: center; }
.dash-search {
  padding: 8px 14px; border: 1.5px solid #ddd; border-radius: 8px;
  font-size: 13px; background: transparent; color: #333; outline: none;
  transition: border-color .2s ease; width: 200px;
}
.dash-search:focus { border-color: var(--accent-bright); }
[data-theme="dark"] .dash-search { border-color: rgba(255,255,255,0.15); color: #fff; }
[data-theme="dark"] .dash-search::placeholder { color: rgba(255,255,255,0.35); }

.dash-inv-wrap { overflow-x: auto; }
.dash-inv-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.dash-inv-table thead th {
  padding: 12px 16px; text-align: left;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
  color: #999; border-bottom: 1px solid #eee;
}
[data-theme="dark"] .dash-inv-table thead th { color: rgba(255,255,255,0.35); border-color: rgba(255,255,255,0.06); }

.dash-inv-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f5f5f5; color: #333; vertical-align: middle; }
[data-theme="dark"] .dash-inv-table tbody td { border-color: rgba(255,255,255,0.04); color: rgba(255,255,255,0.8); }
.dash-inv-table tbody tr:hover td { background: rgba(0,0,0,0.02); }
[data-theme="dark"] .dash-inv-table tbody tr:hover td { background: rgba(255,255,255,0.03); }

.dash-product-cell { display: flex; align-items: center; gap: 12px; }
.dash-product-thumb { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; background: #f5f5f5; border: 1px solid #eee; }
[data-theme="dark"] .dash-product-thumb { background: #222; border-color: rgba(255,255,255,0.06); }
.dash-product-name { font-weight: 600; font-size: 14px; }
.dash-product-id { font-size: 11px; color: #999; }
[data-theme="dark"] .dash-product-id { color: rgba(255,255,255,0.3); }

.dash-category { font-size: 12px; color: #777; }
[data-theme="dark"] .dash-category { color: rgba(255,255,255,0.4); }

.dash-price { font-weight: 700; font-size: 14px; white-space: nowrap; }

.dash-stock-badge {
  display: inline-block; padding: 3px 10px; border-radius: 50px;
  font-size: 11px; font-weight: 700; white-space: nowrap;
}
.dash-stock-badge--ok { background: rgba(34,197,94,0.12); color: #22c55e; }
.dash-stock-badge--low { background: rgba(245,158,11,0.12); color: #f59e0b; }
.dash-stock-badge--out { background: rgba(239,68,68,0.12); color: #ef4444; }

.dash-action-btns { display: flex; gap: 6px; justify-content: flex-end; }
.dash-action-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 8px; color: #666;
  border: 1px solid #e5e5e5; background: transparent; text-decoration: none;
  transition: all .15s ease;
}
.dash-action-btn:hover { border-color: var(--accent-bright); color: var(--accent-bright); }
.dash-action-btn--danger:hover { border-color: #ef4444; color: #ef4444; }
[data-theme="dark"] .dash-action-btn { color: rgba(255,255,255,0.5); border-color: rgba(255,255,255,0.1); }

/* Responsive */
@media (max-width: 1024px) {
  .dash-row { grid-template-columns: 1fr; }
  .dash-sidebar { flex-direction: row; }
  .dash-sidebar > * { flex: 1; }
}
@media (max-width: 768px) {
  .dash-kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .dash-header { flex-direction: column; align-items: flex-start; }
  .dash-sidebar { flex-direction: column; }
  .dash-inv-actions { flex-direction: column; width: 100%; }
  .dash-search { width: 100%; }
}
@media (max-width: 480px) {
  .dash-kpi-grid { grid-template-columns: 1fr; }
}
</style>

<script>
// Product search filter
document.addEventListener('DOMContentLoaded', function() {
  var search = document.getElementById('productSearch');
  if (search) {
    search.addEventListener('input', function() {
      var term = this.value.toLowerCase();
      var rows = document.querySelectorAll('#productsTable .product-row');
      rows.forEach(function(row) {
        var name = row.querySelector('.dash-product-name');
        row.style.display = name && name.textContent.toLowerCase().includes(term) ? '' : 'none';
      });
    });
  }
});
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>