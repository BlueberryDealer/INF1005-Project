<?php
require_once __DIR__ . '/../security/session.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';

$session = new SessionManager();

$errorMsg = "";
$success = true;
$result = null;
$searchTerm = trim((string) ($_GET['search'] ?? ''));
$selectedCategory = trim((string) ($_GET['category'] ?? ''));
$selectedStock = trim((string) ($_GET['stock'] ?? ''));
$selectedSort = trim((string) ($_GET['sort'] ?? 'default'));
$matchedProducts = [];
$categories = [];

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
    foreach ($matchedProducts as $product) {
      $category = trim((string) ($product['category'] ?? ''));
      if ($category !== '') {
        $categories[$category] = true;
      }
    }
  }
  $stmt->close();
  $conn->close();
}

$categoryOptions = array_keys($categories);
sort($categoryOptions, SORT_NATURAL | SORT_FLAG_CASE);

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

    <section class="shop-toolbar" aria-label="Filter and sort products">
      <div class="shop-toolbar-group">
        <label for="categoryFilter" class="shop-toolbar-label">Category</label>
        <select id="categoryFilter" class="shop-toolbar-select">
          <option value="">All categories</option>
          <?php foreach ($categoryOptions as $category): ?>
            <option value="<?= Sanitizer::escape($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
              <?= Sanitizer::escape($category) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="shop-toolbar-group">
        <label for="stockFilter" class="shop-toolbar-label">Availability</label>
        <select id="stockFilter" class="shop-toolbar-select">
          <option value="">All items</option>
          <option value="in-stock" <?= $selectedStock === 'in-stock' ? 'selected' : '' ?>>In stock</option>
          <option value="out-of-stock" <?= $selectedStock === 'out-of-stock' ? 'selected' : '' ?>>Out of stock</option>
        </select>
      </div>

      <div class="shop-toolbar-group">
        <label for="sortProducts" class="shop-toolbar-label">Sort by</label>
        <select id="sortProducts" class="shop-toolbar-select">
          <option value="default" <?= $selectedSort === 'default' ? 'selected' : '' ?>>Featured</option>
          <option value="name-asc" <?= $selectedSort === 'name-asc' ? 'selected' : '' ?>>Name: A to Z</option>
          <option value="name-desc" <?= $selectedSort === 'name-desc' ? 'selected' : '' ?>>Name: Z to A</option>
          <option value="price-asc" <?= $selectedSort === 'price-asc' ? 'selected' : '' ?>>Price: Low to high</option>
          <option value="price-desc" <?= $selectedSort === 'price-desc' ? 'selected' : '' ?>>Price: High to low</option>
        </select>
      </div>

      <button type="button" id="clearProductFilters" class="shop-filter-reset">Clear filters</button>
    </section>

    <!-- Skeleton loader (shown while page loads) -->
    <div class="row g-4 skeleton-grid" id="skeletonGrid" aria-hidden="true">
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="skeleton-card">
          <div class="skeleton-img"></div>
          <div class="skeleton-body">
            <div class="skeleton-line skeleton-line--title"></div>
            <div class="skeleton-line skeleton-line--text"></div>
            <div class="skeleton-line skeleton-line--price"></div>
            <div class="skeleton-line skeleton-line--btn"></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="skeleton-card">
          <div class="skeleton-img"></div>
          <div class="skeleton-body">
            <div class="skeleton-line skeleton-line--title"></div>
            <div class="skeleton-line skeleton-line--text"></div>
            <div class="skeleton-line skeleton-line--price"></div>
            <div class="skeleton-line skeleton-line--btn"></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="skeleton-card">
          <div class="skeleton-img"></div>
          <div class="skeleton-body">
            <div class="skeleton-line skeleton-line--title"></div>
            <div class="skeleton-line skeleton-line--text"></div>
            <div class="skeleton-line skeleton-line--price"></div>
            <div class="skeleton-line skeleton-line--btn"></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="skeleton-card">
          <div class="skeleton-img"></div>
          <div class="skeleton-body">
            <div class="skeleton-line skeleton-line--title"></div>
            <div class="skeleton-line skeleton-line--text"></div>
            <div class="skeleton-line skeleton-line--price"></div>
            <div class="skeleton-line skeleton-line--btn"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actual product grid (hidden initially, revealed by JS) -->
    <div class="row g-4 product-list" id="productList" style="display:none;">
       
      <?php if (!empty($matchedProducts)): ?>
        <?php foreach ($matchedProducts as $row): ?>
         <?php $detailUrl = '/pages/product_details.php?id=' . (int) $row['product_id']; ?>
          <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="shop-card product-card" data-product-id="<?= (int) $row['product_id'] ?>"
              data-name="<?= Sanitizer::escape($row['name']) ?>" data-price="<?= Sanitizer::escape($row['price']) ?>"
              data-category="<?= Sanitizer::escape($row['category'] ?? '') ?>" data-stock="<?= (int) $row['quantity'] ?>"
              data-default-order="<?= (int) $row['product_id'] ?>">

              <a href="<?= $detailUrl ?>" class="shop-card-img"
                aria-label="View details for <?= Sanitizer::escape($row['name']) ?>" tabindex="0">
                <img src="/images/<?= Sanitizer::escape($row['image_url']) ?>" alt="<?= Sanitizer::escape($row['name']) ?>"
                  loading="lazy">
              </a>


              <div class="shop-card-body">
                <a href="<?= $detailUrl ?>" class="shop-card-title-link">
                  <h3 class="shop-card-title"><?= Sanitizer::escape($row['name']) ?></h3>
                </a>
                <p class="shop-card-desc card-text"><?= Sanitizer::escape($row['description']) ?></p>
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