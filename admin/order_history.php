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
            <div class="oh-order-left">
              <span class="oh-order-id">#<?= (int)$order['id'] ?></span>
              <div class="oh-order-customer">
                <span class="oh-customer-name"><?= Sanitizer::escape($order['full_name']) ?></span>
                <span class="oh-customer-email"><?= Sanitizer::escape($order['email']) ?></span>
              </div>
            </div>
            <div class="oh-order-right">
              <span class="oh-order-date">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <?= date('d M Y, g:i A', strtotime($order['created_at'])) ?>
              </span>
              <span class="oh-order-amount">$<?= number_format((float)$order['total_amount'], 2) ?></span>
              <span class="oh-status <?= $statusClass ?>"><?= Sanitizer::escape(ucfirst($order['status'])) ?></span>
              <svg class="oh-chevron" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
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

<style>
/* ── Order History Page ───────────────────────────── */
.oh-page { padding: 32px 0 80px; }

/* Header */
.oh-header {
  display: flex; justify-content: space-between; align-items: flex-end;
  flex-wrap: wrap; gap: 16px; margin-bottom: 24px;
}
.oh-breadcrumb {
  font-size: 13px; color: #999; margin: 0 0 4px;
}
.oh-breadcrumb a {
  color: var(--accent-bright); text-decoration: none;
}
.oh-breadcrumb a:hover { text-decoration: underline; }
[data-theme="dark"] .oh-breadcrumb { color: rgba(255,255,255,0.4); }

.oh-count {
  padding: 6px 14px; border-radius: 50px; font-size: 13px; font-weight: 700;
  background: rgba(34,211,238,0.12); color: var(--accent-bright);
}

/* Empty State */
.oh-empty {
  text-align: center; padding: 80px 24px; color: #999;
}
.oh-empty svg { color: #555; margin-bottom: 16px; }
.oh-empty h2 { font-size: 18px; font-weight: 700; margin: 0 0 8px; color: #555; }
.oh-empty p { font-size: 14px; margin: 0; }
[data-theme="dark"] .oh-empty svg { color: rgba(255,255,255,0.25); }
[data-theme="dark"] .oh-empty h2 { color: rgba(255,255,255,0.5); }
[data-theme="dark"] .oh-empty p { color: rgba(255,255,255,0.3); }

/* Toolbar */
.oh-toolbar { margin-bottom: 20px; }
.oh-search {
  padding: 10px 16px; border: 1.5px solid #ddd; border-radius: 10px;
  font-size: 14px; background: transparent; color: #333; outline: none;
  transition: border-color .2s ease; width: 100%; max-width: 400px;
}
.oh-search:focus { border-color: var(--accent-bright); }
[data-theme="dark"] .oh-search { border-color: rgba(255,255,255,0.12); color: #fff; }
[data-theme="dark"] .oh-search::placeholder { color: rgba(255,255,255,0.3); }

/* Order List */
.oh-list { display: flex; flex-direction: column; gap: 8px; }

/* Order Card */
.oh-order {
  background: var(--white, #fff); border: 1px solid #eee;
  border-radius: 12px; overflow: hidden;
  transition: border-color .2s ease;
}
.oh-order:hover { border-color: #ccc; }
[data-theme="dark"] .oh-order { background: #151515; border-color: rgba(255,255,255,0.06); }
[data-theme="dark"] .oh-order:hover { border-color: rgba(255,255,255,0.12); }

/* Order Header Button */
.oh-order-header {
  display: flex; justify-content: space-between; align-items: center;
  width: 100%; padding: 16px 20px; gap: 16px;
  background: none; border: none; cursor: pointer; text-align: left;
  color: inherit; font-family: inherit;
  transition: background .15s ease;
}
.oh-order-header:hover { background: rgba(0,0,0,0.02); }
[data-theme="dark"] .oh-order-header:hover { background: rgba(255,255,255,0.03); }

.oh-order-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
.oh-order-id {
  font-family: 'Sora', sans-serif; font-weight: 800; font-size: 15px;
  color: var(--accent-bright); white-space: nowrap;
}
.oh-order-customer { display: flex; flex-direction: column; min-width: 0; }
.oh-customer-name { font-weight: 600; font-size: 14px; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.oh-customer-email { font-size: 12px; color: #999; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
[data-theme="dark"] .oh-customer-name { color: rgba(255,255,255,0.9); }
[data-theme="dark"] .oh-customer-email { color: rgba(255,255,255,0.35); }

.oh-order-right { display: flex; align-items: center; gap: 16px; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; }

.oh-order-date {
  display: flex; align-items: center; gap: 5px;
  font-size: 12px; color: #999; white-space: nowrap;
}
.oh-order-date svg { flex-shrink: 0; }
[data-theme="dark"] .oh-order-date { color: rgba(255,255,255,0.35); }

.oh-order-amount {
  font-family: 'Sora', sans-serif; font-weight: 700; font-size: 15px;
  color: #111; white-space: nowrap;
}
[data-theme="dark"] .oh-order-amount { color: #fff; }

/* Status Badges */
.oh-status {
  display: inline-block; padding: 3px 10px; border-radius: 50px;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
  white-space: nowrap;
}
.oh-status--pending { background: rgba(245,158,11,0.12); color: #f59e0b; }
.oh-status--processing { background: rgba(59,130,246,0.12); color: #3b82f6; }
.oh-status--shipped { background: rgba(139,92,246,0.12); color: #8b5cf6; }
.oh-status--delivered { background: rgba(34,197,94,0.12); color: #22c55e; }
.oh-status--cancelled { background: rgba(239,68,68,0.12); color: #ef4444; }

/* Chevron */
.oh-chevron {
  color: #999; transition: transform .25s ease; flex-shrink: 0;
}
[data-theme="dark"] .oh-chevron { color: rgba(255,255,255,0.35); }
.oh-order-header[aria-expanded="true"] .oh-chevron { transform: rotate(180deg); }

/* Order Detail (expandable) */
.oh-order-detail {
  max-height: 0; overflow: hidden;
  transition: max-height .3s ease, padding .3s ease;
  padding: 0 20px;
}
.oh-order-detail.oh-open {
  max-height: 600px; padding: 0 20px 20px;
}

/* Items Table */
.oh-items-table {
  width: 100%; border-collapse: collapse; font-size: 13px;
  border-top: 1px solid #eee;
}
[data-theme="dark"] .oh-items-table { border-color: rgba(255,255,255,0.06); }

.oh-items-table thead th {
  padding: 10px 12px; text-align: left;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
  color: #999; border-bottom: 1px solid #eee;
}
[data-theme="dark"] .oh-items-table thead th { color: rgba(255,255,255,0.35); border-color: rgba(255,255,255,0.06); }

.oh-items-table tbody td {
  padding: 10px 12px; border-bottom: 1px solid #f5f5f5; color: #333;
}
[data-theme="dark"] .oh-items-table tbody td { border-color: rgba(255,255,255,0.04); color: rgba(255,255,255,0.8); }

.oh-item-name { font-weight: 600; }

.oh-items-table tfoot td {
  padding: 12px 12px; border-top: 1px solid #eee;
}
[data-theme="dark"] .oh-items-table tfoot td { border-color: rgba(255,255,255,0.08); }

.oh-total-label { font-weight: 700; color: #999; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; }
[data-theme="dark"] .oh-total-label { color: rgba(255,255,255,0.4); }
.oh-total-value { font-family: 'Sora', sans-serif; font-weight: 800; color: #111; font-size: 15px; }
[data-theme="dark"] .oh-total-value { color: #fff; }

.oh-no-items { padding: 20px 0; color: #999; font-size: 13px; }
[data-theme="dark"] .oh-no-items { color: rgba(255,255,255,0.35); }

/* Responsive */
@media (max-width: 768px) {
  .oh-order-header { flex-direction: column; align-items: flex-start; gap: 10px; }
  .oh-order-right { width: 100%; justify-content: flex-start; }
  .oh-header { flex-direction: column; align-items: flex-start; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Accordion toggle
  document.querySelectorAll('.oh-order-header').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var expanded = this.getAttribute('aria-expanded') === 'true';
      // Close all
      document.querySelectorAll('.oh-order-header').forEach(function(b) {
        b.setAttribute('aria-expanded', 'false');
      });
      document.querySelectorAll('.oh-order-detail').forEach(function(d) {
        d.classList.remove('oh-open');
      });
      // Toggle current
      if (!expanded) {
        this.setAttribute('aria-expanded', 'true');
        var detail = this.parentElement.querySelector('.oh-order-detail');
        if (detail) detail.classList.add('oh-open');
      }
    });
  });

  // Search filter
  var search = document.getElementById('orderSearch');
  if (search) {
    search.addEventListener('input', function() {
      var term = this.value.toLowerCase();
      document.querySelectorAll('.oh-order').forEach(function(order) {
        var data = order.getAttribute('data-search') || '';
        order.style.display = data.includes(term) ? '' : 'none';
      });
    });
  }
});
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>