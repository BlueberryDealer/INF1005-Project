<?php
// -------------------------------------------------------
// cart.php  –  View & manage the shopping cart
// -------------------------------------------------------
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/models/order_model.php';

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
        $productMap[$p['id']] = $p;
    }

    foreach ($_SESSION['cart'] as $pid => $qty) {
        if (!isset($productMap[$pid])) continue; // product removed from DB
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
}

$pageTitle = 'Your Cart – BoltBrew Energy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Site styles (Role 1) -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<?php include __DIR__ . '/components/header.php'; ?>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="container my-5" id="main-content">
    <h1 class="mb-4">Your Cart</h1>

    <?php if (empty($cartItems)): ?>
    <!-- ===== EMPTY CART STATE ===== -->
    <div class="text-center py-5" id="empty-cart-message">
        <i class="bi bi-cart-x" style="font-size: 4rem; color: #adb5bd;" aria-hidden="true"></i>
        <h2 class="mt-3 h4">Your cart is empty</h2>
        <p class="text-muted">Looks like you haven't added any energy drinks yet.</p>
        <a href="products.php" class="btn btn-primary mt-2">
            <i class="bi bi-lightning-fill me-1" aria-hidden="true"></i>
            Browse Products
        </a>
    </div>

    <?php else: ?>
    <!-- ===== CART TABLE ===== -->
    <div class="table-responsive">
        <table class="table align-middle" id="cart-table" aria-label="Shopping cart items">
            <thead class="table-dark">
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
                            <img src="<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 width="60" height="60"
                                 class="rounded object-fit-cover"
                                 onerror="this.src='assets/images/placeholder.png'">
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
    <div class="row justify-content-end mt-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 card-title">Order Summary</h2>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span id="cart-grand-total">$<?= number_format($grandTotal, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-muted small">
                        <span>Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total</span>
                        <span id="cart-total-display">$<?= number_format($grandTotal, 2) ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary w-100">
                        <i class="bi bi-credit-card me-1" aria-hidden="true"></i>
                        Proceed to Checkout
                    </a>
                    <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Live region for cart update announcements (WCAG) -->
    <div id="cart-status" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>

<!-- Hidden CSRF token for JS use -->
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Cart JS (Role 5 integrates into main.js; standalone version below) -->
<script src="assets/js/cart.js"></script>
</body>
</html>