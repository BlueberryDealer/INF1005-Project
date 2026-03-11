<header class="navbar">
  <nav class="nav-container">

    <!-- Left navigation -->
<ul class="nav-links">
  <li><a href="/pages/products.php">Shop</a></li>
  <li><a href="/pages/about.php">About</a></li>
  <li><a href="#">Where to Buy</a></li>
  <li><a href="/admin/add_product.php">Admin add</a></li>

  <li class="dropdown">
    <a href="#">Member</a>
    <ul class="dropdown-menu">
      <li><a href="/auth/login.php">Login</a></li>
      <li><a href="/auth/register.php">Register</a></li>
    </ul>
  </li>

</ul>

    <!-- Center brand -->
    <a class="nav-brand" href="/index.php">QUENCH</a>

    <!-- Right side -->
<div class="nav-actions">

<form id="siteSearchForm" class="nav-search-form" action="/pages/products_test.php" method="get">
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

  <a href="cart_test.php">Cart (<span id="cartCount">0</span>)</a>

</div>

  </nav>
</header>