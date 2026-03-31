<?php


require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../config/db_connect.php';

$name = $price = $desc = $stock = $category = $successMsg = "";
$errorMsg = "";
$success = true;
$result = null;

// 2. PROCESS FORM ON SUBMIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check CSRF
    if (!CSRFToken::validate($_POST['csrf_token'] ?? '', true)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    try {
        $conn = db_connect();
    } catch (RuntimeException $e) {
        $errorMsg = $e->getMessage();
        $success = false;
    }

    if ($success) {
        $validator = new Sanitizer($_POST);
        $ok = $validator->validate([
            'name' => 'required|min:3|max:100',
            'price' => 'required|float|min:0',
            'description' => 'max:600',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $name = Sanitizer::sanitizeString((string) $_POST['name']);
        $price = Sanitizer::sanitizeFloat($_POST['price']);
        $desc = Sanitizer::sanitizeString((string) $_POST['description']);
        $stock = Sanitizer::sanitizeInt($_POST['stock_quantity']);
        $image_url = Sanitizer::sanitizeString((string) $_POST['image_url']);
        $category = Sanitizer::sanitizeString((string) ($_POST['category'] ?? ''));
        $quantity = $stock;

        if (!$ok) {
            $errorMsg = $validator->firstError() ?? 'Please check your input.';
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, quantity, category) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdssi", $name, $desc, $price, $image_url, $quantity, $category);

            if ($stmt->execute()) {
                $successMsg = "Product added successfully!";
                $name = $price = $desc = $stock = $category = "";
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }
            $stmt->close();
            $conn->close();
        }
    }
}

include __DIR__ . "/../components/header.php";
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . "/../components/navbar.php"; ?>

<main id="maincontent" class="pf-page">
  <div class="container">
    <div class="pf-wrapper">

      <div class="pf-top">
        <p class="pf-breadcrumb"><a href="/admin/dashboard.php">Dashboard</a> / Add Product</p>
        <h1 class="section-title-bold">Add New Product</h1>
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

        <form action="add_product.php" method="POST">
          <?php echo CSRFToken::field('csrf_token'); ?>

          <div class="pf-field">
            <label for="name" class="pf-label">Product Name <span class="pf-req">*</span></label>
            <input type="text" id="name" name="name" class="pf-input"
                   value="<?= Sanitizer::escape($name); ?>" required placeholder="e.g. Blueberry Blast">
          </div>

          <div class="pf-row">
            <div class="pf-field">
              <label for="price" class="pf-label">Price ($) <span class="pf-req">*</span></label>
              <input type="number" step="0.01" id="price" name="price" class="pf-input"
                     value="<?= Sanitizer::escape($price); ?>" required placeholder="0.00">
            </div>
            <div class="pf-field">
              <label for="stock_quantity" class="pf-label">Stock Quantity <span class="pf-req">*</span></label>
              <input type="number" id="stock_quantity" name="stock_quantity" class="pf-input" min="0"
                     value="<?= Sanitizer::escape($stock); ?>" required placeholder="0">
            </div>
          </div>

          <div class="pf-field">
            <label for="category" class="pf-label">Category</label>
            <input type="text" id="category" name="category" class="pf-input"
                   value="<?= Sanitizer::escape($category); ?>"
                   placeholder="e.g. Energy Drink, Soda, Sports Drink">
          </div>

          <div class="pf-field">
            <label for="description" class="pf-label">Description</label>
            <textarea id="description" name="description" class="pf-input pf-textarea"
                      rows="3" placeholder="Brief product description..."><?= Sanitizer::escape($desc); ?></textarea>
          </div>

          <div class="pf-field">
            <label for="image_url" class="pf-label">Image Filename</label>
            <input type="text" id="image_url" name="image_url" class="pf-input" value="default.jpg"
                   placeholder="e.g. blueberry_blast.png">
            <span class="pf-hint">Place the image in the <code>/images</code> folder first.</span>
          </div>

          <div class="pf-actions">
            <button type="submit" class="pf-btn pf-btn--primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
              Add Product
            </button>
            <a href="dashboard.php" class="pf-btn pf-btn--secondary">Back to Dashboard</a>
          </div>
        </form>
      </div>

    </div>
  </div>
</main>

<style>
/* ── Product Form Shared Styles ───────────────────────── */
.pf-page { padding: 32px 0 80px; }
.pf-wrapper { max-width: 680px; margin: 0 auto; }

.pf-top { margin-bottom: 24px; }
.pf-breadcrumb { font-size: 13px; color: #999; margin: 0 0 4px; }
.pf-breadcrumb a { color: var(--accent-bright); text-decoration: none; }
.pf-breadcrumb a:hover { text-decoration: underline; }
[data-theme="dark"] .pf-breadcrumb { color: rgba(255,255,255,0.4); }

.pf-card {
  background: var(--white, #fff); border: 1px solid #eee;
  border-radius: 14px; padding: 32px;
}
[data-theme="dark"] .pf-card { background: #151515; border-color: rgba(255,255,255,0.06); }

/* Alerts */
.pf-alert {
  display: flex; align-items: center; gap: 10px;
  padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
  margin-bottom: 24px;
}
.pf-alert--success { background: rgba(34,197,94,0.1); color: #22c55e; }
.pf-alert--error { background: rgba(239,68,68,0.1); color: #ef4444; }

/* Fields */
.pf-field { margin-bottom: 20px; }
.pf-label {
  display: block; font-size: 13px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.04em;
  color: #555; margin-bottom: 6px;
}
[data-theme="dark"] .pf-label { color: rgba(255,255,255,0.55); }
.pf-req { color: var(--accent-bright); }

.pf-input {
  width: 100%; padding: 12px 16px;
  border: 1.5px solid #ddd; border-radius: 10px;
  font-size: 14px; font-family: inherit;
  background: transparent; color: #222;
  outline: none; transition: border-color .2s ease;
  box-sizing: border-box;
}
.pf-input:focus { border-color: var(--accent-bright); }
.pf-input::placeholder { color: #bbb; }
[data-theme="dark"] .pf-input { border-color: rgba(255,255,255,0.12); color: #fff; }
[data-theme="dark"] .pf-input::placeholder { color: rgba(255,255,255,0.25); }
.pf-input:disabled { opacity: 0.5; cursor: not-allowed; }

.pf-textarea { resize: vertical; min-height: 80px; }

.pf-hint { display: block; margin-top: 5px; font-size: 12px; color: #999; }
.pf-hint code { font-size: 12px; color: var(--accent-bright); }
[data-theme="dark"] .pf-hint { color: rgba(255,255,255,0.3); }

/* Row */
.pf-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

/* Image Preview */
.pf-preview { margin-top: 10px; }
.pf-preview p { font-size: 12px; color: #999; margin: 0 0 6px; }
[data-theme="dark"] .pf-preview p { color: rgba(255,255,255,0.35); }
.pf-preview img { height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
[data-theme="dark"] .pf-preview img { border-color: rgba(255,255,255,0.08); }

/* Actions */
.pf-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 28px; }

.pf-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 14px 24px; font-size: 14px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.04em;
  border-radius: 10px; text-decoration: none; border: none;
  cursor: pointer; transition: all .2s ease; text-align: center;
  font-family: inherit;
}
.pf-btn--primary { background: var(--accent-bright); color: #000; }
.pf-btn--primary:hover { filter: brightness(1.1); }
.pf-btn--secondary {
  background: transparent; color: #555; border: 1.5px solid #ddd;
}
.pf-btn--secondary:hover { border-color: #bbb; color: #333; }
[data-theme="dark"] .pf-btn--secondary { color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.12); }
[data-theme="dark"] .pf-btn--secondary:hover { border-color: rgba(255,255,255,0.25); color: #fff; }

@media (max-width: 600px) {
  .pf-row { grid-template-columns: 1fr; }
  .pf-card { padding: 24px 20px; }
}
</style>

<?php include __DIR__ . "/../components/footer.php"; ?>