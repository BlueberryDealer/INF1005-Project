<?php
session_start();
/* TO DO

1)CHECK IF USER IS ADMIN - IF NOT, REDIRECT TO HOMEPAGE (SECURITY)
2)FIX AND TEST ADD PRODUCT FUNCTIONALITY (ADMIN ONLY) 
3)IMPLEMENT EDIT AND DELETE FUNCTIONALITY (ADMIN ONLY)
*/
 $config = parse_ini_file('/var/www/private/db-config.ini'); 
    if (!$config) 
    { 
        $errorMsg = "Failed to read database config file."; 
        $success = false; 
    } 
    else 
    { 
        $conn = new mysqli( 
            $config['servername'], 
            $config['username'], 
            $config['password'], 
            $config['dbname'] 
        ); 
 
        // Check connection 
        if ($conn->connect_error) 
        { 
            $errorMsg = "Connection failed: " . $conn->connect_error; 
            $success = false; 
        } 
        else 
        { 
            // Prepare the statement: 
            $stmt = $conn->prepare("SELECT * FROM products");

            // Bind & execute the query statement: 
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } 
 
        $conn->close(); 
    } 
?>


<!DOCTYPE html>
<html lang="en">
<?php
define('ROOT', $_SERVER['DOCUMENT_ROOT']);
include ROOT . "/components/header.php";
include ROOT . "/components/navbar.php";
?>
<div class="container mt-5">
    <h2 class="text-center mb-4">Our Products</h2>
    <div class="row" id="productList">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-sm-6 col-md-4 mb-4">
                    <div class="card h-100 shadow-sm product-card"
                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                        data-category="<?php echo htmlspecialchars($row['category'] ?? ''); ?>">
                        <img src="/images/<?php echo htmlspecialchars($row['image_url']); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($row['description']); ?></p>
                            <h6 class="text-primary">$<?php echo number_format($row['price'], 2); ?></h6>
                            
                            <div class="d-grid gap-2 mt-3">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-warning">Edit</a>
                                    <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-danger">Delete</a>
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

<?php include __DIR__ . "/../components/footer.php"; ?>
<script src="/js/main.js"></script>