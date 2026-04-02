<?php
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../config/db_connect.php';


$errorMsg = "";
$success = true;
$result = null;
$successMsg = "";

// ── Step 1: Validate the product_id from the URL ──────────────
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$productId = (int) $_GET['id'];



// ── Step 3: Handle form submission (POST = save changes) ──────
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Check CSRF 
    if (!CSRFToken::validate($_POST['csrf_token'] ?? '', true)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    // Sanitization & Validation
    $validator = new Sanitizer($_POST);
    $ok = $validator->validate([
        'name' => 'required|min:3|max:100',
        'price' => 'required|float|min:0',
        'description' => 'max:600',
        'quantity' => 'required|integer|min:0',
    ]);

    $name = Sanitizer::sanitizeString((string) $_POST['name']);
    $price = Sanitizer::sanitizeFloat($_POST['price']);
    $desc = Sanitizer::sanitizeString((string) $_POST['description']);
    $stock = Sanitizer::sanitizeInt($_POST['quantity']);
    $image_url = Sanitizer::sanitizeString((string) $_POST['image_url']);
    $quantity = $stock;

    if (!$ok) {
        $errorMsg = $validator->firstError() ?? 'Please check your input.';
    } else {
        try {
            $conn = db_connect();
        } catch (RuntimeException $e) {
            $errorMsg = $e->getMessage();
            $success = false;
        }

        if ($success) {
            $category = Sanitizer::sanitizeString((string) ($_POST['category'] ?? ''));
            $stmt = $conn->prepare(
                "UPDATE products 
            SET name = ?, description = ?, price = ?, image_url = ?, quantity = ?, category = ?
          WHERE product_id = ?"
            );
            $stmt->bind_param("ssdsisi", $name, $desc, $price, $image_url, $quantity, $category, $productId);

            if ($stmt->execute()) {
                $successMsg = "Product updated successfully!";
            } else {
                $errorMsg = "Failed to update product. Please try again.";
            }

            $stmt->close();
            $conn->close();
        }
    }
}

try {
    $conn = db_connect();
} catch (RuntimeException $e) {
    $errorMsg = $e->getMessage();
    $success = false;
}

if ($success) {
    $stmt = $conn->prepare(
        "SELECT product_id, name, price, description, image_url, quantity, category 
       FROM products 
      WHERE product_id = ?"
    );
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$product) {
        header("Location: dashboard.php");
        exit();
    }
}

// ── Step 4: Fetch current product data to pre-fill the form ───
include __DIR__ . "/../components/header.php";
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/../components/navbar.php"; ?>

<main id="maincontent" class="pf-page">
  <div class="container">
    <div class="pf-wrapper">

      <div class="pf-top">
        <p class="pf-breadcrumb"><a href="/admin/dashboard.php">Dashboard</a> / Edit Product</p>
        <h1 class="section-title-bold">Edit Product <span class="pf-id-badge">#<?= Sanitizer::escape($product['product_id']) ?></span></h1>
      </div>

      <div class="pf-card">

        <?php if ($successMsg): ?>
          <div class="pf-alert pf-alert--success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?= Sanitizer::escape($successMsg) ?>
          </div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
          <div class="pf-alert pf-alert--error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <?= Sanitizer::escape($errorMsg) ?>
          </div>
        <?php endif; ?>

        <form action="edit_product.php?id=<?= Sanitizer::escape($product['product_id']) ?>" method="POST">
          <?php echo CSRFToken::field('csrf_token'); ?>

          <div class="pf-field">
            <label for="name" class="pf-label">Product Name <span class="pf-req">*</span></label>
            <input type="text" id="name" name="name" class="pf-input"
                   value="<?= Sanitizer::escape($product['name']) ?>" required>
          </div>

          <div class="pf-row">
            <div class="pf-field">
              <label for="price" class="pf-label">Price ($) <span class="pf-req">*</span></label>
              <input type="number" step="0.01" id="price" name="price" class="pf-input"
                     value="<?= Sanitizer::escape($product['price']) ?>" required>
            </div>
            <div class="pf-field">
              <label for="quantity" class="pf-label">Stock Quantity <span class="pf-req">*</span></label>
              <input type="number" id="quantity" name="quantity" class="pf-input" min="0"
                     value="<?= Sanitizer::escape($product['quantity']) ?>" required>
            </div>
          </div>

          <div class="pf-field">
            <label for="product_id" class="pf-label">Product ID</label>
            <input type="text" id="product_id" class="pf-input" value="#<?= Sanitizer::escape($product['product_id']) ?>" disabled>
            <span class="pf-hint">Product ID cannot be changed.</span>
          </div>

          <div class="pf-field">
            <label for="category" class="pf-label">Category</label>
            <input type="text" id="category" name="category" class="pf-input"
                   value="<?= Sanitizer::escape($product['category'] ?? '') ?>"
                   placeholder="e.g. Energy Drink, Soda, Sports Drink">
          </div>

          <div class="pf-field">
            <label for="description" class="pf-label">Description</label>
            <textarea id="description" name="description" class="pf-input pf-textarea"
                      rows="3"><?= Sanitizer::escape($product['description'] ?? '') ?></textarea>
          </div>

          <div class="pf-field">
            <label for="image_url" class="pf-label">Image Filename</label>
            <input type="text" id="image_url" name="image_url" class="pf-input"
                   value="<?= Sanitizer::escape($product['image_url'] ?? '') ?>"
                   placeholder="e.g. blueberry_blast.png">
            <span class="pf-hint">Place the image in the <code>/images</code> folder first.</span>
            <?php if (!empty($product['image_url'])): ?>
              <div class="pf-preview">
                <p>Current image:</p>
                <img src="/images/<?= Sanitizer::escape($product['image_url']) ?>"
                     alt="<?= Sanitizer::escape($product['name']) ?>"
                     onerror="this.style.display='none'">
              </div>
            <?php endif; ?>
          </div>

          <div class="pf-actions">
            <button type="submit" class="pf-btn pf-btn--primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 21v-8H7v8M7 3v5h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Save Changes
            </button>
            <a href="dashboard.php" class="pf-btn pf-btn--secondary">Back to Dashboard</a>
          </div>
        </form>
      </div>

    </div>
  </div>
</main>


<?php include __DIR__ . "/../components/footer.php"; ?>