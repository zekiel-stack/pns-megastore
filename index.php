<?php
/**
 * PNS Mega Store — Dashboard
 *
 * Overview page with metric cards, shelf advisory alerts,
 * recent activity, and quick action links.
 */

require_once 'includes/db.php';

$page_title   = 'Dashboard';
$current_page = 'dashboard';

// ── Metrics ──────────────────────────────────────────────
$total_products  = 0;
$low_stock       = 0;
$out_of_stock    = 0;
$total_value     = 0;
$shelf_alerts    = [];
$recent          = [];
$category_counts = [];

$result = $conn->query("SELECT * FROM pns_inventory ORDER BY updated_at DESC");

if ($result && $result->num_rows > 0) {
    $all_items = [];
    while ($row = $result->fetch_assoc()) {
        $all_items[] = $row;
    }

    $total_products = count($all_items);

    foreach ($all_items as $item) {
        $stock = (int)$item['current_stock'];
        $min   = (int)$item['minimum_stock'];
        $shelf = (int)$item['shelf_qty'];
        $price = (float)$item['price'];

        // Stock status counts
        if ($stock === 0) {
            $out_of_stock++;
        } elseif ($stock <= $min) {
            $low_stock++;
        }

        // Total value
        $total_value += $price * $stock;

        // Shelf advisory: items with 3 or fewer on shelf
        if ($shelf <= 3) {
            $shelf_alerts[] = $item;
        }

        // Category counts
        $cat = $item['category'];
        if (!isset($category_counts[$cat])) $category_counts[$cat] = 0;
        $category_counts[$cat]++;
    }

    // Recent activity: first 8 items (already sorted by updated_at DESC)
    $recent = array_slice($all_items, 0, 8);
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <h1>Dashboard</h1>
  <p>Welcome to PNS Mega Store Inventory Management</p>
</div>

<!-- Flash Messages -->
<?php if (isset($_GET['msg'])): ?>
<div class="flash-message flash-success">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
  </svg>
  <?php echo htmlspecialchars($_GET['msg']); ?>
</div>
<?php endif; ?>

<!-- Metric Cards -->
<div class="metrics-grid">

  <!-- Total Products -->
  <div class="metric-card">
    <div class="metric-icon purple">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0h-17.25"/>
      </svg>
    </div>
    <div class="metric-label">Total Products</div>
    <div class="metric-value"><?php echo $total_products; ?></div>
    <div class="metric-desc"><?php echo $low_stock; ?> in low or critical stock</div>
  </div>

  <!-- Low Stock -->
  <div class="metric-card">
    <div class="metric-icon yellow">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374
             L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
      </svg>
    </div>
    <div class="metric-label">Low Stock Items</div>
    <div class="metric-value text-warning"><?php echo $low_stock; ?></div>
    <div class="metric-desc">Items below minimum threshold</div>
  </div>

  <!-- Total Value -->
  <div class="metric-card">
    <div class="metric-icon green">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75
             M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25
             M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75
             c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375
             a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75
             M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
      </svg>
    </div>
    <div class="metric-label">Total Value</div>
    <div class="metric-value">&#8358;<?php echo number_format($total_value, 2); ?></div>
    <div class="metric-desc">Total inventory worth</div>
  </div>

  <!-- Out of Stock -->
  <div class="metric-card">
    <div class="metric-icon red">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
      </svg>
    </div>
    <div class="metric-label">Out of Stock</div>
    <div class="metric-value text-danger"><?php echo $out_of_stock; ?></div>
    <div class="metric-desc">Must restock immediately</div>
  </div>

</div>

<!-- Two-column: Quick Actions + Shelf Advisory -->
<div class="grid-2 mb-md">

  <!-- Quick Actions -->
  <div class="card">
    <div class="card-header">
      <h3>Quick Actions</h3>
      <p>Common inventory tasks</p>
    </div>
    <div class="card-body">
      <div class="quick-actions">
        <a href="add_item.php" class="btn btn-primary btn-block">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
          </svg>
          Add New Item
        </a>
        <a href="export_csv.php" class="btn btn-secondary btn-block">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
          </svg>
          Export CSV
        </a>
        <a href="analytics.php" class="btn btn-secondary btn-block">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75
                 C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/>
          </svg>
          View Analytics
        </a>
      </div>
    </div>
  </div>

  <!-- Shelf Advisory Alerts -->
  <div class="card">
    <div class="card-header">
      <h3>Shelf Advisory</h3>
      <p>Items with low shelf quantities (≤ 3 on shelf)</p>
    </div>
    <div class="card-body">
      <?php if (count($shelf_alerts) === 0): ?>
        <p class="text-muted" style="font-style:italic;font-size:0.85rem;">All shelves are well stocked.</p>
      <?php else: ?>
        <div class="alert-list">
          <?php foreach (array_slice($shelf_alerts, 0, 5) as $alert):
            $shelf       = (int)$alert['shelf_qty'];
            $badge_class = $shelf === 0 ? 'badge-danger' : 'badge-warning';
            $badge_text  = $shelf === 0 ? 'Empty Shelf' : $shelf . ' left on shelf';
          ?>
          <div class="alert-item">
            <div class="alert-info">
              <h4><?php echo htmlspecialchars($alert['product_name']); ?></h4>
              <p><?php echo htmlspecialchars($alert['category']); ?> &middot; Stock: <?php echo $alert['current_stock']; ?></p>
            </div>
            <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Recent Activity -->
<div class="card">
  <div class="card-header">
    <h3>Recent Activity</h3>
    <p>Latest inventory changes</p>
  </div>
  <div class="card-body">
    <?php if (count($recent) === 0): ?>
      <p class="text-muted" style="font-style:italic;font-size:0.85rem;">No activity yet.</p>
    <?php else: ?>
      <div class="activity-list">
        <?php foreach ($recent as $item):
          $stock = (int)$item['current_stock'];
          $min   = (int)$item['minimum_stock'];
          if ($stock === 0) {
            $type  = 'Alert';
            $badge = 'badge-danger';
          } elseif ($stock <= $min) {
            $type  = 'Low';
            $badge = 'badge-warning';
          } elseif ($item['created_at'] === $item['updated_at']) {
            $type  = 'New';
            $badge = 'badge-success';
          } else {
            $type  = 'Update';
            $badge = 'badge-info';
          }
        ?>
        <div class="activity-item">
          <div>
            <div class="activity-text">
              <?php echo htmlspecialchars($item['product_name']); ?>
              (<?php echo $stock; ?> units)
              <?php if ((float)$item['price'] <= 5000): ?>
                <span class="mini-tag">MINI</span>
              <?php endif; ?>
            </div>
            <div class="activity-time">
              <?php echo date('M j, Y \a\t g:i A', strtotime($item['updated_at'])); ?>
            </div>
          </div>
          <span class="badge <?php echo $badge; ?>"><?php echo $type; ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
