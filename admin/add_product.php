<?php
session_start();
//require_once __DIR__ . '/../security/admin_guard.php';  
// 1. SECURITY CHECK: Only Admins allowed
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../index.php");
//     exit();
// }

    

$name = $price = $desc = $stock = $errorMsg = $successMsg = "";

// 2. PROCESS FORM ON SUBMIT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //$config = parse_ini_file('/var/www/private/db-config.ini'); prod
    //$config = parse_ini_file(__DIR__ . '/../db-config.ini'); test
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
    } else {
        // Sanitization & Validation
        $name = trim($_POST['name']);
        $price = $_POST['price'];
        $desc = trim($_POST['description']);
        $stock = $_POST['stock_quantity'];
        $image_url = $_POST['image_url']; // For now, manually typing the filename

        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection 
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
            if (empty($name) || empty($price)) {
                $errorMsg = "Product name and price are required.";
            } else {
                // 3. SECURE INSERT: Using Prepared Statements
                $stmt = $conn->prepare("INSERT INTO products (name, description, price,  image_url) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssds", $name, $desc, $price, $image_url);

                if ($stmt->execute()) {
                    $successMsg = "Product added successfully!";
                    // Reset fields
                    $name = $price = $desc = $stock = "";
                } else {
                    $errorMsg = "Database error: " . $stmt->error;
                }
                $stmt->close();
            }
        }

        $conn->close();
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
                            <div class="mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price ($) *</label>
                                    <input type="number" step="0.01" name="price" class="form-control"
                                        value="<?php echo $price; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Quantity</label>
                                    <input type="number" name="stock_quantity" class="form-control"
                                        value="<?php echo $stock; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control"
                                    rows="3"><?php echo htmlspecialchars($desc); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image Filename (e.g., dog_food.jpg)</label>
                                <input type="text" name="image_url" class="form-control"
                                    placeholder="Place image in /images folder first">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Save Product</button>
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