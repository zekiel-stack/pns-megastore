<?php
/**
 * PNS Mega Store — Stock Update Handler (AJAX)
 *
 * Accepts POST JSON: { id: int, change: int }
 * Updates current_stock by the change amount (+ or -).
 * Returns JSON response.
 */

require_once 'includes/db.php';

header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

$id     = isset($input['id'])     ? (int)$input['id']     : 0;
$change = isset($input['change']) ? (int)$input['change'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
    exit;
}

if ($change === 0) {
    echo json_encode(['success' => false, 'error' => 'No change specified']);
    exit;
}

// Get current stock
$stmt = $conn->prepare("SELECT current_stock FROM pns_inventory WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'error' => 'Item not found']);
    exit;
}

$new_stock = max(0, (int)$row['current_stock'] + $change);

// Update stock
$stmt = $conn->prepare("UPDATE pns_inventory SET current_stock = ? WHERE id = ?");
$stmt->bind_param('ii', $new_stock, $id);

if ($stmt->execute()) {
    $stmt->close();

    // Determine new status
    $stmt2 = $conn->prepare("SELECT minimum_stock FROM pns_inventory WHERE id = ?");
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $r2 = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    $min = (int)($r2['minimum_stock'] ?? 0);

    if ($new_stock === 0) {
        $status       = 'Out of Stock';
        $status_class = 'badge-danger';
    } elseif ($new_stock <= $min) {
        $status       = 'Low Stock';
        $status_class = 'badge-warning';
    } else {
        $status       = 'In Stock';
        $status_class = 'badge-success';
    }

    echo json_encode([
        'success'      => true,
        'new_stock'    => $new_stock,
        'status'       => $status,
        'status_class' => $status_class,
    ]);
} else {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'Database update failed']);
}
exit;
?>
