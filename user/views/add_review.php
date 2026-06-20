<?php
// user/views/add_review.php - Leave a review for an order
require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_login('user');

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Verify order belongs to user and is delivered
$stmt = $mysqli->prepare("SELECT o.id, o.canteen_id, c.name as canteen_name FROM orders o JOIN canteens c ON o.canteen_id = c.id WHERE o.id = ? AND o.user_id = ? AND (o.status = 'Ready' OR o.status = 'Completed' OR o.status = 'Delivered')");
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    // Check if review already exists
    header("Location: history.php");
    exit();
}

$canteenId = $order['canteen_id'];

// Check if already reviewed
$check = $mysqli->query("SELECT id FROM reviews WHERE order_id = $orderId");
if ($check->num_rows > 0) {
    header("Location: history.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comment = sanitize($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $error = "Please select a rating between 1 and 5.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO reviews (user_id, canteen_id, order_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiis', $userId, $canteenId, $orderId, $rating, $comment);
        if ($stmt->execute()) {
            $success = "Review submitted successfully!";
            // Redirect after 2 seconds
            header("refresh:2;url=history.php");
        } else {
            $error = "Failed to submit review.";
        }
        $stmt->close();
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5" style="max-width: 600px;">
    <div class="premium-card p-4">
        <h3 class="text-gold mb-3 text-center">Rate Your Experience</h3>
        <p class="text-center text-muted mb-4">How was your order from <strong><?= htmlspecialchars($order['canteen_name']) ?></strong>?</p>

        <?php if ($success): ?>
            <div class="alert alert-success text-center">
                <i class="fa-solid fa-check-circle fa-2x mb-2"></i><br>
                <?= $success ?>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4 text-center">
                    <label class="form-label d-block mb-2">Rating</label>
                    <div class="rating-stars" style="font-size: 2rem; color: var(--color-text-muted);">
                        <i class="fa-regular fa-star star-input" data-value="1"></i>
                        <i class="fa-regular fa-star star-input" data-value="2"></i>
                        <i class="fa-regular fa-star star-input" data-value="3"></i>
                        <i class="fa-regular fa-star star-input" data-value="4"></i>
                        <i class="fa-regular fa-star star-input" data-value="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="ratingValue" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Comment (Optional)</label>
                    <textarea name="comment" class="form-control form-control-cafe" rows="4" placeholder="Tell us what you liked or didn't like..."></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-cafe">Submit Review</button>
                    <a href="history.php" class="btn btn-link text-muted mt-2">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.star-input {
    cursor: pointer;
    transition: color 0.2s;
}
.star-input:hover {
    transform: scale(1.2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-input');
    const ratingInput = document.getElementById('ratingValue');
    let currentRating = 0;

    stars.forEach(star => {
        // Hover Effect
        star.addEventListener('mouseenter', function() {
            const val = this.getAttribute('data-value');
            highlightStars(val);
        });
        
        // Click Event
        star.addEventListener('click', function() {
            currentRating = this.getAttribute('data-value');
            ratingInput.value = currentRating;
            highlightStars(currentRating);
            
            // Visual feedback
            stars.forEach(s => s.classList.add('animate__animated', 'animate__pulse'));
            setTimeout(() => stars.forEach(s => s.classList.remove('animate__animated', 'animate__pulse')), 500);
        });
    });

    // Reset to selected rating when leaving container
    document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
        highlightStars(currentRating);
    });

    function highlightStars(value) {
        stars.forEach(s => {
            const sVal = s.getAttribute('data-value');
            if (sVal <= value) {
                s.classList.remove('fa-regular', 'text-muted');
                s.classList.add('fa-solid', 'text-gold');
                s.style.color = '#e8b86d'; // Force color
            } else {
                s.classList.remove('fa-solid', 'text-gold');
                s.classList.add('fa-regular', 'text-muted');
                s.style.color = 'var(--color-text-muted)';
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
