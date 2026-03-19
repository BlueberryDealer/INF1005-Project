<?php
require_once __DIR__ . '/../security/auth_guard.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/sanitization.php';

$userId = $session->getUserId();
if (!$userId) {
    $_SESSION['flash_error'] = 'Please log in again.';
    header('Location: /auth/login.php');
    exit;
}

$orders = getOrdersByUserId($userId);

include __DIR__ . '/../components/header.php';
include __DIR__ . '/../components/navbar.php';
?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <h1 class="mb-1">My Orders</h1>
      <p class="text-muted mb-0">Review your previous purchases and open any order for details.</p>
    </div>
    <a href="/account/userProfile.php" class="btn btn-outline-secondary">Back to Profile</a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="card">
      <div class="card-body py-5 text-center">
        <h2 class="h4 mb-2">No orders yet</h2>
        <p class="text-muted mb-3">Once you complete checkout, your orders will appear here.</p>
        <a href="/pages/products.php" class="btn btn-primary">Start Shopping</a>
      </div>
    </div>
  <?php else: ?>
    <div class="card shadow-sm border-0">
      <div class="card-body p-0 table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col">Order</th>
              <th scope="col">Date</th>
              <th scope="col">Status</th>
              <th scope="col" class="text-end">Total</th>
              <th scope="col" class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td class="fw-semibold">#<?= (int)$order['id'] ?></td>
                <td><?= Sanitizer::escape(date('d M Y, g:i A', strtotime((string)$order['created_at']))) ?></td>
                <td>
                  <span class="badge bg-success text-capitalize">
                    <?= Sanitizer::escape((string)($order['status'] ?? 'placed')) ?>
                  </span>
                </td>
                <td class="text-end fw-semibold">$<?= number_format((float)$order['total_amount'], 2) ?></td>
                <td class="text-end">
                  <a href="/pages/order_confirm.php?order_id=<?= (int)$order['id'] ?>" class="btn btn-sm btn-outline-primary">View Order</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>
