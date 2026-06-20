<?php
// user/views/product.php - Product Detail Page
require_once '../../includes/session.php';
require_once '../../config/db_connect.php';

// Check if user is logged in for cart actions
$isLoggedIn = isset($_SESSION['user_id']);
$userData = null;
if ($isLoggedIn) {
    $stmt = $mysqli->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
// Restrict cart actions for non-users (e.g. canteen owners)
$canOrder = !$isLoggedIn || ($userData && $userData['role'] === 'user');

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Fetch product details with canteen info and average rating
$stmt = $mysqli->prepare("
    SELECT m.*, c.name as canteen_name, c.logo as canteen_logo,
           (SELECT AVG(rating) FROM reviews WHERE order_id IN (SELECT id FROM orders WHERE items LIKE CONCAT('%\"id\":', m.id, ',%'))) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE order_id IN (SELECT id FROM orders WHERE items LIKE CONCAT('%\"id\":', m.id, ',%'))) as review_count
    FROM menu m 
    JOIN canteens c ON m.canteen_id = c.id 
    WHERE m.id = ? AND m.is_available = 1 AND c.is_active = 1
");
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: ../order.php");
    exit();
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canOrder) {
    if (!$isLoggedIn) {
        header("Location: ../../auth/login.php");
        exit();
    }
    
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    
    $qty = intval($_POST['quantity']);
    if ($qty > 0) {
        $cartKey = $product['canteen_id'] . ':' . $product['id'];
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey] += $qty;
        } else {
            $_SESSION['cart'][$cartKey] = $qty;
        }
        $success = "Added to cart!";
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row align-items-center">
        <!-- Product Image -->
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="premium-card p-2 text-center" style="height: 400px; display: flex; align-items: center; justify-content: center; background: var(--color-dark-card);">
                <?php if ($product['image']): ?>
                    <img src="../../uploads/menu/<?= htmlspecialchars($product['image']) ?>" 
                         class="img-fluid rounded" 
                         style="max-height: 100%; object-fit: contain;" />
                <?php else: ?>
                    <i class="fa-solid fa-burger fa-8x text-gold"></i>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php" class="text-muted">Home</a></li>
                    <li class="breadcrumb-item"><a href="order.php" class="text-muted">Menu</a></li>
                    <li class="breadcrumb-item active text-gold" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
                </ol>
            </nav>
            
            <h1 class="display-4 fw-bold text-light mb-2"><?= htmlspecialchars($product['name']) ?></h1>
            <div class="d-flex align-items-center mb-4">
                <span class="text-gold lead fw-bold me-3">PKR <?= number_format($product['price'], 0) ?></span>
                <?php if ($product['avg_rating']): ?>
                    <span class="text-gold me-2"><i class="fa-solid fa-star me-1"></i><?= number_format($product['avg_rating'], 1) ?></span>
                    <span class="text-muted small">(<?= $product['review_count'] ?> reviews)</span>
                <?php else: ?>
                    <span class="text-muted small">No ratings yet</span>
                <?php endif; ?>
            </div>

            <p class="lead text-muted mb-4"><?= htmlspecialchars($product['description'] ?? 'No description available.') ?></p>
            
            <div class="d-flex align-items-center mb-5">
                <div class="d-flex align-items-center me-3">
                    <?php if ($product['canteen_logo']): ?>
                        <img src="../../uploads/canteens/<?= htmlspecialchars($product['canteen_logo']) ?>" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-dark-lighter d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-store text-gold"></i>
                        </div>
                    <?php endif; ?>
                    <span class="text-light">Sold by <strong><?= htmlspecialchars($product['canteen_name']) ?></strong></span>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success d-inline-block py-2 px-4 mb-4">
                    <i class="fa-solid fa-check-circle me-2"></i><?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="d-flex gap-3" style="max-width: 400px;">
                <?= csrf_field() ?>
                <input type="number" name="quantity" class="form-control form-control-cafe text-center" value="1" min="1" max="10" style="width: 80px;">
                <?php if ($canOrder): ?>
                    <button type="submit" class="btn btn-cafe flex-grow-1"><i class="fa-solid fa-cart-plus me-2"></i>Add to Cart</button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary flex-grow-1" disabled>Seller Account</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
