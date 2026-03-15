<?php
// -------------------------------------------------------
// checkout.php  –  Shipping details + order placement
// -------------------------------------------------------

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/auth_guard.php';


// ---------- Redirect if cart is empty ----------
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// ---------- CSRF token ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// ---------- Build cart for display ----------
$cartItems  = [];
$grandTotal = 0.0;
$productIds = array_keys($_SESSION['cart']);
$products   = getProductsByIds($productIds);

$productMap = [];
foreach ($products as $p) {
    $productMap[$p['id']] = $p;
}

foreach ($_SESSION['cart'] as $pid => $qty) {
    if (!isset($productMap[$pid])) continue;
    $p         = $productMap[$pid];
    $subtotal  = $p['price'] * $qty;
    $grandTotal += $subtotal;
    $cartItems[] = [
        'product_id' => $p['id'],
        'name'       => $p['name'],
        'price'      => $p['price'],
        'image'      => $p['image'],
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
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        die('CSRF validation failed.');
    }

    // ---- Collect & sanitize input ----
    $fields = ['full_name', 'email', 'phone', 'address_line', 'city', 'postal_code', 'country'];
    foreach ($fields as $f) {
        $oldInput[$f] = trim($_POST[$f] ?? '');
    }

    // ---- Server-side validation ----
    if (empty($oldInput['full_name']) || strlen($oldInput['full_name']) < 2) {
        $errors['full_name'] = 'Please enter your full name (min 2 characters).';
    }

    if (empty($oldInput['email']) || !filter_var($oldInput['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($oldInput['phone']) || !preg_match('/^\+?[\d\s\-]{7,15}$/', $oldInput['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number (7–15 digits).';
    }

    if (empty($oldInput['address_line'])) {
        $errors['address_line'] = 'Please enter your street address.';
    }

    if (empty($oldInput['city'])) {
        $errors['city'] = 'Please enter your city.';
    }

    if (empty($oldInput['postal_code']) || !preg_match('/^[A-Za-z0-9\s\-]{3,10}$/', $oldInput['postal_code'])) {
        $errors['postal_code'] = 'Please enter a valid postal code.';
    }

    if (empty($oldInput['country'])) {
        $errors['country'] = 'Please select a country.';
    }

    // ---- If no errors, create the order ----
    if (empty($errors)) {
        $orderId = createOrder(
            (int) $_SESSION['user_id'],
            $oldInput,
            $cartItems,
            $grandTotal
        );

        if ($orderId) {
            // Clear cart and redirect to confirmation
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

$pageTitle = 'Checkout – BoltBrew Energy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php include __DIR__ . '/../components/header.php'; ?>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="container my-5" id="main-content">
    <h1 class="mb-4">Checkout</h1>

    <?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($errors['general']) ?>
    </div>
    <?php endif; ?>

    <div class="row g-5">

        <!-- ===== LEFT: Shipping Form ===== -->
        <div class="col-lg-7">
            <h2 class="h4 mb-3">Shipping Details</h2>

            <form method="POST" action="checkout.php"
                  id="checkout-form" novalidate>

                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrfToken) ?>">

                <!-- Full Name -->
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name <span aria-hidden="true" class="text-danger">*</span></label>
                    <input type="text"
                           id="full_name"
                           name="full_name"
                           class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($oldInput['full_name'] ?? $defaultName) ?>"
                           required
                           autocomplete="name"
                           aria-describedby="full_name_error"
                           aria-required="true">
                    <?php if (isset($errors['full_name'])): ?>
                    <div id="full_name_error" class="invalid-feedback">
                        <?= htmlspecialchars($errors['full_name']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span aria-hidden="true" class="text-danger">*</span></label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($oldInput['email'] ?? $defaultEmail) ?>"
                           required
                           autocomplete="email"
                           aria-describedby="email_error"
                           aria-required="true">
                    <?php if (isset($errors['email'])): ?>
                    <div id="email_error" class="invalid-feedback">
                        <?= htmlspecialchars($errors['email']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span aria-hidden="true" class="text-danger">*</span></label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>"
                           required
                           autocomplete="tel"
                           placeholder="+65 9123 4567"
                           aria-describedby="phone_error"
                           aria-required="true">
                    <?php if (isset($errors['phone'])): ?>
                    <div id="phone_error" class="invalid-feedback">
                        <?= htmlspecialchars($errors['phone']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <label for="address_line" class="form-label">Street Address <span aria-hidden="true" class="text-danger">*</span></label>
                    <input type="text"
                           id="address_line"
                           name="address_line"
                           class="form-control <?= isset($errors['address_line']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($oldInput['address_line'] ?? '') ?>"
                           required
                           autocomplete="street-address"
                           aria-describedby="address_error"
                           aria-required="true">
                    <?php if (isset($errors['address_line'])): ?>
                    <div id="address_error" class="invalid-feedback">
                        <?= htmlspecialchars($errors['address_line']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- City + Postal -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="city" class="form-label">City <span aria-hidden="true" class="text-danger">*</span></label>
                        <input type="text"
                               id="city"
                               name="city"
                               class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($oldInput['city'] ?? '') ?>"
                               required
                               autocomplete="address-level2"
                               aria-describedby="city_error"
                               aria-required="true">
                        <?php if (isset($errors['city'])): ?>
                        <div id="city_error" class="invalid-feedback">
                            <?= htmlspecialchars($errors['city']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="postal_code" class="form-label">Postal Code <span aria-hidden="true" class="text-danger">*</span></label>
                        <input type="text"
                               id="postal_code"
                               name="postal_code"
                               class="form-control <?= isset($errors['postal_code']) ? 'is-invalid' : '' ?>"
                               value="<?= htmlspecialchars($oldInput['postal_code'] ?? '') ?>"
                               required
                               autocomplete="postal-code"
                               aria-describedby="postal_error"
                               aria-required="true">
                        <?php if (isset($errors['postal_code'])): ?>
                        <div id="postal_error" class="invalid-feedback">
                            <?= htmlspecialchars($errors['postal_code']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Country -->
                <div class="mb-4">
                    <label for="country" class="form-label">Country <span aria-hidden="true" class="text-danger">*</span></label>
                    <select id="country"
                            name="country"
                            class="form-select <?= isset($errors['country']) ? 'is-invalid' : '' ?>"
                            required
                            autocomplete="country-name"
                            aria-describedby="country_error"
                            aria-required="true">
                        <option value="" disabled <?= empty($oldInput['country']) ? 'selected' : '' ?>>Select country…</option>
                        <?php
                        $countries = ['Singapore', 'Malaysia', 'Indonesia', 'Thailand',
                                      'Philippines', 'Vietnam', 'Australia', 'United States',
                                      'United Kingdom', 'Other'];
                        foreach ($countries as $c):
                            $selected = (($oldInput['country'] ?? 'Singapore') === $c) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($c) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['country'])): ?>
                    <div id="country_error" class="invalid-feedback">
                        <?= htmlspecialchars($errors['country']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-bag-check me-1" aria-hidden="true"></i>
                    Place Order – $<?= number_format($grandTotal, 2) ?>
                </button>
            </form>
        </div><!-- /col -->

        <!-- ===== RIGHT: Order Summary ===== -->
        <div class="col-lg-5">
            <h2 class="h4 mb-3">Order Summary</h2>
            <div class="card shadow-sm">
                <ul class="list-group list-group-flush">
                    <?php foreach ($cartItems as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 width="48" height="48"
                                 class="rounded"
                                 onerror="this.src='assets/images/placeholder.png'">
                            <div>
                                <div class="fw-semibold small"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="text-muted small">Qty: <?= (int)$item['quantity'] ?></div>
                            </div>
                        </div>
                        <span>$<?= number_format($item['subtotal'], 2) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="card-body">
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span>$<?= number_format($grandTotal, 2) ?></span>
                    </div>
                </div>
            </div>

            <a href="cart.php" class="btn btn-outline-secondary mt-3 w-100">
                <i class="bi bi-pencil me-1" aria-hidden="true"></i>
                Edit Cart
            </a>
        </div>

    </div><!-- /row -->
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/checkout_validation.js"></script>
</body>
</html>