<?php
// user/views/order.php - Menu browsing with canteen filter
require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
// Public page

include '../includes/header.php';
include '../includes/navbar.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$canteenFilter = isset($_GET['canteen']) ? intval($_GET['canteen']) : 0;

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }

    if (!$isLoggedIn) {
        $_SESSION['return_url'] = '/CFOMS/user/views/order.php' . ($canteenFilter ? "?canteen=$canteenFilter" : '');
        header('Location: ../../auth/login.php');
        exit();
    }

    $item_id = intval($_POST['item_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $item_canteen_id = intval($_POST['canteen_id'] ?? 0);

    if ($item_id > 0 && $quantity > 0) {
        // Store as canteen_id:item_id => quantity for multi-canteen cart
        $cartKey = $item_canteen_id . ':' . $item_id;
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = $quantity;
        }
        $message = 'Item added to cart!';
    }
}

// Handle Remove
if (isset($_GET['remove']) && $isLoggedIn) {
    $removeKey = sanitize($_GET['remove']);
    if (isset($_SESSION['cart'][$removeKey])) {
        unset($_SESSION['cart'][$removeKey]);
        $message = 'Item removed from cart.';
    }
}

// Fetch canteens for filter dropdown
$canteens = $mysqli->query("SELECT id, name FROM canteens WHERE is_active = 1 ORDER BY name ASC");

// Build menu query
$whereClause = "m.is_available = 1 AND c.is_active = 1";
if ($canteenFilter > 0) {
    $whereClause .= " AND m.canteen_id = $canteenFilter";
}

$items = $mysqli->query("
    SELECT m.*, c.name as canteen_name, c.id as canteen_id 
    FROM menu m 
    JOIN canteens c ON m.canteen_id = c.id 
    WHERE $whereClause 
    ORDER BY c.name, m.name ASC
");

// Get selected canteen name
$selectedCanteenName = '';
if ($canteenFilter > 0) {
    $sc = $mysqli->query("SELECT name FROM canteens WHERE id = $canteenFilter")->fetch_assoc();
    $selectedCanteenName = $sc['name'] ?? '';
}

// Cart count
$cartCount = array_sum($_SESSION['cart']);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="section-title mb-0">
            <i class="fa-solid fa-utensils me-2 text-gold"></i>
            <?= $selectedCanteenName ? htmlspecialchars($selectedCanteenName) : 'All Menu Items' ?>
        </h2>
        <div class="d-flex gap-2 align-items-center">
            <!-- Canteen Filter -->
            <select class="form-select form-control-cafe" onchange="window.location.href='order.php?canteen='+this.value" style="min-width: 180px;">
                <option value="">All Canteens</option>
                <?php while ($c = $canteens->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= $canteenFilter == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
            
            <?php if ($isLoggedIn && $cartCount > 0): ?>
            <a href="../checkout.php" class="btn btn-cafe position-relative">
                <i class="fa-solid fa-shopping-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $cartCount ?>
                </span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$isLoggedIn): ?>
    <div class="alert alert-info">
        <i class="fa-solid fa-info-circle me-2"></i>
        <a href="../../auth/login.php" class="alert-link">Login</a> or 
        <a href="../../auth/register.php" class="alert-link">Register</a> to add items to your cart.
    </div>
    <?php endif; ?>

    <?php if ($items->num_rows === 0): ?>
    <div class="premium-card p-5 text-center">
        <i class="fa-solid fa-utensils fa-3x text-muted mb-3"></i>
        <p class="text-muted">No menu items available.</p>
        <a href="../canteens.php" class="btn btn-cafe">Browse Canteens</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php 
        $currentCanteen = '';
        while ($item = $items->fetch_assoc()): 
            // Show canteen header when listing all
            if (!$canteenFilter && $currentCanteen !== $item['canteen_name']):
                $currentCanteen = $item['canteen_name'];
        ?>
        <div class="col-12">
            <h4 class="text-gold mt-3"><i class="fa-solid fa-store me-2"></i><?= htmlspecialchars($currentCanteen) ?></h4>
            <hr class="border-secondary">
        </div>
        <?php endif; ?>
        
        <div class="col-md-6 col-lg-3">
            <div class="menu-card premium-card h-100">
                <a href="product.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                    <?php if ($item['image']): ?>
                        <img src="../../uploads/menu/<?= htmlspecialchars($item['image']) ?>" 
                             class="card-img-top" style="height: 140px; object-fit: cover;" 
                             alt="<?= htmlspecialchars($item['name']) ?>" />
                    <?php else: ?>
                        <div class="bg-dark-card d-flex align-items-center justify-content-center" style="height: 140px;">
                            <i class="fa-solid fa-burger fa-3x text-gold"></i>
                        </div>
                    <?php endif; ?>
                </a>
                <div class="card-body d-flex flex-column">
                    <a href="product.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                        <h6 class="card-title text-light mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                    </a>
                    <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</p>
                    <p class="price-tag mb-2">PKR <?= number_format($item['price'], 0) ?></p>
                    
                    <?php if ($isLoggedIn): ?>
                    <form method="POST" class="d-flex gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="canteen_id" value="<?= $item['canteen_id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" max="99" class="form-control form-control-sm form-control-cafe" style="width: 60px;">
                        <button type="submit" name="add_to_cart" class="btn btn-cafe btn-sm flex-grow-1">
                            <i class="fa-solid fa-cart-plus"></i>
                        </button>
                    </form>
                    <?php else: ?>
                    <a href="../../auth/login.php" class="btn btn-cafe-outline btn-sm">Login to Order</a>
                    <?php endif; ?>
                </div>
                
                <?php 
                $cartKey = $item['canteen_id'] . ':' . $item['id'];
                $inCart = $_SESSION['cart'][$cartKey] ?? 0;
                if ($inCart > 0): 
                ?>
                <div class="card-footer bg-success text-white d-flex justify-content-between align-items-center py-2">
                    <small><i class="fa-solid fa-check me-1"></i>In Cart: <?= $inCart ?></small>
                    <a href="?remove=<?= $cartKey ?><?= $canteenFilter ? "&canteen=$canteenFilter" : '' ?>" class="btn btn-sm btn-outline-light py-0">×</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($isLoggedIn && $cartCount > 0): ?>
    <div class="text-center mt-5">
        <a href="../checkout.php" class="btn btn-cafe btn-lg px-5 shadow">
            <i class="fa-solid fa-check-circle me-2"></i>Proceed to Checkout (<?= $cartCount ?> items)
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
