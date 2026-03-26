<?php

require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';

$pd_session = new SessionManager();
$pd_errorMsg = '';
$pd_success = true;
$pd_product = null;

// ── Step 1: Validate ?id= from URL ─────────────────────────────────────────
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: products.php');
  exit();
}

$pd_productId = (int) $_GET['id'];       // cast to int — safe against SQL injection
$pd_csrf = CSRFToken::get();

// ── Step 2: Fetch the product from the database ─────────────────────────────
try {
  $pd_conn = db_connect();
} catch (RuntimeException $e) {
  $pd_errorMsg = $e->getMessage();
  $pd_success = false;
}

if ($pd_success) {
  $pd_stmt = $pd_conn->prepare(
    "SELECT product_id, name, price, description, image_url, quantity
           FROM products
          WHERE product_id = ?
          LIMIT 1"
  );
  $pd_stmt->bind_param('i', $pd_productId);
  $pd_stmt->execute();
  $pd_result = $pd_stmt->get_result();
  $pd_product = $pd_result->fetch_assoc();   // ← stored under $pd_product, not $product
  $pd_stmt->close();
  $pd_conn->close();

  // Product not found — redirect back to shop
  if (!$pd_product) {
    header('Location: products.php');
    exit();
  }
}

// Determine stock BEFORE any include can touch our variables
$pd_inStock = $pd_success && $pd_product && (int) $pd_product['quantity'] > 0;

// ── Step 3: Output HTML (includes share scope — our $pd_ vars are safe) ────
include __DIR__ . '/../components/header.php';
?>

<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main id="maincontent" class="pd-page">
  <div class="container">

    <!-- Breadcrumb -->
    <nav class="pd-breadcrumb" aria-label="Breadcrumb">
      <a href="/pages/products.php">Shop</a>
      <span class="pd-breadcrumb-sep" aria-hidden="true">/</span>
      <span class="pd-breadcrumb-current">
        <?= $pd_success ? Sanitizer::escape($pd_product['name']) : 'Product' ?>
      </span>
    </nav>

    <?php if (!empty($pd_errorMsg)): ?>
      <div class="alert alert-danger" role="alert">
        <?= Sanitizer::escape($pd_errorMsg) ?>
      </div>
    <?php elseif ($pd_product): ?>

      <!-- ── Product Detail Card ─────────────────────────────────── -->
      <div class="pd-card reveal">

        <!-- Left: Product Image -->
        <div class="pd-image-wrap">
          <div class="pd-image-frame">
            <img src="/images/<?= Sanitizer::escape($pd_product['image_url']) ?>"
              alt="<?= Sanitizer::escape($pd_product['name']) ?>" class="pd-image"
              onerror="this.src='/assets/images/placeholder.png'" loading="eager">
          </div>

          <!-- Stock badge overlaid on image corner -->
          <div class="pd-stock-badge <?= $pd_inStock ? 'pd-stock-badge--in' : 'pd-stock-badge--out' ?>"
            aria-live="polite">
            <?= $pd_inStock ? 'In Stock' : 'Out of Stock' ?>
          </div>
        </div>

        <!-- Right: Product Details -->
        <div class="pd-details">

          <h1 class="pd-name"><?= Sanitizer::escape($pd_product['name']) ?></h1>

          <p class="pd-price">$<?= number_format((float) $pd_product['price'], 2) ?></p>

          <div class="pd-divider" aria-hidden="true"></div>

          <p class="pd-description">
            <?= !empty($pd_product['description'])
              ? Sanitizer::escape($pd_product['description'])
              : '<span class="pd-no-desc">No description available.</span>' ?>
          </p>

          <!-- Stock status line (text, below description) -->
          <div class="pd-availability <?= $pd_inStock ? 'pd-availability--in' : 'pd-availability--out' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <?php if ($pd_inStock): ?>
                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                  stroke-linejoin="round" />
              <?php else: ?>
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                  stroke-linejoin="round" />
              <?php endif; ?>
            </svg>
            <span><?= $pd_inStock ? 'Available for delivery' : 'Currently unavailable' ?></span>
          </div>

          <!-- Action Buttons -->
          <div class="pd-actions">

            <?php if ($pd_session->getRole() === 'admin'): ?>
              <button class="pd-btn pd-btn--disabled" disabled aria-disabled="true">
                Not available for admins
              </button>
            <?php elseif ($pd_inStock): ?>
              <button class="pd-btn pd-btn--primary add-cart" data-product-id="<?= (int) $pd_product['product_id'] ?>"
                data-name="<?= Sanitizer::escape($pd_product['name']) ?>"
                aria-label="Add <?= Sanitizer::escape($pd_product['name']) ?> to cart">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round" />
                  <line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                  <path d="M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                    stroke-linejoin="round" />
                </svg>
                Add to Cart
              </button>
            <?php else: ?>
              <button class="pd-btn pd-btn--disabled" disabled aria-disabled="true">
                Unavailable
              </button>
            <?php endif; ?>

            <a href="/pages/products.php" class="pd-btn pd-btn--secondary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
              Back to Shop
            </a>

          </div>

          <!-- AJAX status message (screen-reader + visual) -->
          <div id="pd-cart-status" class="pd-cart-status" role="status" aria-live="polite" aria-atomic="true">
          </div>

        </div>
      </div><!-- /.pd-card -->

    <?php endif; ?>

  </div><!-- /.container -->
</main>

<!-- Hidden CSRF token used by product_details.js -->
<input type="hidden" id="csrf-token" value="<?= Sanitizer::escape($pd_csrf) ?>">

<!-- Page-specific JS -->
<script src="/assets/js/product_details.js" defer></script>

<?php include __DIR__ . '/../components/footer.php'; ?>