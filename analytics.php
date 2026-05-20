<?php
/**
 * PNS Mega Store — Analytics
 *
 * Summary statistics, category breakdown, stock health overview,
 * and top/bottom performing products by value.
 */

require_once 'includes/db.php';

$page_title   = 'Analytics';
$current_page = 'analytics';

// ── Fetch all items ───────────────────────────────────────
$result    = $conn->query("SELECT * FROM pns_inventory ORDER BY id ASC");
$all_items = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_items[] = $row;
    }
}

// ── Aggregate stats ───────────────────────────────────────
$total_products  = count($all_items);
$total_value     = 0;
$out_of_stock    = 0;
$low_stock       = 0;
$in_stock        = 0;
$mini_count      = 0;
$category_data   = [];
$top_by_value    = [];

foreach ($all_items as $item) {
    $stock = (int)$item['current_stock'];
    $min   = (int)$item['minimum_stock'];
    $price = (float)$item['price'];
    $val   = $price * $stock;

    $total_value += $val;

    if ($stock === 0)     $out_of_stock++;
    elseif ($stock <= $min) $low_stock++;
    else                  $in_stock++;

    if ($price <= 5000)   $mini_count++;

    $cat = $item['category'];
    if (!isset($category_data[$cat])) {
        $category_data[$cat] = ['count' => 0, 'value' => 0];
    }
    $category_data[$cat]['count']++;
    $category_data[$cat]['value'] += $val;

    $top_by_value[] = [
        'name'  => $item['product_name'],
        'cat'   => $cat,
        'value' => $val,
        'stock' => $stock,
        'price' => $price,
    ];
}

// Sort by total value desc
usort($top_by_value, fn($a, $b) => $b['value'] <=> $a['value']);

// Category colors for display
$cat_colors = [
    'Concession'              => '#60a5fa',
    'Mini'                    => '#a78bfa',
    'In-house Reuse'          => '#34d399',
    'Vintages'                => '#fbbf24',
    'Reduced'                 => '#f87171',
    'Original & Classic by PNS' => '#f472b6',
];

$max_cat_value = max(array_column($category_data, 'value') ?: [1]);

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <h1>Analytics</h1>
  <p>Inventory performance and category insights</p>
</div>

<!-- Summary Metrics -->
<div class="metrics-grid" style="margin-bottom:1.5rem;">
  <div class="metric-card">
    <div class="metric-icon purple">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0h-17.25"/>
      </svg>
    </div>
    <div class="metric-label">Total Products</div>
    <div class="metric-value"><?php echo $total_products; ?></div>
    <div class="metric-desc">Across <?php echo count($category_data); ?> categories</div>
  </div>
  <div class="metric-card">
    <div class="metric-icon green">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75
             M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25
             M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75
             c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375
             a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
      </svg>
    </div>
    <div class="metric-label">Total Inventory Value</div>
    <div class="metric-value" style="font-size:1.35rem;">&#8358;<?php echo number_format($total_value, 2); ?></div>
    <div class="metric-desc">All stock × price</div>
  </div>
  <div class="metric-card">
    <div class="metric-icon yellow">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374
             L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
      </svg>
    </div>
    <div class="metric-label">Stock Health</div>
    <div class="metric-value"><?php echo $in_stock; ?><span style="font-size:1rem;color:var(--text-muted);">/<?php echo $total_products; ?></span></div>
    <div class="metric-desc"><?php echo $low_stock; ?> low · <?php echo $out_of_stock; ?> out of stock</div>
  </div>
  <div class="metric-card">
    <div class="metric-icon red">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581
             c.699.699 1.78.872 2.648.43a18.849 18.849 0 005.441-5.44c.444-.869.272-1.949-.428-2.649L10.16 3.659
             A2.25 2.25 0 008.568 3H9.568zM6 6h.008v.008H6V6z"/>
      </svg>
    </div>
    <div class="metric-label">Mini Items</div>
    <div class="metric-value"><?php echo $mini_count; ?></div>
    <div class="metric-desc">Priced at &#8358;5,000 or below</div>
  </div>
</div>

<!-- Two-column: Category Breakdown + Stock Health -->
<div class="analytics-grid">

  <!-- Category Breakdown by Value -->
  <div class="card">
    <div class="card-header">
      <h3>Category Value Breakdown</h3>
      <p>Total inventory value per category</p>
    </div>
    <div class="card-body">
      <div class="bar-chart">
        <?php foreach ($category_data as $cat => $data):
          $pct   = $max_cat_value > 0 ? round(($data['value'] / $max_cat_value) * 100) : 0;
          $color = $cat_colors[$cat] ?? '#5b8af5';
        ?>
        <div class="bar-row">
          <div class="bar-label">
            <span><?php echo htmlspecialchars($cat); ?></span>
            <span>&#8358;<?php echo number_format($data['value'], 0); ?> (<?php echo $data['count']; ?> items)</span>
          </div>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Stock Health Donut (simple bars) -->
  <div class="card">
    <div class="card-header">
      <h3>Stock Health Overview</h3>
      <p>Distribution of product stock statuses</p>
    </div>
    <div class="card-body">
      <?php
        $health = [
          ['label' => 'In Stock',     'count' => $in_stock,    'color' => '#34d399'],
          ['label' => 'Low Stock',    'count' => $low_stock,   'color' => '#fbbf24'],
          ['label' => 'Out of Stock', 'count' => $out_of_stock,'color' => '#f87171'],
        ];
      ?>
      <div class="bar-chart">
        <?php foreach ($health as $h):
          $pct = $total_products > 0 ? round(($h['count'] / $total_products) * 100) : 0;
        ?>
        <div class="bar-row">
          <div class="bar-label">
            <span><?php echo $h['label']; ?></span>
            <span><?php echo $h['count']; ?> (<?php echo $pct; ?>%)</span>
          </div>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $h['color']; ?>;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<!-- Top Products by Total Value -->
<div class="card" style="margin-top:1rem;">
  <div class="card-header">
    <h3>Products by Inventory Value</h3>
    <p>Ranked by (price × current stock)</p>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>Product</th>
          <th>Category</th>
          <th>Stock</th>
          <th>Unit Price (&#8358;)</th>
          <th>Total Value (&#8358;)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($top_by_value)): ?>
          <tr><td colspan="6" class="table-empty">No products yet</td></tr>
        <?php else: ?>
          <?php foreach ($top_by_value as $i => $p):
            $cat_class_map = [
              'Concession'              => 'cat-concession',
              'Mini'                    => 'cat-mini',
              'In-house Reuse'          => 'cat-reuse',
              'Vintages'                => 'cat-vintages',
              'Reduced'                 => 'cat-reduced',
              'Original & Classic by PNS' => 'cat-original',
            ];
            $cc = $cat_class_map[$p['cat']] ?? '';
          ?>
          <tr>
            <td style="color:var(--text-muted);font-weight:700;">#<?php echo $i + 1; ?></td>
            <td style="font-weight:500;color:var(--text-heading);">
              <?php echo htmlspecialchars($p['name']); ?>
              <?php if ($p['price'] <= 5000): ?>
                <span class="mini-tag">MINI</span>
              <?php endif; ?>
            </td>
            <td><span class="cat-badge <?php echo $cc; ?>"><?php echo htmlspecialchars($p['cat']); ?></span></td>
            <td><?php echo $p['stock']; ?></td>
            <td><?php echo number_format($p['price'], 2); ?></td>
            <td style="font-weight:600;color:var(--green);"><?php echo number_format($p['value'], 2); ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
