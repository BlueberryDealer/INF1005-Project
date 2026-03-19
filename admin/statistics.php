<?php
require_once __DIR__ . '/../security/admin_guard.php';
require_once __DIR__ . '/../models/order_model.php';
require_once __DIR__ . '/../security/sanitization.php';

$errorMsg = '';
$summary = [
    'total_orders' => 0,
    'total_revenue' => 0.0,
    'units_sold' => 0,
    'top_product' => null,
];
$topProducts = [];

try {
    $summary = getSalesSummary();
    $topProducts = getTopSellingProducts(8);
} catch (Throwable $e) {
    $errorMsg = 'Unable to load sales statistics right now.';
}

$chartLabels = [];
$chartValues = [];

foreach ($topProducts as $product) {
    $chartLabels[] = (string)$product['product_name'];
    $chartValues[] = (int)$product['units_sold'];
}

include __DIR__ . '/../components/header.php';
?>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <h1 class="mb-1">Sales Statistics</h1>
      <p class="text-muted mb-0">Monitor product performance and identify your best-selling drinks.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/admin/dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
      <a href="/pages/products.php" class="btn btn-primary">Back to Store</a>
    </div>
  </div>

  <?php if ($errorMsg !== ''): ?>
    <div class="alert alert-danger" role="alert">
      <?= Sanitizer::escape($errorMsg) ?>
    </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body">
          <div class="text-muted text-uppercase small fw-semibold mb-2">Total Orders</div>
          <div class="display-6 fw-bold"><?= number_format((int)$summary['total_orders']) ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body">
          <div class="text-muted text-uppercase small fw-semibold mb-2">Units Sold</div>
          <div class="display-6 fw-bold"><?= number_format((int)$summary['units_sold']) ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body">
          <div class="text-muted text-uppercase small fw-semibold mb-2">Revenue</div>
          <div class="display-6 fw-bold">$<?= number_format((float)$summary['total_revenue'], 2) ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-body">
          <div class="text-muted text-uppercase small fw-semibold mb-2">Top Product</div>
          <?php if (!empty($summary['top_product'])): ?>
            <div class="fw-bold fs-4 mb-1"><?= Sanitizer::escape((string)$summary['top_product']['product_name']) ?></div>
            <div class="text-muted"><?= number_format((int)$summary['top_product']['units_sold']) ?> units sold</div>
          <?php else: ?>
            <div class="fw-bold fs-4 mb-1">No sales yet</div>
            <div class="text-muted">Add orders to see top performers.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h2 class="h4 mb-0">Top-Selling Products Graph</h2>
            <span class="text-muted small">Based on total units sold</span>
          </div>

          <?php if (empty($topProducts)): ?>
            <div class="text-center py-5 text-muted">
              No sales data available yet. Once orders are placed, the graph will appear here.
            </div>
          <?php else: ?>
            <div id="salesChartWrap" style="height: 440px;">
              <canvas
                id="salesChart"
                aria-label="Top-selling products chart"
                role="img"
                style="display:block; width:100%; height:100%;"
              ></canvas>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <h2 class="h4 mb-3">Top Products Table</h2>

          <?php if (empty($topProducts)): ?>
            <p class="text-muted mb-0">No product sales have been recorded yet.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Product</th>
                    <th scope="col" class="text-end">Units</th>
                    <th scope="col" class="text-end">Revenue</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topProducts as $product): ?>
                    <tr>
                      <td>
                        <div class="fw-semibold"><?= Sanitizer::escape((string)$product['product_name']) ?></div>
                        <div class="text-muted small"><?= number_format((int)$product['order_count']) ?> order(s)</div>
                      </td>
                      <td class="text-end fw-semibold"><?= number_format((int)$product['units_sold']) ?></td>
                      <td class="text-end">$<?= number_format((float)$product['revenue'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php if (!empty($topProducts)): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const canvas = document.getElementById("salesChart");
  const chartWrap = document.getElementById("salesChartWrap");
  if (!canvas) return;

  const labels = <?= json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const values = <?= json_encode($chartValues) ?>;
  const ctx = canvas.getContext("2d");

  function drawChart() {
    const width = chartWrap?.clientWidth || canvas.clientWidth || 640;
    const height = chartWrap?.clientHeight || canvas.clientHeight || 440;
    const ratio = window.devicePixelRatio || 1;

    canvas.width = width * ratio;
    canvas.height = height * ratio;
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(ratio, ratio);

    const theme = document.documentElement.getAttribute("data-theme");
    const axisColor = theme === "dark" ? "rgba(255,255,255,0.35)" : "rgba(17,17,17,0.2)";
    const textColor = theme === "dark" ? "rgba(255,255,255,0.82)" : "#222";
    const gridColor = theme === "dark" ? "rgba(255,255,255,0.08)" : "rgba(17,17,17,0.08)";
    const barColor = theme === "dark" ? "#1a9e78" : "#0f7a5f";

    const isMobile = width < 640;
    const padding = isMobile
      ? { top: 24, right: 12, bottom: 110, left: 42 }
      : { top: 28, right: 28, bottom: 98, left: 56 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;
    const maxValue = Math.max(...values, 1);
    const barGap = isMobile ? 12 : 18;
    const barWidth = Math.max(28, (chartWidth - barGap * (values.length - 1)) / values.length);
    const fontSize = isMobile ? 11 : 13;

    ctx.clearRect(0, 0, width, height);
    ctx.font = `${fontSize}px Inter, sans-serif`;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";

    for (let i = 0; i <= 4; i++) {
      const y = padding.top + (chartHeight / 4) * i;
      ctx.strokeStyle = gridColor;
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(padding.left, y);
      ctx.lineTo(width - padding.right, y);
      ctx.stroke();

      const labelValue = Math.round(maxValue - (maxValue / 4) * i);
      ctx.fillStyle = textColor;
      ctx.textAlign = "right";
      ctx.fillText(String(labelValue), padding.left - 8, y);
    }

    ctx.strokeStyle = axisColor;
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(padding.left, padding.top);
    ctx.lineTo(padding.left, padding.top + chartHeight);
    ctx.lineTo(width - padding.right, padding.top + chartHeight);
    ctx.stroke();

    values.forEach(function (value, index) {
      const barHeight = (value / maxValue) * (chartHeight - 16);
      const x = padding.left + index * (barWidth + barGap);
      const y = padding.top + chartHeight - barHeight;

      ctx.fillStyle = barColor;
      ctx.beginPath();
      ctx.roundRect(x, y, barWidth, barHeight, 10);
      ctx.fill();

      ctx.fillStyle = textColor;
      ctx.textAlign = "center";
      ctx.fillText(String(value), x + barWidth / 2, y - 12);

      ctx.save();
      ctx.translate(x + barWidth / 2, padding.top + chartHeight + 22);
      ctx.rotate(isMobile ? -0.7 : -0.5);
      ctx.textAlign = "right";
      ctx.fillText(labels[index], 0, 0);
      ctx.restore();
    });
  }

  drawChart();
  window.addEventListener("resize", drawChart);
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>
