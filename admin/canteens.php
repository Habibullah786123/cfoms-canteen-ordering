<?php
// admin/canteens.php - Manage All Canteens
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('super_admin');

$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }

    if (isset($_POST['toggle'])) {
        $canteenId = intval($_POST['toggle']);
        $mysqli->query("UPDATE canteens SET is_active = NOT is_active WHERE id = $canteenId");
        $message = 'Canteen status updated.';
    }

    // Handle delete
    if (isset($_POST['delete'])) {
        $canteenId = intval($_POST['delete']);
        // Delete canteen and its menu items
        $mysqli->query("DELETE FROM menu WHERE canteen_id = $canteenId");
        $mysqli->query("DELETE FROM canteens WHERE id = $canteenId");
        $message = 'Canteen deleted.';
    }
}

// Fetch all canteens
$canteens = $mysqli->query("
    SELECT c.*, u.username as owner, u.phone,
           (SELECT COUNT(*) FROM menu WHERE canteen_id = c.id) as menu_count,
           (SELECT COUNT(*) FROM orders WHERE canteen_id = c.id) as order_count
    FROM canteens c 
    JOIN users u ON c.owner_id = u.id 
    ORDER BY c.created_at DESC
");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <h2 class="text-admin mb-4"><i class="fa-solid fa-store me-2"></i>Manage Canteens</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($canteens->num_rows === 0): ?>
    <div class="admin-card p-5 text-center">
        <i class="fa-solid fa-store fa-3x text-white-50 mb-3"></i>
        <p class="text-white-50">No canteens registered yet.</p>
    </div>
    <?php else: ?>
    <div class="admin-card p-4">
        <table class="table table-admin table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Canteen</th>
                    <th>Owner</th>
                    <th>Menu Items</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = $canteens->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($c['name']) ?></strong>
                        <?php if ($c['description']): ?>
                            <br><small class="text-white-50"><?= htmlspecialchars(substr($c['description'], 0, 50)) ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($c['owner']) ?>
                        <?php if ($c['phone']): ?>
                            <br><small class="text-white-50"><?= htmlspecialchars($c['phone']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-info"><?= $c['menu_count'] ?></span></td>
                    <td><span class="badge bg-warning text-dark"><?= $c['order_count'] ?></span></td>
                    <td>
                        <span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                    <td>
                        <form method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="toggle" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $c['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                <i class="fa-solid fa-<?= $c['is_active'] ? 'pause' : 'play' ?>"></i>
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this canteen and all its data?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
