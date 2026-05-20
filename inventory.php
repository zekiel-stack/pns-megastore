<?php
/**
 * PNS Mega Store — Inventory Page
 *
 * Full inventory table with search, category filter,
 * stock adjustment, edit, delete, and shelf advisory.
 */

require_once 'includes/db.php';

$page_title   = 'Inventory';
$current_page = 'inventory';

// ── Get filters ──────────────────────────────────────────
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// ── Build query ──────────────────────────────────────────
$where_clauses = [];
$params        = [];
$types         = '';

if ($search !== '') {
    $where_clauses[] = "(product_name LIKE ? OR supplier LIKE ? OR storage_location LIKE ?)";
    $search_param    = '%' . $search . '%';
    $params[]        = $search_param;
    $params[]        = $search_param;
    $params[]        = $search_param;
    $types          .= 'sss';
}

if ($category !== '') {
    $where_clauses[] = "category = ?";
    $params[]        = $category;
    $types          .= 's';
}

$sql = "SELECT * FROM pns_inventory";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY updated_at DESC";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

// ── Category list for filter dropdown ────────────────────
$categories = [
    'Concession',
    'Mini',
    'In-house Reuse',
    'Vintages',
    'Reduced',
    'Original & Classic by PNS',
];

// ── Helper: get status ───────────────────────────────────
function getStatus($stock, $min) {
    if ($stock <= 0)      return ['label' => 'Out of Stock', 'class' => 'badge-danger'];
    if ($stock <= $min)   return ['label' => 'Low Stock',    'class' => 'badge-warning'];
    return                       ['label' => 'In Stock',     'class' => 'badge-success'];
}

// ── Helper: category badge class ─────────────────────────
function getCategoryClass($cat) {
    $map = [
        'Concession'              => 'cat-concession',
        'Mini'                    => 'cat-mini',
        'In-house Reuse'          => 'cat-reuse',
        'Vintages'                => 'cat-vintages',
        'Reduced'                 => 'cat-reduced',
        'Original & Classic by PNS' => 'cat-original',
    ];
    return $map[$cat] ?? '';
}

// ── Helper: shelf bar level ──────────────────────────────
function getShelfLevel($shelf_qty, $min_stock) {
    if ($min_stock <= 0) $min_stock = 1;
    $ratio = $shelf_qty / $min_stock;
    if ($ratio >= 1)   return 'high';
    if ($ratio >= 0.4) return 'medium';
    return 'low';
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;">
  <div>
    <h1>Inventory</h1>
    <p>Manage all products in PNS Mega Store</p>
  </div>
  <a href="add_item.php" class="btn btn-primary">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
    </svg>
    Add Item
  </a>
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
<?php if (isset($_GET['err'])): ?>
<div class="flash-message flash-error">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M12 9v3.75m9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374
         L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
  </svg>
  <?php echo htmlspecialchars($_GET['err']); ?>
</div>
<?php endif; ?>

<!-- Toolbar: Search + Category Filter -->
<form method="GET" action="inventory.php" class="toolbar">
  <div class="search-box">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
    </svg>
    <input type="text" name="search" placeholder="Search products, suppliers, locations..."
           value="<?php echo htmlspecialchars($search); ?>">
  </div>
  <div class="filter-select">
    <select name="category" class="form-control" onchange="this.form.submit()">
      <option value="">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($cat); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <button type="submit" class="btn btn-secondary">
    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591
           l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568
           a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096
           A48.32 48.32 0 0112 3z"/>
    </svg>
    Filter
  </button>
  <?php if ($search !== '' || $category !== ''): ?>
    <a href="inventory.php" class="btn btn-secondary">Clear</a>
  <?php endif; ?>
</form>

<!-- Inventory Table -->
<div class="card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Product Name</th>
          <th>Category</th>
          <th>Stock</th>
          <th>Shelf</th>
          <th>Min</th>
          <th>Price (&#8358;)</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($items) === 0): ?>
          <tr><td colspan="9" class="table-empty">No products found</td></tr>
        <?php else: ?>
          <?php foreach ($items as $item):
            $stock       = (int)$item['current_stock'];
            $shelf       = (int)$item['shelf_qty'];
            $min         = (int)$item['minimum_stock'];
            $price       = (float)$item['price'];
            $status      = getStatus($stock, $min);
            $cat_class   = getCategoryClass($item['category']);
            $shelf_level = getShelfLevel($shelf, $min);
            $is_mini     = ($price <= 5000);

            // Shelf fill percentage (capped at 100% for display)
            $shelf_pct = $min > 0 ? min(100, round(($shelf / $min) * 100)) : ($shelf > 0 ? 100 : 0);
          ?>
          <tr>
            <td style="color:var(--text-muted);font-weight:600;">#<?php echo $item['id']; ?></td>
            <td>
              <span style="font-weight:500;"><?php echo htmlspecialchars($item['product_name']); ?></span>
              <?php if ($is_mini): ?>
                <span class="mini-tag">MINI</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="cat-badge <?php echo $cat_class; ?>">
                <?php echo htmlspecialchars($item['category']); ?>
              </span>
            </td>
            <td>
              <div class="stock-adjuster">
                <button onclick="updateStock(<?php echo $item['id']; ?>, -1)" title="Decrease">−</button>
                <span><?php echo $stock; ?></span>
                <button onclick="updateStock(<?php echo $item['id']; ?>, 1)" title="Increase">+</button>
              </div>
            </td>
            <td>
              <div class="shelf-indicator">
                <span style="font-weight:500;min-width:20px;"><?php echo $shelf; ?></span>
                <div class="shelf-bar">
                  <div class="shelf-bar-fill <?php echo $shelf_level; ?>" style="width:<?php echo $shelf_pct; ?>%"></div>
                </div>
              </div>
            </td>
            <td><?php echo $min; ?></td>
            <td style="font-weight:500;">&#8358;<?php echo number_format($price, 2); ?></td>
            <td><span class="badge <?php echo $status['class']; ?>"><?php echo $status['label']; ?></span></td>
            <td>
              <div class="action-btns">
                <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn-icon edit" title="Edit">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07
                         a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z
                         m0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25
                         A2.25 2.25 0 015.25 6H10"/>
                  </svg>
                </a>
                <button onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo addslashes($item['product_name']); ?>')"
                        class="btn-icon delete" title="Delete">
                  <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166
                         m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077
                         L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165
                         m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201
                         a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Summary -->
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
  Showing <?php echo count($items); ?> item<?php echo count($items) !== 1 ? 's' : ''; ?>
  <?php if ($category !== ''): ?> in <strong><?php echo htmlspecialchars($category); ?></strong><?php endif; ?>
  <?php if ($search !== ''): ?> matching &ldquo;<?php echo htmlspecialchars($search); ?>&rdquo;<?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
