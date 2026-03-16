<?php
if (!isset($session)) {
    require_once __DIR__ . '/../security/session.php';
    $session = new SessionManager();
}
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';

$isAuthenticated = $session->isAuthenticated();
$isAdmin = $isAuthenticated && $session->getRole() === 'admin';
$memberHref = $isAuthenticated ? '/account/userProfile.php' : '/auth/login.php';
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$cartPreviewItems = [];
$cartPreviewTotal = 0.0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $products = getProductsByIds($productIds);
    $productMap = [];

    foreach ($products as $product) {
        $productMap[$product['product_id']] = $product;
    }

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        if (!isset($productMap[$productId])) {
            continue;
        }

        $product = $productMap[$productId];
        $subtotal = (float)$product['price'] * (int)$quantity;
        $cartPreviewTotal += $subtotal;
        $cartPreviewItems[] = [
            'name' => (string)$product['name'],
            'image' => '/images/' . ltrim((string)$product['image_url'], '/'),
            'quantity' => (int)$quantity,
            'subtotal' => $subtotal,
        ];
    }
}
?>
<header class="navbar">
  <nav class="nav-container">

    <!-- Left navigation -->
    <ul class="nav-links">
      <li><a href="/pages/products.php">Shop</a></li>
      <li><a href="/pages/about.php">About</a></li>
      <li><a href="#">Where to Buy</a></li>
      <?php if ($isAdmin): ?>
        <li><a href="/admin/dashboard.php">Admin</a></li>
      <?php endif; ?>
      <li class="dropdown">
        <button type="button" class="nav-trigger nav-trigger-text" aria-haspopup="true" aria-expanded="false">Member</button>
        <ul class="dropdown-menu">
          <?php if ($isAuthenticated): ?>
            <li><a href="/account/userProfile.php">View Profile</a></li>
            <li><a href="<?= $memberHref ?>">Account Home</a></li>
            <li><a href="/auth/logout.php">Logout</a></li>
          <?php else: ?>
            <li><a href="/auth/login.php">Login</a></li>
            <li><a href="/auth/register.php">Register</a></li>
          <?php endif; ?>
        </ul>
      </li>
    </ul>

    <!-- Center brand -->
    <a class="nav-brand" href="/index.php">QUENCH</a>

    <!-- Right side -->
    <div class="nav-actions">
      <form id="siteSearchForm" class="nav-search-form" action="/pages/products.php" method="get">
        <input
          type="text"
          id="searchInput"
          name="search"
          class="nav-search-input"
          placeholder="Search products..."
          aria-label="Search products"
        >
        <button type="submit" class="nav-icon" aria-label="Submit search">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="white" stroke-width="2"/>
            <line x1="16.65" y1="16.65" x2="21" y2="21" stroke="white" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>
      </form>

      <div class="cart-container">
        <a href="/pages/cart.php" class="nav-cart">
          Cart
          <span id="cartCount" class="cart-badge" <?= $cartCount <= 0 ? 'style="display:none;"' : '' ?>>
            <?= $cartCount ?>
          </span>
        </a>
        <div class="cart-preview" id="cartPreview">
          <?php if (empty($cartPreviewItems)): ?>
            <div class="p-3 small text-muted">Your cart is empty.</div>
          <?php else: ?>
            <div class="p-3">
              <div class="fw-semibold mb-2">Cart Preview</div>
              <?php foreach (array_slice($cartPreviewItems, 0, 3) as $item): ?>
                <div class="d-flex align-items-start gap-2 mb-2 small">
                  <img
                    src="<?= htmlspecialchars($item['image']) ?>"
                    alt="<?= htmlspecialchars($item['name']) ?>"
                    width="42"
                    height="42"
                    class="rounded object-fit-cover flex-shrink-0"
                  >
                  <div class="flex-grow-1">
                    <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="text-muted">Qty: <?= (int)$item['quantity'] ?></div>
                  </div>
                  <div>$<?= number_format((float)$item['subtotal'], 2) ?></div>
                </div>
              <?php endforeach; ?>

              <?php if (count($cartPreviewItems) > 3): ?>
                <div class="small text-muted mb-2">+ <?= count($cartPreviewItems) - 3 ?> more item(s)</div>
              <?php endif; ?>

              <div class="d-flex justify-content-between small border-top pt-2 mt-2">
                <span>Subtotal</span>
                <strong>$<?= number_format($cartPreviewTotal, 2) ?></strong>
              </div>
              <a href="/pages/cart.php" class="btn btn-sm btn-primary w-100 mt-2">View Cart</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="dropdown nav-profile">
        <button type="button" class="nav-icon nav-trigger nav-icon-button" aria-label="Profile menu" aria-haspopup="true" aria-expanded="false">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="8" r="4" stroke="white" stroke-width="2"/>
            <path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6" stroke="white" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </button>

        <ul class="dropdown-menu">
          <?php if ($isAuthenticated): ?>
            <li><a href="/account/userProfile.php">View Profile</a></li>
            <?php if ($isAdmin): ?>
              <li><a href="/admin/dashboard.php">Admin Dashboard</a></li>
            <?php endif; ?>
            <li><a href="<?= $memberHref ?>">Account Home</a></li>
            <li><a href="/auth/logout.php">Logout</a></li>
          <?php else: ?>
            <li><a href="/auth/login.php">Login</a></li>
            <li><a href="/auth/register.php">Register</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

  </nav>
</header>
