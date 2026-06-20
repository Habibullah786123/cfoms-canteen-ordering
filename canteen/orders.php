<?php
// canteen/orders.php - Manage Canteen Orders
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('canteen_owner');

$canteenId = $_SESSION['canteen_id'];
$message = '';

// Handle status update
if (isset($_POST['update_status'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    $orderId = intval($_POST['order_id']);
    $newStatus = sanitize($_POST['status']);
    $validStatuses = ['Pending', 'Preparing', 'Ready', 'Completed', 'Cancelled'];
    
    if (in_array($newStatus, $validStatuses)) {
        $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE id = ? AND canteen_id = ?");
        $stmt->bind_param('sii', $newStatus, $orderId, $canteenId);
        $stmt->execute();
        $message = 'Order status updated.';
        $stmt->close();
    }
}

// Filter
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$whereClause = "canteen_id = $canteenId";
if ($statusFilter) {
    $whereClause .= " AND status = '" . $mysqli->real_escape_string($statusFilter) . "'";
}

$orders = $mysqli->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE $whereClause ORDER BY o.created_at DESC");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-gold mb-0"><i class="fa-solid fa-receipt me-2"></i>Orders</h2>
        <!-- Filters -->
        <div class="btn-group">
            <a href="orders.php" class="btn btn-sm <?= !$statusFilter ? 'btn-cafe' : 'btn-cafe-outline' ?>">All</a>
            <a href="?status=Pending" class="btn btn-sm <?= $statusFilter === 'Pending' ? 'btn-cafe' : 'btn-cafe-outline' ?>">Pending</a>
            <a href="?status=Preparing" class="btn btn-sm <?= $statusFilter === 'Preparing' ? 'btn-cafe' : 'btn-cafe-outline' ?>">Preparing</a>
            <a href="?status=Ready" class="btn btn-sm <?= $statusFilter === 'Ready' ? 'btn-cafe' : 'btn-cafe-outline' ?>">Ready</a>
            <a href="?status=Completed" class="btn btn-sm <?= $statusFilter === 'Completed' ? 'btn-cafe' : 'btn-cafe-outline' ?>">Completed</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($orders->num_rows === 0): ?>
    <div class="premium-card p-5 text-center">
        <i class="fa-solid fa-receipt fa-3x text-muted mb-3"></i>
        <p class="text-muted">No orders found.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-dark table-hover">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): 
                    $items = json_decode($order['items'], true);
                ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td>
                        <?php if (is_array($items)): ?>
                            <?php foreach (array_slice($items, 0, 2) as $item): ?>
                                <span class="badge bg-secondary me-1"><?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?></span>
                            <?php endforeach; ?>
                            <?php if (count($items) > 2): ?>
                                <span class="text-muted">+<?= count($items) - 2 ?> more</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-gold fw-bold">PKR <?= number_format($order['total_price'], 0) ?></td>
                    <td>
                        <span class="badge bg-<?= 
                            $order['status'] === 'Pending' ? 'warning text-dark' : 
                            ($order['status'] === 'Preparing' ? 'info' : 
                            ($order['status'] === 'Ready' ? 'primary' : 
                            ($order['status'] === 'Completed' ? 'success' : 'danger'))) 
                        ?>">
                            <?= $order['status'] ?>
                        </span>
                    </td>
                    <td class="text-muted"><?= date('M d, H:i', strtotime($order['created_at'])) ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>" />
                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                <option value="">Update...</option>
                                <option value="Pending">Pending</option>
                                <option value="Preparing">Preparing</option>
                                <option value="Ready">Ready</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <input type="hidden" name="update_status" value="1" />
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
// Simple auto-refresh to check for new orders every 15 seconds
// In production, we would use AJAX to check first to avoid reloading if not needed.
setTimeout(function() {
    location.reload();
}, 15000);
</script>

<?php include 'includes/footer.php'; ?>
