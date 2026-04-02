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
$revenueTrend = [];
$revenueByProduct = [];

try {
    $summary = getSalesSummary();
    $topProducts = getTopSellingProducts(8);
    $revenueTrend = getDailyRevenueTrend(30);
    $revenueByProduct = getRevenueByProduct(6);
} catch (Throwable $e) {
    $errorMsg = 'Unable to load sales statistics right now.';
}

// Prepare chart data
$barLabels = [];
$barValues = [];
foreach ($topProducts as $product) {
    $barLabels[] = (string)$product['product_name'];
    $barValues[] = (int)$product['units_sold'];
}

$trendLabels = [];
$trendValues = [];
foreach ($revenueTrend as $day) {
    $trendLabels[] = date('d M', strtotime($day['order_date']));
    $trendValues[] = (float)$day['daily_revenue'];
}

$pieLabels = [];
$pieValues = [];
foreach ($revenueByProduct as $item) {
    $pieLabels[] = (string)$item['product_name'];
    $pieValues[] = (float)$item['revenue'];
}

include __DIR__ . '/../components/header.php';
?>
<a class="skip-link" href="#maincontent">Skip to main content</a>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<main id="maincontent" class="stats-page">
  <div class="container">

    <!-- Header -->
    <div class="stats-header">
      <div>
        <h1 class="section-title-bold">Sales Statistics</h1>
        <p class="stats-subtitle">Monitor product performance and identify your best-selling drinks.</p>
      </div>
      <div class="stats-actions">
        <a href="/admin/dashboard.php" class="stats-btn stats-btn--outline">Dashboard</a>
        <a href="/admin/order_history.php" class="stats-btn stats-btn--outline">Order History</a>
        <a href="/pages/products.php" class="stats-btn stats-btn--primary">Back to Store</a>
      </div>
    </div>

    <?php if ($errorMsg !== ''): ?>
      <div class="alert alert-danger" role="alert">
        <?= Sanitizer::escape($errorMsg) ?>
      </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="stats-kpi-grid">
      <div class="stats-kpi">
        <div class="stats-kpi-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.8"/></svg>
        </div>
        <div class="stats-kpi-content">
          <span class="stats-kpi-label">Total Orders</span>
          <span class="stats-kpi-value"><?= number_format((int)$summary['total_orders']) ?></span>
        </div>
      </div>

      <div class="stats-kpi">
        <div class="stats-kpi-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M20 12V22H4V12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 7H2V12H22V7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="stats-kpi-content">
          <span class="stats-kpi-label">Units Sold</span>
          <span class="stats-kpi-value"><?= number_format((int)$summary['units_sold']) ?></span>
        </div>
      </div>

      <div class="stats-kpi">
        <div class="stats-kpi-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="stats-kpi-content">
          <span class="stats-kpi-label">Revenue</span>
          <span class="stats-kpi-value">$<?= number_format((float)$summary['total_revenue'], 2) ?></span>
        </div>
      </div>

      <div class="stats-kpi">
        <div class="stats-kpi-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="stats-kpi-content">
          <span class="stats-kpi-label">Top Product</span>
          <?php if (!empty($summary['top_product'])): ?>
            <span class="stats-kpi-value stats-kpi-value--sm"><?= Sanitizer::escape((string)$summary['top_product']['product_name']) ?></span>
            <span class="stats-kpi-sub"><?= number_format((int)$summary['top_product']['units_sold']) ?> units sold</span>
          <?php else: ?>
            <span class="stats-kpi-value stats-kpi-value--sm">No sales yet</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Charts Row 1: Revenue Trend -->
    <?php if (!empty($revenueTrend)): ?>
    <div class="stats-chart-card">
      <div class="stats-chart-header">
        <h2 class="stats-chart-title">Revenue Trend</h2>
        <span class="stats-chart-note">Last 30 days</span>
      </div>
      <div class="stats-chart-body stats-chart-body--wide">
        <canvas id="trendChart" aria-label="Revenue trend chart" role="img"></canvas>
      </div>
    </div>
    <?php endif; ?>

    <!-- Charts Row 2: Bar + Doughnut -->
    <div class="stats-chart-row">
      <div class="stats-chart-card stats-chart-card--bar">
        <div class="stats-chart-header">
          <h2 class="stats-chart-title">Top-Selling Products</h2>
          <span class="stats-chart-note">By units sold</span>
        </div>
        <?php if (empty($topProducts)): ?>
          <div class="stats-chart-empty">No sales data available yet.</div>
        <?php else: ?>
          <div class="stats-chart-body">
            <canvas id="barChart" aria-label="Top selling products chart" role="img"></canvas>
          </div>
        <?php endif; ?>
      </div>

      <div class="stats-chart-card stats-chart-card--pie">
        <div class="stats-chart-header">
          <h2 class="stats-chart-title">Revenue Breakdown</h2>
          <span class="stats-chart-note">By product</span>
        </div>
        <?php if (empty($revenueByProduct)): ?>
          <div class="stats-chart-empty">No revenue data available yet.</div>
        <?php else: ?>
          <div class="stats-chart-body stats-chart-body--square">
            <canvas id="pieChart" aria-label="Revenue breakdown chart" role="img"></canvas>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Products Table -->
    <?php if (!empty($topProducts)): ?>
    <div class="stats-chart-card">
      <div class="stats-chart-header">
        <h2 class="stats-chart-title">Product Performance</h2>
        <span class="stats-chart-note">Ranked by units sold</span>
      </div>
      <div class="stats-table-wrap">
        <table class="stats-table" aria-label="Product performance table">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Product</th>
              <th scope="col" class="text-end">Orders</th>
              <th scope="col" class="text-end">Units</th>
              <th scope="col" class="text-end">Revenue</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topProducts as $i => $product): ?>
              <tr>
                <td class="stats-table-rank"><?= $i + 1 ?></td>
                <td class="stats-table-name"><?= Sanitizer::escape((string)$product['product_name']) ?></td>
                <td class="text-end"><?= number_format((int)$product['order_count']) ?></td>
                <td class="text-end stats-fw-600"><?= number_format((int)$product['units_sold']) ?></td>
                <td class="text-end stats-fw-600">$<?= number_format((float)$product['revenue'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</main>


<?php if (!empty($topProducts) || !empty($revenueTrend) || !empty($revenueByProduct)): ?>
<script>
(function() {
  var trendLabels = <?= json_encode($trendLabels, JSON_HEX_TAG) ?>;
  var trendValues = <?= json_encode($trendValues) ?>;
  var barLabels = <?= json_encode($barLabels, JSON_HEX_TAG) ?>;
  var barValues = <?= json_encode($barValues) ?>;
  var pieLabels = <?= json_encode($pieLabels, JSON_HEX_TAG) ?>;
  var pieValues = <?= json_encode($pieValues) ?>;
  var barCount = <?= count($barLabels) ?>;
  var pieCount = <?= count($pieLabels) ?>;

  var palette = ["#22d3ee", "#8b5cf6", "#f59e0b", "#22c55e", "#ef4444", "#ec4899", "#3b82f6", "#f97316"];
  var charts = {};

  function getThemeColors() {
    var isDark = document.documentElement.getAttribute("data-theme") === "dark";
    return {
      isDark: isDark,
      tick: isDark ? "#fff" : "#333",
      grid: isDark ? "rgba(255,255,255,0.15)" : "rgba(0,0,0,0.12)",
      border: isDark ? "rgba(255,255,255,0.2)" : "rgba(0,0,0,0.15)",
      line: isDark ? "#22d3ee" : "#6366f1",
      cardBg: isDark ? "#151515" : "#fff",
      tooltipBg: isDark ? "#1e1e1e" : "#fff",
      tooltipTitle: isDark ? "#fff" : "#111",
      tooltipBody: isDark ? "#ddd" : "#555",
      tooltipBorder: isDark ? "rgba(255,255,255,0.12)" : "#ddd",
      doughnutBorder: isDark ? "#151515" : "#fff"
    };
  }

  function buildCharts() {
    // Destroy existing charts
    Object.keys(charts).forEach(function(k) { if (charts[k]) charts[k].destroy(); });
    charts = {};

    var c = getThemeColors();

    Chart.defaults.color = c.tick;
    Chart.defaults.font.family = "'DM Sans', sans-serif";
    Chart.defaults.font.size = 13;

    var tooltip = {
      backgroundColor: c.tooltipBg,
      titleColor: c.tooltipTitle,
      bodyColor: c.tooltipBody,
      borderColor: c.tooltipBorder,
      borderWidth: 1, padding: 12, cornerRadius: 8,
      titleFont: { weight: "700" }
    };

    // ── Revenue Trend ──
    var trendEl = document.getElementById("trendChart");
    if (trendEl) {
      var ctx = trendEl.getContext("2d");
      var grad = ctx.createLinearGradient(0, 0, 0, 300);
      grad.addColorStop(0, c.isDark ? "rgba(34,211,238,0.3)" : "rgba(99,102,241,0.18)");
      grad.addColorStop(1, "rgba(0,0,0,0)");

      charts.trend = new Chart(ctx, {
        type: "line",
        data: {
          labels: trendLabels,
          datasets: [{
            data: trendValues,
            borderColor: c.line,
            backgroundColor: grad,
            borderWidth: 2.5, tension: 0.4, fill: true,
            pointRadius: 5, pointBackgroundColor: c.line,
            pointBorderColor: c.cardBg, pointBorderWidth: 2.5, pointHoverRadius: 7
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          scales: {
            x: {
              ticks: { color: c.tick, font: { size: 12, weight: "600" }, maxRotation: 45 },
              grid: { display: false }, border: { color: c.border }
            },
            y: {
              ticks: { color: c.tick, font: { size: 12, weight: "600" },
                callback: function(v) { return "$" + v.toLocaleString(); } },
              grid: { color: c.grid }, border: { display: false }, beginAtZero: true
            }
          },
          plugins: {
            legend: { display: false },
            tooltip: Object.assign({}, tooltip, {
              callbacks: { label: function(x) { return "Revenue: $" + x.parsed.y.toLocaleString(undefined,{minimumFractionDigits:2}); } }
            })
          }
        }
      });
    }

    // ── Bar Chart ──
    var barEl = document.getElementById("barChart");
    if (barEl) {
      charts.bar = new Chart(barEl.getContext("2d"), {
        type: "bar",
        data: {
          labels: barLabels,
          datasets: [{ data: barValues, backgroundColor: palette.slice(0, barCount),
            borderRadius: 6, borderSkipped: false, maxBarThickness: 44 }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, indexAxis: "y",
          layout: { padding: { left: 4, right: 16 } },
          scales: {
            x: { ticks: { color: c.tick, font: { size: 12, weight: "600" }, stepSize: 1 },
              grid: { color: c.grid }, border: { display: false }, beginAtZero: true },
            y: { ticks: { color: c.tick, font: { size: 13, weight: "700" }, autoSkip: false },
              grid: { display: false }, border: { color: c.border },
              afterFit: function(a) { a.width = Math.max(a.width, 130); } }
          },
          plugins: {
            legend: { display: false },
            tooltip: Object.assign({}, tooltip, {
              callbacks: { label: function(x) { return x.parsed.x + " units sold"; } }
            })
          }
        }
      });
    }

    // ── Doughnut ──
    var pieEl = document.getElementById("pieChart");
    if (pieEl) {
      charts.pie = new Chart(pieEl.getContext("2d"), {
        type: "doughnut",
        data: {
          labels: pieLabels,
          datasets: [{ data: pieValues, backgroundColor: palette.slice(0, pieCount),
            borderColor: c.doughnutBorder, borderWidth: 3, hoverOffset: 10 }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, cutout: "62%",
          plugins: {
            legend: { position: "bottom", labels: { color: c.tick,
              font: { size: 13, weight: "700", family: "'DM Sans', sans-serif" },
              padding: 16, usePointStyle: true, pointStyle: "circle" } },
            tooltip: Object.assign({}, tooltip, {
              callbacks: { label: function(x) { return x.label + ": $" + x.parsed.toLocaleString(undefined,{minimumFractionDigits:2}); } }
            })
          }
        }
      });
    }
  }

  // Build on load
  document.addEventListener("DOMContentLoaded", buildCharts);

  // Rebuild on theme toggle
  var themeBtn = document.getElementById("themeToggle");
  if (themeBtn) {
    themeBtn.addEventListener("click", function() {
      setTimeout(buildCharts, 50);
    });
  }
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>