<?php
// canteen/menu.php - Manage Canteen Menu
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('canteen_owner');

$canteenId = $_SESSION['canteen_id'];
$message = '';
$error = '';

// Handle delete
if (isset($_POST['delete'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    $itemId = intval($_POST['delete']);
    $stmt = $mysqli->prepare("DELETE FROM menu WHERE id = ? AND canteen_id = ?");
    $stmt->bind_param('ii', $itemId, $canteenId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = 'Item deleted successfully.';
    }
    $stmt->close();
}

// Handle add/edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    $itemId = intval($_POST['item_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;

    if (empty($name) || $price <= 0) {
        $error = 'Name and valid price are required.';
    } else {
        // Handle image upload
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            
            if (in_array($fileType, $allowedTypes)) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = 'menu_' . $canteenId . '_' . time() . '.' . $ext;
                $uploadDir = '../uploads/menu/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        if ($itemId > 0) {
            // Update
            if ($imageName) {
                $stmt = $mysqli->prepare("UPDATE menu SET name=?, description=?, price=?, is_available=?, image=? WHERE id=? AND canteen_id=?");
                $stmt->bind_param('ssdisii', $name, $description, $price, $isAvailable, $imageName, $itemId, $canteenId);
            } else {
                $stmt = $mysqli->prepare("UPDATE menu SET name=?, description=?, price=?, is_available=? WHERE id=? AND canteen_id=?");
                $stmt->bind_param('ssdiii', $name, $description, $price, $isAvailable, $itemId, $canteenId);
            }
            $stmt->execute();
            $message = 'Item updated successfully.';
        } else {
            // Insert
            $stmt = $mysqli->prepare("INSERT INTO menu (canteen_id, name, description, price, is_available, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issdis', $canteenId, $name, $description, $price, $isAvailable, $imageName);
            $stmt->execute();
            $message = 'Item added successfully.';
        }
        $stmt->close();
    }
}

// Fetch menu items for this canteen
$items = $mysqli->query("SELECT * FROM menu WHERE canteen_id = $canteenId ORDER BY name ASC");

// Check if editing
$editItem = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $mysqli->prepare("SELECT * FROM menu WHERE id = ? AND canteen_id = ?");
    $stmt->bind_param('ii', $editId, $canteenId);
    $stmt->execute();
    $editItem = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'add' || $editItem;

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-gold mb-0"><i class="fa-solid fa-utensils me-2"></i>Menu Management</h2>
        <?php if (!$showForm): ?>
        <a href="?action=add" class="btn btn-cafe"><i class="fa-solid fa-plus me-2"></i>Add Item</a>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($showForm): ?>
    <!-- Add/Edit Form -->
    <div class="premium-card p-4 mb-4">
        <h5 class="text-gold mb-3"><?= $editItem ? 'Edit Item' : 'Add New Item' ?></h5>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= $editItem['id'] ?? 0 ?>" />
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Name *</label>
                    <input type="text" name="name" class="form-control form-control-cafe" required value="<?= htmlspecialchars($editItem['name'] ?? '') ?>" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Price (PKR) *</label>
                    <input type="number" name="price" class="form-control form-control-cafe" step="0.01" min="0" required value="<?= $editItem['price'] ?? '' ?>" />
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-light">Description</label>
                <textarea name="description" class="form-control form-control-cafe" rows="2"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-light">Image</label>
                    <input type="file" name="image" class="form-control form-control-cafe" accept="image/*" />
                    <?php if ($editItem && $editItem['image']): ?>
                        <small class="text-muted">Current: <?= htmlspecialchars($editItem['image']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_available" id="is_available" class="form-check-input" <?= ($editItem['is_available'] ?? 1) ? 'checked' : '' ?> />
                        <label for="is_available" class="form-check-label text-light">Available</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-cafe"><i class="fa-solid fa-save me-2"></i>Save</button>
                <a href="menu.php" class="btn btn-cafe-outline">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Menu Items List -->
    <div class="row g-4">
        <?php if ($items->num_rows === 0): ?>
        <div class="col-12">
            <div class="premium-card p-5 text-center">
                <i class="fa-solid fa-utensils fa-3x text-muted mb-3"></i>
                <p class="text-muted">No menu items yet. Add your first item!</p>
                <a href="?action=add" class="btn btn-cafe">Add Item</a>
            </div>
        </div>
        <?php else: ?>
        <?php while ($item = $items->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <div class="menu-card premium-card h-100">
                <?php if ($item['image']): ?>
                    <img src="../uploads/menu/<?= htmlspecialchars($item['image']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="<?= htmlspecialchars($item['name']) ?>" />
                <?php else: ?>
                    <div class="bg-dark-card d-flex align-items-center justify-content-center" style="height: 150px;">
                        <i class="fa-solid fa-burger fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title text-light mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                        <span class="badge <?= $item['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                        </span>
                    </div>
                    <p class="card-text text-muted small"><?= htmlspecialchars(substr($item['description'], 0, 60)) ?>...</p>
                    <p class="price-tag mb-3">PKR <?= number_format($item['price'], 0) ?></p>
                    <div class="d-flex gap-2">
                        <a href="?edit=<?= $item['id'] ?>" class="btn btn-sm btn-cafe-outline"><i class="fa-solid fa-edit"></i></a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
