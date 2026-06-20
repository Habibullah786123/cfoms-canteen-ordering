<?php
require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_login('user');

include '../includes/header.php';
include '../includes/navbar.php';

$order_status = null;
$order_items = null;
$total_price = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);

    if ($order_id <= 0) {
        $error = "Please enter a valid Order ID.";
    } else {
        $user_id = $_SESSION['user_id'];
        $stmt = $mysqli->prepare("SELECT status, items, total_price FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $order_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($status, $items_json, $total_price_db);
        if ($stmt->fetch()) {
            $order_status = $status;
            $total_price = $total_price_db;
            $order_items = json_decode($items_json, true);
        } else {
            $error = "Order not found or you do not have permission to view it.";
        }
        $stmt->close();
    }
}
?>

<div class="container mt-5" style="max-width: 600px;">
    <h2 class="mb-4 text-center">Track Your Order</h2>

    <form method="POST" class="mb-4">
        <div class="input-group shadow-sm rounded">
            <input type="number" name="order_id" class="form-control form-control-lg" placeholder="Enter your Order ID" required />
            <button class="btn btn-primary btn-lg" type="submit">
                <i class="fa fa-search me-2"></i> Track
            </button>
        </div>
    </form>

    <?php if ($order_status !== null): ?>
        <div class="card border-primary shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3 text-center">Order Status</h5>
                <p id="statusText" class="display-6 text-primary fw-bold text-center mb-4"><?= htmlspecialchars($order_status) ?></p>

                <h6>Order Details:</h6>
                <ul class="list-group mb-3">
                    <?php if ($order_items && is_array($order_items)): ?>
                        <?php foreach ($order_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <br>
                                    Quantity: <?= intval($item['quantity']) ?>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    PKR<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted">No items found in this order.</li>
                    <?php endif; ?>
                </ul>

                <h5 class="text-end">Total: <span class="text-success fw-bold">PKR<?= number_format($total_price, 2) ?></span></h5>
            </div>
        </div>
    <?php elseif ($error !== null): ?>
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="fa fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderId = <?= isset($order_id) ? $order_id : 'null' ?>;
    const statusDisplay = document.getElementById('statusText');
    
    if (orderId && statusDisplay) {
        let currentStatus = statusDisplay.innerText;

        setInterval(() => {
            fetch(`../api/order_status.php?id=${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.status !== currentStatus) {
                        // Play sound
                        const audio = new Audio('../../assets/sounds/notification.mp3'); 
                        audio.play().catch(e => console.log('Audio play failed', e));

                        // Update Status
                        currentStatus = data.status;
                        statusDisplay.innerText = currentStatus;
                        statusDisplay.classList.add('animate__animated', 'animate__pulse');
                        
                        // Update color (simplified logic for now)
                        if (currentStatus === 'Ready' || currentStatus === 'Completed') {
                            statusDisplay.className = 'display-6 text-success fw-bold text-center mb-4';
                        }
                    }
                })
                .catch(err => console.error('Polling error', err));
        }, 5000); // Poll every 5 seconds
    }
});
</script>

<?php include '../includes/footer.php'; ?>
