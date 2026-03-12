<?php
// admin_dashboard.php

$errorMsg = "";
$success = true;
$result = null;

// Fetching products using your provided logic
$config = parse_ini_file(__DIR__ . '/../db-config.ini');
if (!$config) {
    $errorMsg = "Failed to read database config file.";
    $success = false;
} else {
    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    if ($conn->connect_error) {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        $success = false;
    } else {
        $stmt = $conn->prepare("SELECT product_id, name, price, description, image_url FROM products");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Admin Control Panel</span>
            </div>
    </nav>

    <div class="container-fluid px-4">
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="text-primary-dark m-0">Inventory Management</h2>
            
            <div class="d-flex gap-2 align-items-center">
                <input type="text" id="productSearch" class="form-control" placeholder="Search products..." aria-label="Search products">
                
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
                            <th scope="col" class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="product-row">
                                    <td class="ps-4 fw-bold text-muted">#<?= htmlspecialchars($row['product_id']) ?></td>
                                    <td>
                                        <img src="/images/<?= htmlspecialchars($row['image_url']) ?>" alt="Product Image" class="product-thumbnail rounded">
                                    </td>
                                    <td class="product-name fw-semibold"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="product-desc text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($row['description']) ?>
                                    </td>
                                    <td class="fw-bold text-success-dark">
                                        $<?= number_format(htmlspecialchars($row['price']), 2) ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-secondary me-1">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No products found in the database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/dashboard.js"></script>
</body>
</html>