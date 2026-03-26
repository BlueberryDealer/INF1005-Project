<?php


require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../security/sanitization.php';
require_once __DIR__ . '/../security/csrf.php';
require_once __DIR__ . '/../config/db_connect.php';

$name = $price = $desc = $stock = $successMsg = "";
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

    //$config = parse_ini_file('/var/www/private/db-config.ini'); prod
    //$config = parse_ini_file(__DIR__ . '/../db-config.ini'); test
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
        $quantity = $stock;

        if (!$ok) {
            $errorMsg = $validator->firstError() ?? 'Please check your input.';
        } else {
            // 3. SECURE INSERT: Using Prepared Statements
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsi", $name, $desc, $price, $image_url, $quantity);

            if ($stmt->execute()) {
                $successMsg = "Product added successfully!";
                // Reset fields
                $name = $price = $desc = $stock = "";
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }
            $stmt->close();
            $conn->close();
        }
    }




}
?>



<?php
include __DIR__ . "/../components/header.php";
?>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Add New Product</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($successMsg): ?>
                            <div class="alert alert-success"><?php echo $successMsg; ?></div>
                        <?php endif; ?>
                        <?php if ($errorMsg): ?>
                            <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                        <?php endif; ?>

                        <form action="add_product.php" method="POST">
                            <?php echo CSRFToken::field('csrf_token'); ?>
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?= Sanitizer::escape($name); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price ($) *</label>
                                    <input type="number" step="0.01" name="price" class="form-control"
                                        value="<?php echo $price; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Quantity *</label>
                                    <input type="number" name="stock_quantity" class="form-control" min="0"
                                        value="<?= Sanitizer::escape($stock); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control"
                                    rows="3"><?= Sanitizer::escape($desc); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image Filename (e.g., dog_food.jpg)</label>
                                <input type="text" name="image_url" class="form-control" value="default.jpg"
                                    placeholder="Place image in /images folder first">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Add Product</button>
                                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<?php include __DIR__ . "/../components/footer.php"; ?>