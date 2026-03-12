<?php
//session_start();
//require_once __DIR__ . '/../security/admin_guard.php'; 

// SECURITY CHECK: Only Admins allowed
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../index.php");
//     exit();
// }

// Handle DELETE action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int) $_GET['id'];
    $config = parse_ini_file(__DIR__ . '/../db-config.ini');
    if ($config) {
        $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
        if (!$conn->connect_error) {
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $deleteId);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
    header("Location: dashboard.php?deleted=1");
    exit();
}

// Fetch all products
$products = [];
$errorMsg = "";

$config = parse_ini_file(__DIR__ . '/../db-config.ini');
if (!$config) {
    $errorMsg = "Failed to read database config file.";
} else {
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    if ($conn->connect_error) {
        $errorMsg = "Connection failed: " . $conn->connect_error;
    } else {
        $stmt = $conn->prepare("SELECT product_id, name, price, description, image_url FROM products ORDER BY product_id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
        $conn->close();
    }
}

$totalProducts = count($products);
$avgPrice = $totalProducts > 0 ? array_sum(array_column($products, 'price')) / $totalProducts : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Product Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>

<!-- ░░ SIDEBAR ░░ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-dot"></span>
        <span class="brand-name">ADMIN</span>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item active">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Products
        </a>
        <a href="../index.php" class="nav-item">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Back to Site
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-avatar">
            <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
        </div>
        <div class="admin-info">
            <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            <span class="admin-role">Administrator</span>
        </div>
    </div>
</aside>

<!-- ░░ MAIN CONTENT ░░ -->
<main class="main-content">

    <!-- Top bar -->
    <header class="topbar">
        <div class="topbar-left">
            <h1 class="page-title">Products</h1>
            <span class="page-subtitle">Manage your inventory</span>
        </div>
        <div class="topbar-right">
            <a href="add_product.php" class="btn-add">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Product
            </a>
        </div>
    </header>

    <!-- Stats row -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-label">Total Products</span>
            <span class="stat-value"><?php echo $totalProducts; ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Avg. Price</span>
            <span class="stat-value">$<?php echo number_format($avgPrice, 2); ?></span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Last Updated</span>
            <span class="stat-value date-value"><?php echo date('M j, Y'); ?></span>
        </div>
    </div>

    <!-- Toast notifications -->
    <?php if (isset($_GET['deleted'])): ?>
        <div class="toast toast-success" id="toast">Product deleted successfully.</div>
    <?php elseif (isset($_GET['added'])): ?>
        <div class="toast toast-success" id="toast">Product added successfully.</div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="toast toast-success" id="toast">Product updated successfully.</div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="toast toast-error"><?php echo htmlspecialchars($errorMsg); ?></div>
    <?php endif; ?>

    <!-- Search & filter bar -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" class="search-input" placeholder="Search products…">
        </div>
        <span class="table-count"><span id="visibleCount"><?php echo $totalProducts; ?></span> products</span>
    </div>

    <!-- Product Table -->
    <div class="table-wrapper">
        <?php if (empty($products) && !$errorMsg): ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <p>No products yet. <a href="add_product.php">Add your first one →</a></p>
            </div>
        <?php else: ?>
        <table class="product-table" id="productTable">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-image">Image</th>
                    <th class="col-name">Name</th>
                    <th class="col-desc">Description</th>
                    <th class="col-price">Price</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php foreach ($products as $index => $product): ?>
                <tr class="table-row" style="--row-index: <?php echo $index; ?>">
                    <td class="col-id">
                        <span class="id-badge">#<?php echo htmlspecialchars($product['product_id']); ?></span>
                    </td>
                    <td class="col-image">
                        <div class="product-thumb">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="/images/<?php echo htmlspecialchars($product['image_url']); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div class="thumb-fallback" style="display:none">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                            <?php else: ?>
                                <div class="thumb-fallback">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="col-name">
                        <span class="product-name"><?php echo htmlspecialchars($product['name']); ?></span>
                    </td>
                    <td class="col-desc">
                        <span class="product-desc"><?php echo htmlspecialchars($product['description'] ?? '—'); ?></span>
                    </td>
                    <td class="col-price">
                        <span class="price-tag">$<?php echo number_format($product['price'], 2); ?></span>
                    </td>
                    <td class="col-actions">
                        <div class="action-group">
                            <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn-edit" title="Edit product">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </a>
                            <button class="btn-delete"
                                    data-id="<?php echo $product['product_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    title="Delete product">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</main>

<!-- ░░ DELETE CONFIRMATION MODAL ░░ -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modal">
        <div class="modal-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h3 class="modal-title">Delete Product?</h3>
        <p class="modal-body">You're about to delete <strong id="modalProductName"></strong>. This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn-modal-cancel" id="modalCancel">Cancel</button>
            <a href="#" class="btn-modal-confirm" id="modalConfirm">Delete</a>
        </div>
    </div>
</div>

<script src="/js/dashboard.js"></script>
</body>
</html>