<?php
require_once __DIR__ . '/../security/session.php';
$session = new SessionManager();

$errorMsg = "";
$success = true;
$result = null;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$serverConfig = '/var/www/private/db-config.ini';
$localConfig  = __DIR__ . '/../config/db-config.ini';
$configPath = file_exists($serverConfig) ? $serverConfig : $localConfig;

$config = parse_ini_file($configPath);

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
        $stmt = $conn->prepare("SELECT * FROM products");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }

    $conn->close();
}
?>


<?php
include __DIR__ . "/../components/header.php";
include __DIR__ . "/../components/navbar.php";
?>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Our Products</h2>
    <div class="row" id="productList">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
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
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="btn btn-outline-warning">Edit</a>
                    <a href="delete_product.php?id=<?= $row['product_id'] ?>" class="btn btn-outline-danger">Delete</a>

                <?php elseif ($row['quantity'] <= 0): ?>
                    <!-- Out of stock — button disabled, cannot be clicked -->
                    <button class="btn btn-secondary" disabled>Unavailable</button>

                <?php else: ?>
                    <button class="btn btn-primary add-cart">Add to Cart</button>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No products found in our inventory.</p>

        <?php endif; ?>
    </div>
</div>
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($csrfToken) ?>">
<script src="/js/main.js"></script>
<script src="/js/cart.js"></script>
</body>
<?php include __DIR__ . "/../components/footer.php"; ?>
