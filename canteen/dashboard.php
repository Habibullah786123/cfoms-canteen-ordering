<?php
// canteen/dashboard.php - Canteen Owner Dashboard
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('canteen_owner');

$canteenId = $_SESSION['canteen_id'];
$canteenName = $_SESSION['canteen_name'];

// Get stats
$menuCount = $mysqli->query("SELECT COUNT(*) as cnt FROM menu WHERE canteen_id = $canteenId")->fetch_assoc()['cnt'];
$pendingOrders = $mysqli->query("SELECT COUNT(*) as cnt FROM orders WHERE canteen_id = $canteenId AND status = 'Pending'")->fetch_assoc()['cnt'];
$todayRevenue = $mysqli->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE canteen_id = $canteenId AND DATE(created_at) = CURDATE() AND status != 'Cancelled'")->fetch_assoc()['total'];

// Recent orders
$recentOrders = $mysqli->query("SELECT * FROM orders WHERE canteen_id = $canteenId ORDER BY created_at DESC LIMIT 5");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-gold mb-0"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</h2>
        <span class="badge bg-success"><i class="fa-solid fa-store me-1"></i><?= htmlspecialchars($canteenName) ?></span>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="premium-card p-4 text-center">
                <i class="fa-solid fa-utensils fa-2x text-gold mb-2"></i>
                <h3 class="text-light"><?= $menuCount ?></h3>
                <p class="text-muted mb-0">Menu Items</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-4 text-center">
                <i class="fa-solid fa-clock fa-2x text-warning mb-2"></i>
                <h3 class="text-light"><?= $pendingOrders ?></h3>
                <p class="text-muted mb-0">Pending Orders</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-4 text-center">
                <i class="fa-solid fa-coins fa-2x text-success mb-2"></i>
                <h3 class="text-light">PKR <?= number_format($todayRevenue, 0) ?></h3>
                <p class="text-muted mb-0">Today's Revenue</p>
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="premium-card p-4">
                <h5 class="text-gold mb-4"><i class="fa-solid fa-chart-line me-2"></i>Weekly Sales</h5>
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-4 h-100">
                <h5 class="text-gold mb-4"><i class="fa-solid fa-pizza-slice me-2"></i>Top Items</h5>
                <canvas id="itemsChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="premium-card p-4">
                <h5 class="text-gold mb-3"><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="menu.php?action=add" class="btn btn-cafe"><i class="fa-solid fa-plus me-2"></i>Add Menu Item</a>
                    <a href="orders.php" class="btn btn-cafe-outline"><i class="fa-solid fa-receipt me-2"></i>View All Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="premium-card p-4">
                <h5 class="text-gold mb-3"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Orders</h5>
                <?php if ($recentOrders->num_rows > 0): ?>
                <ul class="list-group list-group-flush bg-transparent">
                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                    <li class="list-group-item bg-transparent text-light d-flex justify-content-between border-secondary">
                        <span>#<?= $order['id'] ?></span>
                        <span class="badge bg-<?= $order['status'] === 'Pending' ? 'warning' : ($order['status'] === 'Completed' ? 'success' : 'secondary') ?>">
                            <?= $order['status'] ?>
                        </span>
                        <span>PKR <?= number_format($order['total_price'], 0) ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php else: ?>
                <p class="text-muted mb-0">No orders yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('api/stats.php')
        .then(response => response.json())
        .then(data => {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: data.sales.labels,
                    datasets: [{
                        label: 'Sales (PKR)',
                        data: data.sales.data,
                        borderColor: '#e8b86d',
                        backgroundColor: 'rgba(232, 184, 109, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: '#9ca3af' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9ca3af' }
                        }
                    }
                }
            });

            // Top Items Chart
            const itemsCtx = document.getElementById('itemsChart').getContext('2d');
            new Chart(itemsCtx, {
                type: 'doughnut',
                data: {
                    labels: data.items.labels,
                    datasets: [{
                        data: data.items.data,
                        backgroundColor: [
                            '#e8b86d',
                            '#d4a574',
                            '#b8956a',
                            '#8c7050',
                            '#6b543c'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9ca3af' }
                        }
                    }
                }
            });
        });
});
</script>

<?php include 'includes/footer.php'; ?>
