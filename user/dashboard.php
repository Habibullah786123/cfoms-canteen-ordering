<?php
// user/dashboard.php - Student Landing Page
require_once '../includes/session.php';
require_once '../config/db_connect.php';
// Public page

include 'includes/header.php';
include 'includes/navbar.php';

$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : '';

// Fetch active canteens
$canteens = $mysqli->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM menu WHERE canteen_id = c.id AND is_available = 1) as menu_count,
           (SELECT AVG(rating) FROM reviews WHERE canteen_id = c.id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE canteen_id = c.id) as review_count
    FROM canteens c WHERE c.is_active = 1 ORDER BY c.name ASC LIMIT 6
");

// Featured items (random from all canteens)
$featured = $mysqli->query("
    SELECT m.*, c.name as canteen_name 
    FROM menu m 
    JOIN canteens c ON m.canteen_id = c.id 
    WHERE m.is_available = 1 AND c.is_active = 1 
    ORDER BY RAND() LIMIT 6
");
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container position-relative">
        <h1 class="hero-title animate-fadeInUp"><i class="fa-solid fa-mug-hot me-3"></i>CFOMS</h1>
        <p class="hero-subtitle animate-fadeInUp" style="animation-delay: 0.1s;">
            University Canteen Food Ordering System. Order delicious food from multiple canteens right from your phone!
        </p>
        <div class="animate-fadeInUp" style="animation-delay: 0.2s;">
            <a href="canteens.php" class="btn btn-cafe btn-lg me-2">
                <i class="fa-solid fa-store me-2"></i>Browse Canteens
            </a>
            <a href="views/order.php" class="btn btn-cafe-outline btn-lg">
                <i class="fa-solid fa-utensils me-2"></i>View All Food
            </a>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5" style="background: var(--color-dark-lighter);">
    <div class="container">
        <h2 class="section-title text-center mb-5">
            <i class="fa-solid fa-list-check me-2 text-gold"></i>How It Works
        </h2>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-3">
                    <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-3 border border-secondary" style="width: 80px; height: 80px;">
                        <span class="text-gold fs-2 fw-bold">1</span>
                    </div>
                    <h5 class="text-light">Browse Menu</h5>
                    <p class="text-muted">Explore food from multiple canteens in one place.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-3 border border-secondary" style="width: 80px; height: 80px;">
                        <span class="text-gold fs-2 fw-bold">2</span>
                    </div>
                    <h5 class="text-light">Order & Pay</h5>
                    <p class="text-muted">Add items to cart and place your order instantly.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-3 border border-secondary" style="width: 80px; height: 80px;">
                        <span class="text-gold fs-2 fw-bold">3</span>
                    </div>
                    <h5 class="text-light">Track & Eat</h5>
                    <p class="text-muted">Get real-time updates and enjoy your meal!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Canteens Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">
            <i class="fa-solid fa-store me-2 text-gold"></i>Our Canteens
        </h2>
        
        <div class="row g-4">
            <?php while ($c = $canteens->fetch_assoc()): ?>
            <div class="col-md-4 col-lg-4">
                <a href="views/order.php?canteen=<?= $c['id'] ?>" class="text-decoration-none">
                    <div class="premium-card h-100">
                        <?php if ($c['logo']): ?>
                            <img src="../uploads/canteens/<?= htmlspecialchars($c['logo']) ?>" 
                                 class="card-img-top" style="height: 150px; object-fit: cover;" />
                        <?php else: ?>
                            <div class="bg-dark-card d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="fa-solid fa-store fa-3x text-gold"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h5 class="card-title text-light"><?= htmlspecialchars($c['name']) ?></h5>
                            <div class="mb-2">
                                <span class="badge bg-success me-1"><?= $c['menu_count'] ?> items</span>
                                <?php if ($c['avg_rating']): ?>
                                    <span class="text-gold"><i class="fa-solid fa-star me-1"></i><?= number_format($c['avg_rating'], 1) ?> (<?= $c['review_count'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted small">No ratings yet</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="canteens.php" class="btn btn-cafe-outline">View All Canteens →</a>
        </div>
    </div>
</section>

<!-- Featured Items -->
<section class="py-5" style="background: var(--color-dark-lighter);">
    <div class="container">
        <h2 class="section-title text-center mb-5">
            <i class="fa-solid fa-star me-2 text-gold"></i>Featured Items
        </h2>
        
        <div class="row g-4">
            <?php while ($item = $featured->fetch_assoc()): ?>
            <div class="col-md-4 col-lg-2">
                <a href="views/product.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                    <div class="menu-card premium-card h-100">
                        <?php if ($item['image']): ?>
                            <img src="../uploads/menu/<?= htmlspecialchars($item['image']) ?>" 
                                 class="card-img-top" style="height: 120px; object-fit: cover;" />
                        <?php else: ?>
                            <div class="bg-dark-card d-flex align-items-center justify-content-center" style="height: 120px;">
                                <i class="fa-solid fa-burger fa-2x text-gold"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body p-2 text-center">
                            <h6 class="card-title text-light small mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($item['canteen_name']) ?></small>
                            <p class="price-tag mt-2 mb-0" style="font-size: 0.85rem;">PKR <?= number_format($item['price'], 0) ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
