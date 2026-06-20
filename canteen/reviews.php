<?php
// canteen/reviews.php - View Canteen Reviews
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('canteen_owner');

$canteenId = $_SESSION['canteen_id'];

// Calculate stats
$stats = $mysqli->query("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as stars_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as stars_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as stars_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as stars_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as stars_1
    FROM reviews WHERE canteen_id = $canteenId
")->fetch_assoc();

// Fetch reviews
$reviews = $mysqli->query("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.canteen_id = $canteenId 
    ORDER BY r.created_at DESC
");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <h2 class="text-gold mb-4"><i class="fa-solid fa-star me-2"></i>Reviews & Feedback</h2>

    <!-- Stats Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="premium-card p-4 text-center h-100">
                <h1 class="display-3 fw-bold text-gold"><?= number_format($stats['avg_rating'] ?? 0, 1) ?></h1>
                <div class="mb-2">
                    <?php
                    $rating = round($stats['avg_rating'] ?? 0);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $rating ? '<i class="fa-solid fa-star text-gold"></i>' : '<i class="fa-regular fa-star text-muted"></i>';
                    }
                    ?>
                </div>
                <p class="text-muted"><?= $stats['total_reviews'] ?> total reviews</p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="premium-card p-4 h-100">
                <h5 class="text-light mb-3">Rating Breakdown</h5>
                <?php
                $total = $stats['total_reviews'] > 0 ? $stats['total_reviews'] : 1;
                for ($i = 5; $i >= 1; $i--) {
                    $count = $stats["stars_$i"];
                    $percent = ($count / $total) * 100;
                    ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted small me-2" style="width: 20px;"><?= $i ?></span>
                        <i class="fa-solid fa-star text-gold me-2 small"></i>
                        <div class="progress flex-grow-1" style="height: 6px; background: rgba(255,255,255,0.1);">
                            <div class="progress-bar bg-gold" role="progressbar" style="width: <?= $percent ?>%; background-color: var(--color-primary);"></div>
                        </div>
                        <span class="text-muted small ms-2" style="width: 30px; text-align: right;"><?= $count ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Review List -->
    <div class="premium-card">
        <div class="card-header bg-transparent border-secondary p-3">
            <h5 class="text-light mb-0">Recent Feedback</h5>
        </div>
        <div class="card-body p-0">
            <?php if ($reviews->num_rows === 0): ?>
                <div class="p-4 text-center text-muted">No reviews yet.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php while ($r = $reviews->fetch_assoc()): ?>
                        <div class="list-group-item bg-transparent border-secondary p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong class="text-light me-2"><?= htmlspecialchars($r['username']) ?></strong>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= $i <= $r['rating'] ? 'fa-solid' : 'fa-regular' ?> fa-star text-gold small"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted"><?= date('M j, Y', strtotime($r['created_at'])) ?></small>
                            </div>
                            <?php if ($r['comment']): ?>
                                <p class="text-light mb-0 small"><?= htmlspecialchars($r['comment']) ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-0 small fst-italic">No comment provided.</p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
