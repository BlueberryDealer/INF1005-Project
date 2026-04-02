<?php
require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';


$errorMsg = "";
$success = true;
$result = null;


// ── Step 1: Validate the product_id from the URL ──────────────
// Dashboard Delete button links here as: delete_product.php?id=5
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$productId = (int) $_GET['id'];



// ── Step 3: Handle confirmed deletion (POST = confirmed) ──────
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CSRF check
    if (!CSRFToken::validate($_POST['csrf_token'] ?? '', true)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    // Double-check the posted ID matches the URL ID — prevents tampering
    if (!isset($_POST['product_id']) || (int) $_POST['product_id'] !== $productId) {
        header("Location: dashboard.php");
        exit();
    }
    try {
        $conn = db_connect();
    } catch (RuntimeException $e) {
        $errorMsg = $e->getMessage();
        $success = false;
    }

    if ($success) {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            // Redirect back to dashboard with a success flag
            header("Location: dashboard.php?deleted=1");
            exit();
        } else {
            $errorMsg = "Failed to delete product. Please try again.";
            $stmt->close();
        }
        $conn->close();
    }
}

// ── Step 4: Fetch product details to show on confirmation page ─
try {
    $conn = db_connect();
} catch (RuntimeException $e) {
    $errorMsg = $e->getMessage();
    $success = false;
}

if ($success) {
    $stmt = $conn->prepare(
        "SELECT product_id, name, price, description, image_url
           FROM products
          WHERE product_id = ?"
    );
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Product not found — nothing to delete
    if (!$product) {
        header("Location: dashboard.php");
        exit();
    }
} else {
    // If DB connection failed, we can't show product details — just redirect back
    header("Location: dashboard.php");
    exit();
}
?>

<?php
include __DIR__ . "/../components/header.php";
?>

<body class="bg-light">

    <header>
        <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Admin Control Panel</span>
                <a href="/index.php" class="btn btn-outline-light btn-sm">Back to Store</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-6">

                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= Sanitizer::escape($errorMsg) ?>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow border-0">

                        <!-- Red header — visually distinct from add (blue) and edit (yellow) -->
                        <div class="card-header bg-danger text-white">
                            <h3 class="mb-0">
                                Delete Product
                                <span class="fs-6 fw-normal ms-2 opacity-75">
                                    #<?= Sanitizer::escape($product['product_id']) ?>
                                </span>
                            </h3>
                        </div>

                        <div class="card-body">

                            <!-- Warning message -->
                            <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                                    class="bi bi-exclamation-triangle-fill flex-shrink-0" viewBox="0 0 16 16"
                                    aria-hidden="true">
                                    <path
                                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                                </svg>
                                <span>This action is <strong>permanent</strong> and cannot be undone.</span>
                            </div>

                            <!-- Product summary — show what is being deleted -->
                            <div class="card border mb-4">
                                <div class="card-body">
                                    <p class="text-muted small mb-2">You are about to delete the following product:</p>

                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Product image thumbnail -->
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="/images/<?= Sanitizer::escape($product['image_url']) ?>"
                                                alt="<?= Sanitizer::escape($product['name']) ?>"
                                                style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; flex-shrink: 0;"
                                                onerror="this.style.display='none'">
                                        <?php endif; ?>

                                        <div>
                                            <p class="fw-semibold mb-1"><?= Sanitizer::escape($product['name']) ?></p>
                                            <p class="text-muted small mb-1">
                                                ID: <span
                                                    class="fw-bold">#<?= Sanitizer::escape($product['product_id']) ?></span>
                                                &nbsp;|&nbsp;
                                                Price: <span
                                                    class="fw-bold">$<?= number_format($product['price'], 2) ?></span>
                                            </p>
                                            <?php if (!empty($product['description'])): ?>
                                                <p class="text-muted small mb-0 text-truncate" style="max-width: 280px;">
                                                    <?= Sanitizer::escape($product['description']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!--
                                POST to same page with product_id hidden field.
                                Server checks POST matches URL ID before deleting.
                            -->
                            <form action="delete_product.php?id=<?= Sanitizer::escape($product['product_id']) ?>"
                                method="POST">
                                <?= CSRFToken::field('csrf_token') ?>
                                <input type="hidden" name="product_id"
                                    value="<?= Sanitizer::escape($product['product_id']) ?>">

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                                        Yes, Delete This Product
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        Cancel — Go Back to Dashboard
                                    </a>
                                </div>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

</body>

<?php include __DIR__ . "/../components/footer.php"; ?>