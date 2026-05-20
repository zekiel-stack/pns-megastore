<?php
/**
 * PNS Mega Store — Edit Item
 *
 * Pre-filled form to edit an existing inventory item.
 */

require_once 'includes/db.php';

$page_title   = 'Edit Item';
$current_page = 'inventory';

$categories = [
    'Concession',
    'Mini',
    'In-house Reuse',
    'Vintages',
    'Reduced',
    'Original & Classic by PNS',
];

$error = '';
$item  = null;

// ── Get item ID ──────────────────────────────────────────
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: inventory.php?err=Invalid item ID');
    exit;
}

// ── Handle form submission ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id               = (int)($_POST['id']             ?? 0);
    $product_name     = trim($_POST['product_name']    ?? '');
    $category         = trim($_POST['category']        ?? '');
    $current_stock    = (int)($_POST['current_stock']  ?? 0);
    $shelf_qty        = (int)($_POST['shelf_qty']       ?? 0);
    $minimum_stock    = (int)($_POST['minimum_stock']  ?? 0);
    $price            = (float)($_POST['price']         ?? 0);
    $supplier         = trim($_POST['supplier']         ?? '');
    $storage_location = trim($_POST['storage_location'] ?? '');

    if ($product_name === '' || $category === '') {
        $error = 'Product name and category are required.';
    } elseif (!in_array($category, $categories)) {
        $error = 'Invalid category selected.';
    } elseif ($current_stock < 0 || $shelf_qty < 0 || $minimum_stock < 0 || $price < 0) {
        $error = 'Numeric values cannot be negative.';
    } elseif ($shelf_qty > $current_stock) {
        $error = 'Shelf quantity cannot exceed current stock.';
    } else {
        $stmt = $conn->prepare(
            "UPDATE pns_inventory SET
               product_name=?, category=?, current_stock=?, shelf_qty=?, minimum_stock=?,
               price=?, supplier=?, storage_location=?
             WHERE id=?"
        );
        $stmt->bind_param(
            'ssiiidssi',
            $product_name, $category, $current_stock, $shelf_qty, $minimum_stock, $price,
            $supplier, $storage_location, $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: inventory.php?msg=Item updated successfully');
            exit;
        } else {
            $error = 'Database error: ' . $stmt->error;
            $stmt->close();
        }
    }
}

// ── Fetch item for pre-fill ──────────────────────────────
$stmt = $conn->prepare("SELECT * FROM pns_inventory WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$item   = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    header('Location: inventory.php?err=Item not found');
    exit;
}

// Use POST data if form was submitted with errors, otherwise use DB data
$form = [
    'product_name'     => $_POST['product_name']     ?? $item['product_name'],
    'category'         => $_POST['category']         ?? $item['category'],
    'current_stock'    => $_POST['current_stock']    ?? $item['current_stock'],
    'shelf_qty'        => $_POST['shelf_qty']         ?? $item['shelf_qty'],
    'minimum_stock'    => $_POST['minimum_stock']    ?? $item['minimum_stock'],
    'price'            => $_POST['price']             ?? $item['price'],
    'supplier'         => $_POST['supplier']          ?? $item['supplier'],
    'storage_location' => $_POST['storage_location'] ?? $item['storage_location'],
];

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <h1>Edit Item</h1>
  <p>Update product #<?php echo $id; ?> — <?php echo htmlspecialchars($item['product_name']); ?></p>
</div>

<?php if ($error): ?>
<div class="flash-message flash-error">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M12 9v3.75m9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374
         L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
  </svg>
  <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Edit Form -->
<div class="card" style="max-width:720px;">
  <div class="card-header">
    <h3>Product Details</h3>
    <p>Modify the fields below and save changes</p>
  </div>
  <div class="card-body">
    <form method="POST" action="edit_item.php?id=<?php echo $id; ?>">
      <input type="hidden" name="id" value="<?php echo $id; ?>">

      <div class="form-row">
        <div class="form-group">
          <label for="product_name">Product Name</label>
          <input type="text" id="product_name" name="product_name" class="form-control" required
                 value="<?php echo htmlspecialchars($form['product_name']); ?>">
        </div>
        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category" class="form-control" required>
            <option value="" disabled>Select category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat); ?>"
                <?php echo $form['category'] === $cat ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="current_stock">Current Stock</label>
          <input type="number" id="current_stock" name="current_stock" class="form-control" min="0" required
                 value="<?php echo htmlspecialchars($form['current_stock']); ?>">
        </div>
        <div class="form-group">
          <label for="shelf_qty">Shelf Quantity</label>
          <input type="number" id="shelf_qty" name="shelf_qty" class="form-control" min="0" required
                 value="<?php echo htmlspecialchars($form['shelf_qty']); ?>">
          <div class="form-hint">Items currently on the shelf</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="minimum_stock">Minimum Stock</label>
          <input type="number" id="minimum_stock" name="minimum_stock" class="form-control" min="0" required
                 value="<?php echo htmlspecialchars($form['minimum_stock']); ?>">
        </div>
        <div class="form-group">
          <label for="price">Price (&#8358;)</label>
          <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required
                 value="<?php echo htmlspecialchars($form['price']); ?>">
          <div class="form-hint">Items ≤ &#8358;5,000 are auto-tagged as Mini</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="supplier">Supplier</label>
          <input type="text" id="supplier" name="supplier" class="form-control"
                 value="<?php echo htmlspecialchars($form['supplier']); ?>">
        </div>
        <div class="form-group">
          <label for="storage_location">Storage Location</label>
          <input type="text" id="storage_location" name="storage_location" class="form-control"
                 value="<?php echo htmlspecialchars($form['storage_location']); ?>">
        </div>
      </div>

      <div class="form-actions">
        <a href="inventory.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07
                 a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
          </svg>
          Save Changes
        </button>
      </div>

    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
