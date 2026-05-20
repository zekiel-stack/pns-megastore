<?php
/**
 * PNS Mega Store — CSV Export
 *
 * Downloads all inventory items as a CSV file.
 */

require_once 'includes/db.php';

$result = $conn->query("SELECT * FROM pns_inventory ORDER BY id ASC");

if (!$result || $result->num_rows === 0) {
    header('Location: index.php?msg=No data to export');
    exit;
}

// Set headers for CSV download
$filename = 'pns_inventory_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Write headers
fputcsv($output, [
    'ID',
    'Product Name',
    'Category',
    'Current Stock',
    'Shelf Quantity',
    'Minimum Stock',
    'Price',
    'Supplier',
    'Storage Location',
    'Status',
    'Mini Item',
    'Created At',
    'Updated At',
]);

// Write data rows
while ($row = $result->fetch_assoc()) {
    $stock = (int)$row['current_stock'];
    $min   = (int)$row['minimum_stock'];
    $price = (float)$row['price'];

    // Derive status
    if ($stock === 0)      $status = 'Out of Stock';
    elseif ($stock <= $min) $status = 'Low Stock';
    else                    $status = 'In Stock';

    // Mini flag
    $is_mini = ($price <= 5000) ? 'Yes' : 'No';

    fputcsv($output, [
        $row['id'],
        $row['product_name'],
        $row['category'],
        $row['current_stock'],
        $row['shelf_qty'],
        $row['minimum_stock'],
        $row['price'],
        $row['supplier'],
        $row['storage_location'],
        $status,
        $is_mini,
        $row['created_at'],
        $row['updated_at'],
    ]);
}

fclose($output);
exit;
?>
