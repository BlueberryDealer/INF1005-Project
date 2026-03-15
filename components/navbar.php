<?php
if (!isset($session)) {
    require_once __DIR__ . '/../security/session.php';
    $session = new SessionManager();
}
?>
<header class="navbar">
  <nav class="nav-container">

    <!-- Left navigation -->
    <ul class="nav-links">
      <li><a href="/pages/products.php">Shop</a></li>
      <li><a href="/pages/about.php">About</a></li>
      <li><a href="#">Where to Buy</a></li>
      <li><a href="/admin/dashboard.php">Admin</a></li>
      <li class="dropdown">
        <a href="#">Member</a>
        <ul class="dropdown-menu">
          <?php if (isset($session) && $session->isAuthenticated()): ?>
            <li><a href="/account/userProfile.php">View Profile</a></li>
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
          <?php $cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
          <span id="cartCount" class="cart-badge" <?= $cartCount <= 0 ? 'style="display:none;"' : '' ?>>
            <?= $cartCount ?>
          </span>
        </a>
        <div class="cart-preview" id="cartPreview"></div>
      </div>

      <div class="dropdown nav-profile">
        <a href="#" class="nav-icon" aria-label="Profile">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="8" r="4" stroke="white" stroke-width="2"/>
            <path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6" stroke="white" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </a>

        <ul class="dropdown-menu">
          <?php if (isset($session) && $session->isAuthenticated()): ?>
            <li><a href="/account/userProfile.php">View Profile</a></li>
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