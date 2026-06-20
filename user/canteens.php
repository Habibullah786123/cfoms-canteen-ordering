<?php
// user/canteens.php - List All Active Canteens
require_once '../includes/session.php';
require_once '../config/db_connect.php';
// Public page - no login required

include 'includes/header.php';
include 'includes/navbar.php';

// Fetch active canteens with menu count
$canteens = $mysqli->query("
    SELECT c.*, u.username as owner,
           (SELECT COUNT(*) FROM menu WHERE canteen_id = c.id AND is_available = 1) as menu_count,
           (SELECT AVG(rating) FROM reviews WHERE canteen_id = c.id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE canteen_id = c.id) as review_count
    FROM canteens c 
    JOIN users u ON c.owner_id = u.id 
    WHERE c.is_active = 1
    ORDER BY c.name ASC
");
?>

<div class="container py-4">
    <h2 class="section-title text-center mb-5">
        <i class="fa-solid fa-store me-2 text-gold"></i>University Canteens
    </h2>

    <?php if ($canteens->num_rows === 0): ?>
    <div class="premium-card p-5 text-center">
        <i class="fa-solid fa-store fa-3x text-muted mb-3"></i>
        <p class="text-muted">No canteens available at the moment.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php while ($c = $canteens->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <a href="views/order.php?canteen=<?= $c['id'] ?>" class="text-decoration-none">
                <div class="premium-card h-100">
                    <?php if ($c['logo']): ?>
                        <img src="../uploads/canteens/<?= htmlspecialchars($c['logo']) ?>" 
                             class="card-img-top" 
                             style="height: 180px; object-fit: cover;" 
                             alt="<?= htmlspecialchars($c['name']) ?>" />
                    <?php else: ?>
                        <div class="bg-dark-card d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fa-solid fa-store fa-4x text-gold"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title text-light"><?= htmlspecialchars($c['name']) ?></h5>
                        <p class="card-text text-muted small">
                            <?= htmlspecialchars(substr($c['description'] ?? 'Delicious food awaits!', 0, 80)) ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-success"><?= $c['menu_count'] ?> Items</span>
                            <?php if ($c['avg_rating']): ?>
                                <span class="text-gold small"><i class="fa-solid fa-star me-1"></i><?= number_format($c['avg_rating'], 1) ?> (<?= $c['review_count'] ?>)</span>
                            <?php endif; ?>
                            <span class="text-gold"><i class="fa-solid fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
