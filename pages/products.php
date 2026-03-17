<?php
require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';

$session = new SessionManager();

$errorMsg = "";
$success = true;
$result = null;
$searchTerm = trim((string)($_GET['search'] ?? ''));
$matchedProducts = [];

$csrfToken = CSRFToken::get();

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
          Results for "<strong><?= Sanitizer::escape($searchTerm) ?></strong>"
          <a href="/pages/products.php" class="shop-clear-search">Clear</a>
        </p>
      <?php endif; ?>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger" role="alert">
            <?= Sanitizer::escape($errorMsg) ?>
        </div>
    <?php endif; ?>

    <!-- Skeleton loader (shown while page loads) -->
    <div class="row g-4 skeleton-grid" id="skeletonGrid" aria-hidden="true">
      <div class="col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-body"><div class="skeleton-line skeleton-line--title"></div><div class="skeleton-line skeleton-line--text"></div><div class="skeleton-line skeleton-line--price"></div><div class="skeleton-line skeleton-line--btn"></div></div></div></div>
      <div class="col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-body"><div class="skeleton-line skeleton-line--title"></div><div class="skeleton-line skeleton-line--text"></div><div class="skeleton-line skeleton-line--price"></div><div class="skeleton-line skeleton-line--btn"></div></div></div></div>
      <div class="col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-body"><div class="skeleton-line skeleton-line--title"></div><div class="skeleton-line skeleton-line--text"></div><div class="skeleton-line skeleton-line--price"></div><div class="skeleton-line skeleton-line--btn"></div></div></div></div>
      <div class="col-sm-6 col-md-4 col-lg-3"><div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-body"><div class="skeleton-line skeleton-line--title"></div><div class="skeleton-line skeleton-line--text"></div><div class="skeleton-line skeleton-line--price"></div><div class="skeleton-line skeleton-line--btn"></div></div></div></div>
    </div>

    <!-- Actual product grid (hidden initially, revealed by JS) -->
    <div class="row g-4 product-list" id="productList" style="display:none;">
      <?php if (!empty($matchedProducts)): ?>
        <?php foreach ($matchedProducts as $row): ?>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="shop-card product-card"
              data-product-id="<?= (int)$row['product_id'] ?>"
              data-name="<?= Sanitizer::escape($row['name']) ?>"
              data-price="<?= Sanitizer::escape($row['price']) ?>"
              data-category="<?= Sanitizer::escape($row['category'] ?? '') ?>">

              <div class="shop-card-img">
                <img src="/images/<?= Sanitizer::escape($row['image_url']) ?>"
                  alt="<?= Sanitizer::escape($row['name']) ?>"
                  loading="lazy">
              </div>

              <div class="shop-card-body">
                <h3 class="shop-card-title"><?= Sanitizer::escape($row['name']) ?></h3>
                <p class="shop-card-desc"><?= Sanitizer::escape($row['description']) ?></p>
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

<input type="hidden" id="csrf-token" value="<?= Sanitizer::escape($csrfToken) ?>">

<?php include __DIR__ . "/../components/footer.php"; ?>