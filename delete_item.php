<?php
/**
 * PNS Mega Store — Delete Item Handler
 *
 * Deletes an item by ID and redirects back to inventory.
 * Called via GET: delete_item.php?id=X
 */

require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: inventory.php?err=Invalid item ID');
    exit;
}

$stmt = $conn->prepare("DELETE FROM pns_inventory WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: inventory.php?msg=Item deleted successfully');
} else {
    $stmt->close();
    header('Location: inventory.php?err=Failed to delete item');
}
exit;
?>
