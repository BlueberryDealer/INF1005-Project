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
$isHomePage = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php';
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

    <!-- Hamburger (mobile only) -->
    <button type="button" class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false">
      <span class="hamburger-line"></span>
      <span class="hamburger-line"></span>
      <span class="hamburger-line"></span>
    </button>

    <!-- Left navigation (desktop only) -->
    <ul class="nav-links">
      <li><a href="/pages/products.php">Shop</a></li>
      <li><a href="/pages/about.php">About</a></li>
      <li><a href="/pages/where_to_buy.php">Where to Buy</a></li>
      <?php if ($isAdmin): ?>
        <li><a href="/admin/dashboard.php">Admin</a></li>
      <?php endif; ?>
    </ul>

    <!-- Center brand -->
    <a class="nav-brand" href="/index.php">QUENCH</a>

    <!-- Right side (always visible) -->
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

      <!-- Theme toggle -->
      <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle light and dark theme">
        <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="5" stroke="white" stroke-width="2"/>
          <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="white" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>

      <div class="cart-container">
        <a href="/pages/cart.php" class="nav-cart">
          Cart
          <span id="cartCount" class="cart-badge" <?= $cartCount <= 0 ? 'style="display:none;"' : '' ?>>
            <?= $cartCount ?>
          </span>
        </a>
        <div class="cart-preview" id="cartPreview">
          <?php if (empty($cartPreviewItems)): ?>
            <div class="cart-preview-empty">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <p>Your cart is empty</p>
              <a href="/pages/products.php" class="cart-preview-shop-link">Start Shopping</a>
            </div>
          <?php else: ?>
            <div class="cart-preview-header">
              <span class="cart-preview-title">Your Cart</span>
              <span class="cart-preview-count"><?= $cartCount ?> item<?= $cartCount > 1 ? 's' : '' ?></span>
            </div>
            <div class="cart-preview-items">
              <?php foreach (array_slice($cartPreviewItems, 0, 3) as $item): ?>
                <div class="cart-preview-item">
                  <img
                    src="<?= htmlspecialchars($item['image']) ?>"
                    alt="<?= htmlspecialchars($item['name']) ?>"
                    class="cart-preview-img"
                    loading="lazy"
                  >
                  <div class="cart-preview-info">
                    <span class="cart-preview-name"><?= htmlspecialchars($item['name']) ?></span>
                    <span class="cart-preview-qty"><?= (int)$item['quantity'] ?> &times; $<?= number_format((float)$item['subtotal'] / (int)$item['quantity'], 2) ?></span>
                  </div>
                  <span class="cart-preview-price">$<?= number_format((float)$item['subtotal'], 2) ?></span>
                </div>
              <?php endforeach; ?>

              <?php if (count($cartPreviewItems) > 3): ?>
                <div class="cart-preview-more">+ <?= count($cartPreviewItems) - 3 ?> more item<?= (count($cartPreviewItems) - 3) > 1 ? 's' : '' ?></div>
              <?php endif; ?>
            </div>
            <div class="cart-preview-footer">
              <div class="cart-preview-subtotal">
                <span>Subtotal</span>
                <strong>$<?= number_format($cartPreviewTotal, 2) ?></strong>
              </div>
              <div class="cart-preview-actions">
                <a href="/pages/cart.php" class="cart-preview-btn cart-preview-btn--primary">View Cart</a>
                <a href="/pages/checkout.php" class="cart-preview-btn cart-preview-btn--outline">Checkout</a>
              </div>
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
            <li><a href="/account/orders.php">Order History</a></li>
            <?php if ($isAdmin): ?>
              <li><a href="/admin/dashboard.php">Admin Dashboard</a></li>
            <?php endif; ?>
            <li><a href="/auth/logout.php">Logout</a></li>
          <?php else: ?>
            <li><a href="/auth/login.php">Login</a></li>
            <li><a href="/auth/register.php">Register</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Mobile slide-out menu -->
    <div class="mobile-menu" id="mobileMenu" aria-hidden="true">
      <ul class="mobile-menu-links">
        <li><a href="/pages/products.php">Shop</a></li>
        <li><a href="/pages/about.php">About</a></li>
        <li><a href="/pages/where_to_buy.php">Where to Buy</a></li>
        <?php if ($isAdmin): ?>
          <li><a href="/admin/dashboard.php">Admin</a></li>
        <?php endif; ?>
      </ul>
      <div class="mobile-menu-divider"></div>
      <ul class="mobile-menu-links">
        <?php if ($isAuthenticated): ?>
          <li><a href="/account/userProfile.php">My Profile</a></li>
          <li><a href="/account/orders.php">Order History</a></li>
          <li><a href="/auth/logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="/auth/login.php">Login</a></li>
          <li><a href="/auth/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>

  </nav>
</header>

<!-- Mobile overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<?php if (!$isHomePage): ?>
  <div class="navbar-spacer" aria-hidden="true"></div>
<?php endif; ?>

<!-- Scroll-to-top button -->
<button type="button" class="scroll-top" id="scrollTopBtn" aria-label="Scroll to top">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
    <path d="M12 19V5M5 12l7-7 7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</button>