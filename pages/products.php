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
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/../components/navbar.php"; ?>

<main id="maincontent" class="shop-page">
  <div class="container">

    <div class="shop-header">
      <h1 class="section-title-bold">Our Products</h1>

      <?php if ($searchTerm !== ''): ?>
        <p class="shop-search-info">
          Results for "<strong><?= htmlspecialchars($searchTerm) ?></strong>"
          <a href="/pages/products.php" class="shop-clear-search">Clear</a>
        </p>
      <?php endif; ?>
    </div>

    <?php if (!empty($errorMsg)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($errorMsg) ?>
      </div>
    <?php endif; ?>

    <div class="row g-4" id="productList">
      <?php if (!empty($matchedProducts)): ?>
        <?php foreach ($matchedProducts as $row): ?>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="shop-card product-card"
              data-product-id="<?= (int)$row['product_id'] ?>"
              data-name="<?= htmlspecialchars($row['name']) ?>"
              data-price="<?= htmlspecialchars($row['price']) ?>"
              data-category="<?= htmlspecialchars($row['category'] ?? '') ?>">

              <div class="shop-card-img">
                <img src="/images/<?= htmlspecialchars($row['image_url']) ?>"
                  alt="<?= htmlspecialchars($row['name']) ?>"
                  loading="lazy">
              </div>

              <div class="shop-card-body">
                <h3 class="shop-card-title"><?= htmlspecialchars($row['name']) ?></h3>
                <p class="shop-card-desc"><?= htmlspecialchars($row['description']) ?></p>
                <span class="shop-card-price">$<?= number_format($row['price'], 2) ?></span>

                <?php if ($row['quantity'] <= 0): ?>
                  <button class="shop-btn shop-btn--disabled" disabled>Unavailable</button>
                <?php else: ?>
                  <button class="shop-btn add-cart">Add to Cart</button>
                <?php endif; ?>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <p class="shop-empty">No products found in our inventory.</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">

<?php include __DIR__ . "/../components/footer.php"; ?>