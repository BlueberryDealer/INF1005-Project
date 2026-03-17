<?php
// -------------------------------------------------------
// cart.php  –  View & manage the shopping cart
// -------------------------------------------------------

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/session.php';
$session = new SessionManager();

// ---------- CSRF token ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// ---------- Build cart items from session ----------
$cartItems  = [];
$grandTotal = 0.0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $products   = getProductsByIds($productIds);

    // Index products by ID for easy lookup
    $productMap = [];
    foreach ($products as $p) {
        $productMap[$p['product_id']] = $p;
    }

    foreach ($_SESSION['cart'] as $pid => $qty) {
        if (!isset($productMap[$pid])) continue;
        $p         = $productMap[$pid];
        $subtotal  = $p['price'] * $qty;
        $grandTotal += $subtotal;
        $cartItems[] = [
            'product_id' => $p['product_id'],
            'name'       => $p['name'],
            'price'      => $p['price'],
            'image'      => $p['image_url'],
            'quantity'   => $qty,
            'subtotal'   => $subtotal,
        ];
    }
}

include __DIR__ . '/../components/header.php';
?>
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<!-- Bootstrap Icons (needed for cart icons) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<main class="cart-page" id="main-content">
  <div class="container">
    <h1 class="section-title-bold">Your Cart</h1>

    <?php if (empty($cartItems)): ?>
    <!-- ===== EMPTY CART STATE ===== -->
    <div class="cart-empty" id="empty-cart-message">
      <i class="bi bi-cart-x cart-empty-icon" aria-hidden="true"></i>
      <h2>Your cart is empty</h2>
      <p>Looks like you haven't added any drinks yet.</p>
      <a href="products.php" class="cta-btn" role="button">
        <i class="bi bi-lightning-fill me-1" aria-hidden="true"></i>
        Browse Products
      </a>
    </div>

    <?php else: ?>
    <!-- ===== CART TABLE ===== -->
    <button type="button" id="clearCartBtn" class="btn btn-outline-danger mb-4">
      Clear Cart
    </button>

    <div class="table-responsive">
      <table class="table align-middle" id="cart-table" aria-label="Shopping cart items">
        <thead>
          <tr>
            <th scope="col">Product</th>
            <th scope="col">Price</th>
            <th scope="col">Quantity</th>
            <th scope="col">Subtotal</th>
            <th scope="col"><span class="visually-hidden">Remove</span></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($cartItems as $item): ?>
          <tr data-product-id="<?= (int)$item['product_id'] ?>">

            <!-- Product -->
            <td>
              <div class="d-flex align-items-center gap-3">
                <img src="/images/<?= htmlspecialchars($item['image']) ?>"
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     width="60" height="60"
                     class="rounded object-fit-cover"
                     loading="lazy"
                     onerror="this.src='/images/placeholder.png'">
                <span class="fw-semibold"><?= htmlspecialchars($item['name']) ?></span>
              </div>
            </td>

            <!-- Unit price -->
            <td>$<?= number_format($item['price'], 2) ?></td>

            <!-- Quantity +/- controls -->
            <td>
              <div class="d-flex align-items-center gap-2" style="width: 130px;">
                <button class="btn btn-outline-secondary btn-sm qty-btn"
                        data-action="decrease"
                        aria-label="Decrease quantity of <?= htmlspecialchars($item['name']) ?>">
                  <i class="bi bi-dash" aria-hidden="true"></i>
                </button>

                <input type="number"
                       class="form-control form-control-sm text-center qty-input"
                       value="<?= (int)$item['quantity'] ?>"
                       min="1"
                       aria-label="Quantity for <?= htmlspecialchars($item['name']) ?>">

                <button class="btn btn-outline-secondary btn-sm qty-btn"
                        data-action="increase"
                        aria-label="Increase quantity of <?= htmlspecialchars($item['name']) ?>">
                  <i class="bi bi-plus" aria-hidden="true"></i>
                </button>
              </div>
            </td>

            <!-- Subtotal -->
            <td class="item-subtotal fw-semibold">
              $<?= number_format($item['subtotal'], 2) ?>
            </td>

            <!-- Remove -->
            <td>
              <button class="btn btn-outline-danger btn-sm remove-btn"
                      aria-label="Remove <?= htmlspecialchars($item['name']) ?> from cart">
                <i class="bi bi-trash" aria-hidden="true"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== ORDER SUMMARY ===== -->
    <div class="row justify-content-end mt-4">
      <div class="col-md-5 col-lg-4">
        <div class="cart-summary">
          <h2 class="cart-summary-title">Order Summary</h2>

          <div class="cart-summary-row">
            <span>Subtotal</span>
            <span id="cart-grand-total">$<?= number_format($grandTotal, 2) ?></span>
          </div>

          <div class="cart-summary-row cart-summary-muted">
            <span>Shipping</span>
            <span>Calculated at checkout</span>
          </div>

          <hr class="cart-summary-divider">

          <div class="cart-summary-row cart-summary-total">
            <span>Total</span>
            <span id="cart-total-display">$<?= number_format($grandTotal, 2) ?></span>
          </div>

          <a href="checkout.php" class="cart-checkout-btn">
            <i class="bi bi-credit-card me-2" aria-hidden="true"></i>
            Proceed to Checkout
          </a>

          <a href="products.php" class="cart-continue-btn">
            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>
            Continue Shopping
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Live region for cart update announcements (WCAG) -->
    <div id="cart-status" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>
  </div>
</main>

<!-- Hidden CSRF token for JS use -->
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<?php include __DIR__ . '/../components/footer.php'; ?>