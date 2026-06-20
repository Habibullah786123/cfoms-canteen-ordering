<?php
require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_login('user');

$user_id = $_SESSION['user_id'];

// Fetch order history
$stmt = $mysqli->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5" style="max-width: 900px;">
    <h2 class="mb-4 text-center">Your Order History</h2>

    <?php if (isset($_GET['order_success'])): ?>
        <div class="alert alert-success text-center shadow-sm">
            <i class="fa fa-check-circle me-2"></i>Order placed successfully!
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <div class="alert alert-info text-center shadow-sm">No orders found.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php while ($order = $result->fetch_assoc()):
                $items = json_decode($order['items'], true);
            ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Order #<?= htmlspecialchars($order['id']) ?></h5>
                                <span class="badge 
                                    <?php
                                        switch (strtolower($order['status'])) {
                                            case 'pending': echo 'bg-warning text-dark'; break;
                                            case 'completed': echo 'bg-success'; break;
                                            case 'cancelled': echo 'bg-danger'; break;
                                            default: echo 'bg-secondary';
                                        }
                                    ?>
                                ">
                                    <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                </span>
                            </div>
                            <p class="card-text flex-grow-1">
                                <strong>Items:</strong><br>
                                <?php
                                $item_descriptions = array_map(function($i) {
                                    return htmlspecialchars($i['name']) . ' × ' . intval($i['quantity']);
                                }, $items);
                                echo implode(', ', $item_descriptions);
                                ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">Ordered At: <?= htmlspecialchars($order['created_at']) ?></small>
                                <div class="text-end">
                                    <span class="fw-bold fs-5 d-block">PKR <?= number_format($order['total_price'], 0) ?></span>
                                    <?php if ($order['status'] === 'Ready' || $order['status'] === 'Delivered' || $order['status'] === 'Completed'): ?>
                                        <?php
                                            // Check if already reviewed (optimized: better to join in main query but this works for now)
                                            $checkReview = $mysqli->query("SELECT id FROM reviews WHERE order_id = " . $order['id']);
                                            $hasReviewed = $checkReview->num_rows > 0;
                                        ?>
                                        <?php if (!$hasReviewed): ?>
                                            <a href="add_review.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-cafe mt-2">
                                                <i class="fa-regular fa-star me-1"></i>Rate Order
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-success mt-2"><i class="fa-solid fa-check me-1"></i>Reviewed</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
