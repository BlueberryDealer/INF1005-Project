<?php
require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../config/db_connect.php';

$session = new SessionManager();

$errorMsg = "";
$success = true;
$result = null;
$searchTerm = trim((string)($_GET['search'] ?? ''));
$matchedProducts = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

try {
    $conn = db_connect();
} catch (RuntimeException $e) {
    $errorMsg = $e->getMessage();
    $success = false;
}

if ($success) {
    if ($searchTerm !== '') {
        $searchLike = '%' . $searchTerm . '%';
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY name");
        $stmt->bind_param("ss", $searchLike, $searchLike);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY name");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $matchedProducts = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
    $conn->close();
}

include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";
?>
<main class="container mt-5">
    <h2 class="text-center mb-4">Our Products</h2>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <?php if ($searchTerm !== ''): ?>
        <p class="text-center text-muted mb-4">
            Search results for "<strong><?= htmlspecialchars($searchTerm) ?></strong>"
        </p>
    <?php endif; ?>

    <div class="row" id="productList">
        <?php if (!empty($matchedProducts)): ?>
            <?php foreach ($matchedProducts as $row): ?>
                <div class="col-sm-6 col-md-4 mb-4">
                    <div class="card h-100 shadow-sm product-card"
                        data-product-id="<?= (int)$row['product_id'] ?>"
                        data-name="<?= htmlspecialchars($row['name']) ?>"
                        data-price="<?= htmlspecialchars($row['price']) ?>"
                        data-category="<?= htmlspecialchars($row['category'] ?? '') ?>">

                        <img src="/images/<?= htmlspecialchars($row['image_url']) ?>"
                            class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>">

                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($row['description']) ?></p>
                            <h6 class="text-primary">$<?= number_format($row['price'], 2) ?></h6>

                            <div class="d-grid gap-2 mt-3">
                                <?php if ($row['quantity'] <= 0): ?>
                                    <button class="btn btn-secondary" disabled>Unavailable</button>
                                <?php else: ?>
                                    <button class="btn btn-primary add-cart">Add to Cart</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No products found in our inventory.</p>
        <?php endif; ?>
    </div>
</main>

<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<?php include __DIR__ . "/../components/footer.php"; ?>
