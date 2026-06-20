<?php
// user/checkout.php - Multi-canteen checkout
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('user');

include 'includes/header.php';
include 'includes/navbar.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$orderPlaced = false;
$orderIds = [];

// Fetch user details
$stmt = $mysqli->prepare("SELECT username, phone, address FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$phone = $user['phone'] ?? '';
$address = $user['address'] ?? '';

// Get cart items (format: canteen_id:item_id => quantity)
$cart = $_SESSION['cart'] ?? [];

// Handle POST: Place Orders
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }

    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (empty($phone) || empty($address)) {
        $errors[] = "Phone and address are required for delivery.";
    }

    if (empty($cart)) {
        $errors[] = "Your cart is empty.";
    }

    // Group cart by canteen
    $ordersByCanteen = [];
    foreach ($cart as $key => $quantity) {
        list($canteenId, $itemId) = explode(':', $key);
        $canteenId = intval($canteenId);
        $itemId = intval($itemId);
        $quantity = intval($quantity);
        
        if ($quantity <= 0) continue;

        $stmt = $mysqli->prepare("SELECT name, price, is_available FROM menu WHERE id = ? AND canteen_id = ?");
        $stmt->bind_param('ii', $itemId, $canteenId);
        $stmt->execute();
        $menuItem = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$menuItem) {
            $errors[] = "Menu item not found.";
            continue;
        }
        if (!$menuItem['is_available']) {
            $errors[] = "{$menuItem['name']} is currently unavailable.";
            continue;
        }

        if (!isset($ordersByCanteen[$canteenId])) {
            $ordersByCanteen[$canteenId] = ['items' => [], 'total' => 0];
        }
        $ordersByCanteen[$canteenId]['items'][] = [
            'id' => $itemId,
            'name' => $menuItem['name'],
            'quantity' => $quantity,
            'price' => floatval($menuItem['price'])
        ];
        $ordersByCanteen[$canteenId]['total'] += floatval($menuItem['price']) * $quantity;
    }

    if (empty($ordersByCanteen) && empty($errors)) {
        $errors[] = "No valid items in your order.";
    }

    if (empty($errors)) {
        // Start Transaction
        $mysqli->begin_transaction();

        try {
            // Update user profile
            $updateUser = $mysqli->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
            $updateUser->bind_param('ssi', $phone, $address, $user_id);
            $updateUser->execute();
            $updateUser->close();

            // Create one order per canteen
            foreach ($ordersByCanteen as $canteenId => $orderData) {
                $itemsJson = json_encode($orderData['items']);
                $totalPrice = $orderData['total'];
                
                $stmt = $mysqli->prepare("INSERT INTO orders (user_id, canteen_id, items, total_price, status, created_at) VALUES (?, ?, ?, ?, 'Pending', NOW())");
                $stmt->bind_param('iisd', $user_id, $canteenId, $itemsJson, $totalPrice);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to insert order for canteen $canteenId");
                }
                $orderIds[] = $stmt->insert_id;
                $stmt->close();
            }

            if (!empty($orderIds)) {
                $mysqli->commit();
                $orderPlaced = true;
                $_SESSION['cart'] = [];
            } else {
                throw new Exception("No orders created.");
            }

        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = "Transaction failed: " . $e->getMessage();
            $orderIds = [];
        }
    }
}

// Calculate cart for display (grouped by canteen)
$cartByCanteen = [];
$grandTotal = 0;
foreach ($cart as $key => $quantity) {
    list($canteenId, $itemId) = explode(':', $key);
    $canteenId = intval($canteenId);
    $itemId = intval($itemId);
    
    $stmt = $mysqli->prepare("SELECT m.name, m.price, c.name as canteen_name FROM menu m JOIN canteens c ON m.canteen_id = c.id WHERE m.id = ?");
    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($item) {
        $subtotal = $item['price'] * $quantity;
        if (!isset($cartByCanteen[$canteenId])) {
            $cartByCanteen[$canteenId] = ['name' => $item['canteen_name'], 'items' => [], 'subtotal' => 0];
        }
        $cartByCanteen[$canteenId]['items'][] = [
            'name' => $item['name'],
            'quantity' => $quantity,
            'price' => $item['price'],
            'subtotal' => $subtotal
        ];
        $cartByCanteen[$canteenId]['subtotal'] += $subtotal;
        $grandTotal += $subtotal;
    }
}
?>

<div class="container py-4" style="max-width: 750px;">
    <?php if ($orderPlaced): ?>
        <div class="text-center">
            <i class="fa-solid fa-circle-check text-success" style="font-size: 5rem;"></i>
            <h2 class="text-success mt-3">Orders Placed Successfully!</h2>
            <p class="lead">Order IDs: <strong><?= implode(', #', $orderIds) ?></strong></p>
            <p>Delivery to: <strong><?= htmlspecialchars($address) ?></strong></p>
            <p>Contact: <strong><?= htmlspecialchars($phone) ?></strong></p>
            <div class="mt-4">
                <a href="views/track.php" class="btn btn-cafe me-2"><i class="fa-solid fa-truck me-1"></i>Track Orders</a>
                <a href="dashboard.php" class="btn btn-cafe-outline"><i class="fa-solid fa-home me-1"></i>Home</a>
            </div>
        </div>
    <?php else: ?>
        <h2 class="section-title text-center d-block mb-4">
            <i class="fa-solid fa-clipboard-check me-2 text-gold"></i>Checkout
        </h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="premium-card p-5 text-center">
                <i class="fa-solid fa-cart-arrow-down fa-3x text-muted mb-3"></i>
                <p class="text-muted">Your cart is empty.</p>
                <a href="views/order.php" class="btn btn-cafe">Browse Menu</a>
            </div>
        <?php else: ?>
            <!-- Order Summary by Canteen -->
            <?php foreach ($cartByCanteen as $canteenId => $canteenData): ?>
            <div class="premium-card mb-3">
                <div class="card-header bg-transparent border-secondary">
                    <strong class="text-gold"><i class="fa-solid fa-store me-2"></i><?= htmlspecialchars($canteenData['name']) ?></strong>
                </div>
                <ul class="list-group list-group-flush bg-transparent">
                    <?php foreach ($canteenData['items'] as $ci): ?>
                    <li class="list-group-item bg-transparent text-light d-flex justify-content-between">
                        <span><?= htmlspecialchars($ci['name']) ?> × <?= $ci['quantity'] ?></span>
                        <span class="text-gold">PKR <?= number_format($ci['subtotal'], 0) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="card-footer bg-transparent text-end border-secondary">
                    <strong>Subtotal: PKR <?= number_format($canteenData['subtotal'], 0) ?></strong>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="premium-card p-3 text-end mb-4">
                <h4 class="text-gold mb-0">Grand Total: PKR <?= number_format($grandTotal, 0) ?></h4>
            </div>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="premium-card p-4 mb-4">
                    <h5 class="text-gold mb-3"><i class="fa-solid fa-location-dot me-2"></i>Delivery Details</h5>
                    <div class="mb-3">
                        <label class="form-label text-light">Phone *</label>
                        <input type="tel" name="phone" class="form-control form-control-cafe" required value="<?= htmlspecialchars($phone) ?>" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Address *</label>
                        <textarea name="address" class="form-control form-control-cafe" rows="2" required><?= htmlspecialchars($address) ?></textarea>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-cafe btn-lg"><i class="fa-solid fa-check-circle me-2"></i>Place Order</button>
                    <a href="views/order.php" class="btn btn-cafe-outline">← Back to Menu</a>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
