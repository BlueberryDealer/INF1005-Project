<?php
require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../security/sanitization.php';

$errorMsg = "";
$success = true;
$result = null;

// Fetching products using your provided logic
try {
    $conn = db_connect();
} catch (RuntimeException $e) {
    $errorMsg = $e->getMessage();
    $success = false;
}

if ($success) {
    $stmt = $conn->prepare("SELECT product_id, name, price, description, image_url, quantity FROM products");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $conn->close();
}
?>

<?php
include __DIR__ . "/../components/header.php";
?>

<body>
    <header>
        <title>Admin Dashboard - BoltBrew Energy</title>
        <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Admin Control Panel</span>
                <a href="/pages/products.php" class="btn btn-outline-light btn-sm">Back to Store</a>
            </div>
        </nav>
    </header>
    <main>
        <div class="container-fluid px-4">
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Sanitizer::escape($errorMsg) ?>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h2 class="text-primary-dark m-0">Inventory Management</h2>

                <div class="d-flex gap-2 align-items-center">
                    <input type="text" id="productSearch" class="form-control" placeholder="Search products..."
                        aria-label="Search products">

                    <a href="add_product.php" class="btn btn-primary text-nowrap shadow-sm">
                        + Add Product
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0" id="productsTable">
                        <thead class="table-light text-secondary-dark">
                            <tr>
                                <th scope="col" class="ps-4">ID</th>
                                <th scope="col">Image</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Price</th>
                                <th scope="col">Stock</th>
                                <th scope="col" class="pe-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="product-row">
                                        <td class="ps-4 fw-bold text-muted">#<?= Sanitizer::escape($row['product_id']) ?></td>
                                        <td>
                                            <img src="/images/<?= Sanitizer::escape($row['image_url']) ?>" alt="Product Image"
                                                class="product-thumbnail rounded">
                                        </td>
                                        <td class="product-name fw-semibold"><?= Sanitizer::escape($row['name']) ?></td>
                                        <td class="product-desc text-truncate" style="max-width: 250px;">
                                            <?= Sanitizer::escape($row['description']) ?>
                                        </td>
                                        <td class="fw-bold text-success-dark">
                                            $<?= number_format(($row['price']), 2) ?>
                                        </td>
                                        <td>
                                            <?php if ($row['quantity'] <= 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($row['quantity'] <= 5): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <?= Sanitizer::escape($row['quantity']) ?> left
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <?= Sanitizer::escape($row['quantity']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <a href="edit_product.php?id=<?= Sanitizer::escape($row['product_id']) ?>"
                                                class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                            <a href="delete_product.php?id=<?= Sanitizer::escape($row['product_id']) ?>"
                                                class="btn btn-sm btn-outline-danger">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No products found in the database.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>


</body>
<?php include __DIR__ . "/../components/footer.php"; ?>
