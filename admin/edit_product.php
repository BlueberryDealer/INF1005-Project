<?php
session_start();
//require_once __DIR__ . '/../security/admin_guard.php';  


$errorMsg = "";
$successMsg = "";

// ── Step 1: Validate the product_id from the URL ──────────────
// The dashboard Edit button links here as: edit_product.php?id=5
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$productId = (int) $_GET['id']; // cast to int — safe against SQL injection

// ── Step 2: Load DB config (shared with all other pages) ──────
$config = parse_ini_file('/var/www/private/db-config.ini');
if (!$config) {
    die("Failed to read database config file.");
}

// ── Step 3: Handle form submission (POST = save changes) ──────
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $price = $_POST['price'];
    $desc = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);
    $quantity = (int) $_POST['quantity'];

    if (empty($name) || empty($price)) {
        $errorMsg = "Product name and price are required.";
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
        } else {
            // Prepared statement — safe UPDATE
            $stmt = $conn->prepare(
                "UPDATE products 
            SET name = ?, description = ?, price = ?, image_url = ?, quantity = ?
          WHERE product_id = ?"
            );
            $stmt->bind_param("ssdsii", $name, $desc, $price, $image_url, $quantity, $productId);

            if ($stmt->execute()) {
                $successMsg = "Product updated successfully!";
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }
    }
}

// ── Step 4: Fetch current product data to pre-fill the form ───
// Re-fetch after POST too, so the form always shows the latest values
$conn = new mysqli(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare(
    "SELECT product_id, name, price, description, image_url, quantity 
       FROM products 
      WHERE product_id = ?"
);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Product not found — send back to dashboard
if (!$product) {
    header("Location: dashboard.php");
    exit();
}
?>

<?php
include __DIR__ . "/../components/header.php";
?>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">

                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            Edit Product
                            <span class="fs-6 fw-normal ms-2 opacity-75">
                                #<?= htmlspecialchars($product['product_id']) ?>
                            </span>
                        </h3>
                    </div>

                    <div class="card-body">

                        <?php if ($successMsg): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
                        <?php endif; ?>

                        <?php if ($errorMsg): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                        <?php endif; ?>

                        <!--
                            Keep the product_id in the URL for the action so
                            the same page handles GET (load) and POST (save)
                        -->
                        <form action="edit_product.php?id=<?= htmlspecialchars($product['product_id']) ?>"
                            method="POST">

                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    value="<?= htmlspecialchars($product['name']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price ($) *</label>
                                    <input type="number" step="0.01" id="price" name="price" class="form-control"
                                        value="<?= htmlspecialchars($product['price']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="quantity" class="form-label">Stock Quantity *</label>
                                    <input type="number" id="quantity" name="quantity" class="form-control" min="0"
                                        value="<?= htmlspecialchars($product['quantity']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <!-- product_id is read-only — never editable -->
                                    <label class="form-label">Product ID</label>
                                    <input type="text" class="form-control"
                                        value="#<?= htmlspecialchars($product['product_id']) ?>" disabled>
                                    <div class="form-text">Product ID cannot be changed.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control"
                                    rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="image_url" class="form-label">Image Filename (e.g., product.jpg)</label>
                                <input type="text" id="image_url" name="image_url" class="form-control"
                                    value="<?= htmlspecialchars($product['image_url'] ?? '') ?>"
                                    placeholder="Place image in /images folder first">
                                <!-- Live preview of the current image -->
                                <?php if (!empty($product['image_url'])): ?>
                                    <div class="mt-2">
                                        <p class="form-text mb-1">Current image:</p>
                                        <img src="/images/<?= htmlspecialchars($product['image_url']) ?>"
                                            alt="<?= htmlspecialchars($product['name']) ?>"
                                            style="height: 80px; object-fit: cover; border-radius: 6px;"
                                            onerror="this.style.display='none'">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Save Changes
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">
                                    Back to Dashboard
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<?php include __DIR__ . "/../components/footer.php"; ?>