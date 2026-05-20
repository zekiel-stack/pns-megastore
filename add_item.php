<?php
/**
 * PNS Mega Store — Add New Item
 *
 * Form to add a new product to the inventory.
 */

require_once 'includes/db.php';

$page_title   = 'Add Item';
$current_page = 'add_item';

$categories = [
    'Concession',
    'Mini',
    'In-house Reuse',
    'Vintages',
    'Reduced',
    'Original & Classic by PNS',
];

$error   = '';
$success = '';

// ── Handle form submission ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name     = trim($_POST['product_name']     ?? '');
    $category         = trim($_POST['category']         ?? '');
    $current_stock    = (int)($_POST['current_stock']   ?? 0);
    $shelf_qty        = (int)($_POST['shelf_qty']        ?? 0);
    $minimum_stock    = (int)($_POST['minimum_stock']   ?? 0);
    $price            = (float)($_POST['price']          ?? 0);
    $supplier         = trim($_POST['supplier']          ?? '');
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
            "INSERT INTO pns_inventory
             (product_name, category, current_stock, shelf_qty, minimum_stock, price, supplier, storage_location)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'ssiiidss',
            $product_name, $category, $current_stock, $shelf_qty, $minimum_stock, $price,
            $supplier, $storage_location
        );

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: inventory.php?msg=Item added successfully');
            exit;
        } else {
            $error = 'Database error: ' . $stmt->error;
            $stmt->close();
        }
    }
}

// Keep POST values on error
$form = [
    'product_name'     => $_POST['product_name']     ?? '',
    'category'         => $_POST['category']         ?? '',
    'current_stock'    => $_POST['current_stock']    ?? 0,
    'shelf_qty'        => $_POST['shelf_qty']         ?? 0,
    'minimum_stock'    => $_POST['minimum_stock']    ?? 0,
    'price'            => $_POST['price']             ?? '',
    'supplier'         => $_POST['supplier']          ?? '',
    'storage_location' => $_POST['storage_location'] ?? '',
];

require_once 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <h1>Add New Item</h1>
  <p>Add a new product to the PNS Mega Store inventory</p>
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

<!-- Add Form -->
<div class="card" style="max-width:720px;">
  <div class="card-header">
    <h3>Product Details</h3>
    <p>Fill in the fields below to add a new product</p>
  </div>
  <div class="card-body">
    <form method="POST" action="add_item.php">

      <div class="form-row">
        <div class="form-group">
          <label for="product_name">Product Name <span style="color:var(--red)">*</span></label>
          <input type="text" id="product_name" name="product_name" class="form-control" required
                 placeholder="e.g. Bottled Water 75cl"
                 value="<?php echo htmlspecialchars($form['product_name']); ?>">
        </div>
        <div class="form-group">
          <label for="category">Category <span style="color:var(--red)">*</span></label>
          <select id="category" name="category" class="form-control" required>
            <option value="" disabled <?php echo $form['category'] === '' ? 'selected' : ''; ?>>Select category</option>
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
                 placeholder="0.00"
                 value="<?php echo htmlspecialchars($form['price']); ?>">
          <div class="form-hint">Items ≤ &#8358;5,000 are auto-tagged as Mini</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="supplier">Supplier</label>
          <input type="text" id="supplier" name="supplier" class="form-control"
                 placeholder="e.g. AquaPure Ltd"
                 value="<?php echo htmlspecialchars($form['supplier']); ?>">
        </div>
        <div class="form-group">
          <label for="storage_location">Storage Location</label>
          <input type="text" id="storage_location" name="storage_location" class="form-control"
                 placeholder="e.g. Aisle A - Shelf 1"
                 value="<?php echo htmlspecialchars($form['storage_location']); ?>">
        </div>
      </div>

      <div class="form-actions">
        <a href="inventory.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
          </svg>
          Add Item
        </button>
      </div>

    </form>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
