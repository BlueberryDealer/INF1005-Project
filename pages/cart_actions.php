<?php
// -------------------------------------------------------
// ** THIS IS AN EXTENTSION FOR cart.php AND NOT TO BE DISPLAYED ON THE WEBPAGE **
// cart_actions.php
// Handles all POST actions for the session cart.
// Called via form POST or fetch() from main.js (Role 5).
// -------------------------------------------------------

session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/models/order_model.php';

// ---------- CSRF helper (mirrors security.php from Role 3) ----------
// If Role 3 provides a security.php with generateCsrfToken() and
// verifyCsrfToken(), replace these two functions with:
//   require_once __DIR__ . '/components/security.php';

function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}
// --------------------------------------------------------------------

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

$action    = $_POST['action']     ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$quantity  = (int)($_POST['quantity']   ?? 1);

// Initialise cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {

    // --------------------------------------------------
    case 'add':
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        // Verify product exists and has stock
        $products = getProductsByIds([$productId]);
        if (empty($products)) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }
        $product = $products[0];

        $currentQty = $_SESSION['cart'][$productId] ?? 0;
        $newQty     = $currentQty + max(1, $quantity);

        $_SESSION['cart'][$productId] = $newQty;

        echo json_encode([
            'success'    => true,
            'message'    => htmlspecialchars($product['name']) . ' added to cart.',
            'cart_count' => array_sum($_SESSION['cart']),
        ]);
        break;

    // --------------------------------------------------
    case 'update':
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        if ($quantity <= 0) {
            // Treat quantity 0 as remove
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        echo json_encode([
            'success'    => true,
            'cart_count' => array_sum($_SESSION['cart']),
        ]);
        break;

    // --------------------------------------------------
    case 'remove':
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        echo json_encode([
            'success'    => true,
            'cart_count' => array_sum($_SESSION['cart']),
        ]);
        break;

    // --------------------------------------------------
    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true, 'cart_count' => 0]);
        break;

    // --------------------------------------------------
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}