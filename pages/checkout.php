<?php
// -------------------------------------------------------
// checkout.php  –  Shipping details + order placement
// -------------------------------------------------------

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/auth_guard.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';
$session = new SessionManager();

// ---------- Redirect if cart is empty ----------
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// ---------- CSRF token ----------
$csrfToken = CSRFToken::get();

// ---------- Build cart for display ----------
$cartItems  = [];
$grandTotal = 0.0;
$productIds = array_keys($_SESSION['cart']);
$products   = getProductsByIds($productIds);

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
        'image'      => '/images/' . ltrim((string)$p['image_url'], '/'),
        'quantity'   => $qty,
        'subtotal'   => $subtotal,
    ];
}

// ---------- Form error / old-input storage ----------
$errors   = [];
$oldInput = [];

// ---------- Handle POST (form submission) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!CSRFToken::validate($_POST['csrf_token'] ?? '', false)) {
        die('CSRF validation failed.');
    }

    // ---- Collect & sanitize input ----
    $rawInput = [
        'full_name'    => $_POST['full_name'] ?? '',
        'email'        => $_POST['email'] ?? '',
        'phone'        => $_POST['phone'] ?? '',
        'address_line' => $_POST['address_line'] ?? '',
        'city'         => $_POST['city'] ?? '',
        'postal_code'  => $_POST['postal_code'] ?? '',
        'country'      => $_POST['country'] ?? '',
    ];
    
    $oldInput = array_map('trim', $rawInput);

    // ---- Server-side validation ----

    // Validation rules 
    $rules = [
        'full_name'    => 'required|min:2|max:100',
        'email'        => 'required|email',
        'phone'        => 'required|phone',
        'address_line' => 'required|min:5|max:200',
        'city'         => 'required|min:2|max:100',
        'postal_code'  => 'required|postal_code',
        'country'      => 'required'
    ];

    $sanitizer = new Sanitizer($rawInput);
    $isValid = $sanitizer->validate($rules);
    $errors  = $sanitizer->getErrors();

    // If validation passes, sanitize before saving 
    if ($isValid) {
        $sanitizedInput = [
            'full_name'    => Sanitizer::sanitizeString($rawInput['full_name']),
            'email'        => Sanitizer::sanitizeEmail($rawInput['email']),
            'phone'        => Sanitizer::sanitizeString($rawInput['phone']),
            'address_line' => Sanitizer::sanitizeString($rawInput['address_line']),
            'city'         => Sanitizer::sanitizeString($rawInput['city']),
            'postal_code'  => Sanitizer::sanitizeString($rawInput['postal_code']),
            'country'      => Sanitizer::sanitizeString($rawInput['country']),
        ];
        // ---- If no errors, create the order ----
        $orderId = createOrder(
            (int) $_SESSION['user_id'],
            $sanitizedInput,
            $cartItems,
            $grandTotal
        );

        if ($orderId) {
            $_SESSION['cart'] = [];
            $_SESSION['last_order_id'] = $orderId;
            header('Location: order_confirm.php?order_id=' . $orderId);
            exit;
        } else {
            $errors['general'] = 'Sorry, there was a problem placing your order. Please try again.';
        }
    }
}

    
    

// Prefill with session user data if available (optional convenience)
$defaultEmail = $_SESSION['user_email'] ?? '';
$defaultName  = $_SESSION['user_name']  ?? '';

include __DIR__ . '/../components/header.php';
?>
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<main class="checkout-page" id="main-content">
  <div class="container">
    <h1 class="section-title-bold">Checkout</h1>

    <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger" role="alert">
        <?= Sanitizer::escape($errors['general']) ?>
    </div>
    <?php endif; ?>

    <div class="row g-5">

      <!-- ===== LEFT: Shipping Form ===== -->
      <div class="col-lg-7">
        <div class="checkout-form-card">
          <h2 class="checkout-section-title">Shipping Details</h2>

            <form method="POST" action="checkout.php"
                  id="checkout-form" novalidate>

                <?= CSRFToken::field('csrf_token') ?>

            <!-- Full Name -->
            <div class="auth-field">
              <label for="full_name" class="auth-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" id="full_name" name="full_name"
                class="auth-input <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                value="<?= Sanitizer::escape($oldInput['full_name'] ?? $defaultName) ?>"
                required autocomplete="name" aria-required="true">
              <?php if (isset($errors['full_name'])): ?>
                <div class="invalid-feedback"><?= Sanitizer::escape($errors['full_name'][0]) ?></div>
              <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="auth-field">
              <label for="email" class="auth-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" id="email" name="email"
                class="auth-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                value="<?= Sanitizer::escape($oldInput['email'] ?? $defaultEmail) ?>"
                required autocomplete="email" aria-required="true">
              <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= Sanitizer::escape($errors['email'][0]) ?></div>
              <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="auth-field">
              <label for="phone" class="auth-label">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" id="phone" name="phone"
                class="auth-input <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                value="<?= Sanitizer::escape($oldInput['phone'] ?? '') ?>"
                placeholder="+65 9123 4567"
                required autocomplete="tel" aria-required="true">
              <?php if (isset($errors['phone'])): ?>
                <div class="invalid-feedback"><?= Sanitizer::escape($errors['phone'][0]) ?></div>
              <?php endif; ?>
            </div>

            <!-- Address -->
            <div class="auth-field">
              <label for="address_line" class="auth-label">Street Address <span class="text-danger">*</span></label>
              <input type="text" id="address_line" name="address_line"
                class="auth-input <?= isset($errors['address_line']) ? 'is-invalid' : '' ?>"
                value="<?= Sanitizer::escape($oldInput['address_line'] ?? '') ?>"
                required autocomplete="street-address" aria-required="true">
              <?php if (isset($errors['address_line'])): ?>
                <div class="invalid-feedback"><?= Sanitizer::escape($errors['address_line'][0]) ?></div>
              <?php endif; ?>
            </div>

            <!-- City + Postal -->
            <div class="auth-row">
              <div class="auth-field">
                <label for="city" class="auth-label">City <span class="text-danger">*</span></label>
                <input type="text" id="city" name="city"
                  class="auth-input <?= isset($errors['city']) ? 'is-invalid' : '' ?>"
                  value="<?= Sanitizer::escape($oldInput['city'] ?? '') ?>"
                  required autocomplete="address-level2" aria-required="true">
                <?php if (isset($errors['city'])): ?>
                  <div class="invalid-feedback"><?= Sanitizer::escape($errors['city'][0]) ?></div>
                <?php endif; ?>
              </div>

              <div class="auth-field">
                <label for="postal_code" class="auth-label">Postal Code <span class="text-danger">*</span></label>
                <input type="text" id="postal_code" name="postal_code"
                  class="auth-input <?= isset($errors['postal_code']) ? 'is-invalid' : '' ?>"
                  value="<?= Sanitizer::escape($oldInput['postal_code'] ?? '') ?>"
                  required autocomplete="postal-code" aria-required="true">
                <?php if (isset($errors['postal_code'])): ?>
                  <div class="invalid-feedback"><?= Sanitizer::escape($errors['postal_code'][0]) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Country -->
            <div class="auth-field">
              <label for="country" class="auth-label">Country <span class="text-danger">*</span></label>
              <select id="country" name="country"
                class="auth-input <?= isset($errors['country']) ? 'is-invalid' : '' ?>"
                required autocomplete="country-name" aria-required="true">
                <option value="" disabled <?= empty($oldInput['country']) ? 'selected' : '' ?>>Select country…</option>
                <?php
                $countries = ['Singapore', 'Malaysia', 'Indonesia', 'Thailand',
                              'Philippines', 'Vietnam', 'Australia', 'United States',
                              'United Kingdom', 'Other'];
                foreach ($countries as $c):
                    $selected = (($oldInput['country'] ?? 'Singapore') === $c) ? 'selected' : '';
                ?>
                <option value="<?= Sanitizer::escape($c) ?>" <?= $selected ?>>
                  <?= Sanitizer::escape($c) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['country'])): ?>
                <div class="invalid-feedback"><?= Sanitizer::escape($errors['country'][0]) ?></div>
              <?php endif; ?>
            </div>

            <button type="submit" class="checkout-submit-btn">
              <i class="bi bi-bag-check me-2" aria-hidden="true"></i>
              Place Order — $<?= number_format($grandTotal, 2) ?>
            </button>
          </form>
        </div>
      </div>

      <!-- ===== RIGHT: Order Summary ===== -->
      <div class="col-lg-5">
        <div class="checkout-summary">
          <h2 class="checkout-section-title">Order Summary</h2>

          <div class="checkout-items">
            <?php foreach ($cartItems as $item): ?>
              <div class="checkout-item">
                <img src="<?= Sanitizer::escape($item['image']) ?>"
                     alt="<?= Sanitizer::escape($item['name']) ?>"
                     width="52" height="52"
                     class="checkout-item-img"
                     loading="lazy"
                     onerror="this.src='/images/placeholder.png'">
                <div class="checkout-item-info">
                  <span class="checkout-item-name"><?= Sanitizer::escape($item['name']) ?></span>
                  <span class="checkout-item-qty">Qty: <?= (int)$item['quantity'] ?></span>
                </div>
                <span class="checkout-item-price">$<?= number_format($item['subtotal'], 2) ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="checkout-summary-divider"></div>

          <div class="checkout-summary-total">
            <span>Total</span>
            <span>$<?= number_format($grandTotal, 2) ?></span>
          </div>

          <a href="cart.php" class="cart-continue-btn">
            <i class="bi bi-pencil me-2" aria-hidden="true"></i>
            Edit Cart
          </a>
        </div>
      </div>

    </div>
  </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
<script src="/js/checkout_validation.js"></script>