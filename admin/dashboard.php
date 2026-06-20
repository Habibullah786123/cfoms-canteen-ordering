<?php
// admin/dashboard.php - Super Admin Dashboard
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('super_admin');

// Stats
$totalCanteens = $mysqli->query("SELECT COUNT(*) as cnt FROM canteens")->fetch_assoc()['cnt'];
$activeCanteens = $mysqli->query("SELECT COUNT(*) as cnt FROM canteens WHERE is_active = 1")->fetch_assoc()['cnt'];
$totalUsers = $mysqli->query("SELECT COUNT(*) as cnt FROM users WHERE role = 'user'")->fetch_assoc()['cnt'];
$totalOrders = $mysqli->query("SELECT COUNT(*) as cnt FROM orders")->fetch_assoc()['cnt'];
$totalRevenue = $mysqli->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE status != 'Cancelled'")->fetch_assoc()['total'];

// Recent canteens
$recentCanteens = $mysqli->query("SELECT c.*, u.username as owner FROM canteens c JOIN users u ON c.owner_id = u.id ORDER BY c.created_at DESC LIMIT 5");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <h2 class="text-admin mb-4"><i class="fa-solid fa-chart-pie me-2"></i>System Overview</h2>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="admin-card p-4 text-center">
                <i class="fa-solid fa-store fa-2x text-admin mb-2"></i>
                <h3><?= $totalCanteens ?></h3>
                <p class="text-white-50 mb-0">Total Canteens</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-4 text-center">
                <i class="fa-solid fa-circle-check fa-2x text-success mb-2"></i>
                <h3><?= $activeCanteens ?></h3>
                <p class="text-white-50 mb-0">Active Canteens</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="admin-card p-4 text-center">
                <i class="fa-solid fa-users fa-2x text-info mb-2"></i>
                <h3><?= $totalUsers ?></h3>
                <p class="text-white-50 mb-0">Registered Users</p>
            </div>
        </div>

    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="admin-card p-4">
                <h5 class="text-admin mb-3"><i class="fa-solid fa-store me-2"></i>Recent Canteens</h5>
                <table class="table table-admin table-hover">
                    <thead>
                        <tr><th>Canteen</th><th>Owner</th><th>Status</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($c = $recentCanteens->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td class="text-white-50"><?= htmlspecialchars($c['owner']) ?></td>
                            <td>
                                <span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="text-white-50"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="canteens.php" class="btn btn-admin-outline btn-sm">View All Canteens</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card p-4">
                <h5 class="text-admin mb-3"><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="canteens.php" class="btn btn-admin"><i class="fa-solid fa-store me-2"></i>Manage Canteens</a>
                    <a href="users.php" class="btn btn-admin-outline"><i class="fa-solid fa-users me-2"></i>Manage Users</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
