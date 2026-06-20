<?php
// user/contact.php
require_once '../includes/session.php';
require_once '../config/db_connect.php';
// CSRF functions are in functions.php which is included by session.php (via check)
if (!function_exists('generate_csrf_token')) {
    require_once '../includes/functions.php';
}

include 'includes/header.php';
include 'includes/navbar.php';

$success = '';
$error = '';

// Pre-fill for logged in users
$name = '';
$email = '';
if (is_logged_in()) {
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $name = $res['username'] ?? '';
    // We don't have email in users table based on schema, so we leave it empty
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF verification failed.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (!$name || !$email || !$subject || !$message) {
            $error = 'All fields are required.';
        } else {
            $userId = is_logged_in() ? $_SESSION['user_id'] : null;
            
            $stmt = $mysqli->prepare("INSERT INTO messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('issss', $userId, $name, $email, $subject, $message);
            
            if ($stmt->execute()) {
                $success = "Message sent successfully! We'll get back to you soon.";
                // Clear form if not logged in (logged in user keeps name)
                if (!is_logged_in()) {
                    $name = $email = '';
                } 
                $subject = $message = '';
            } else {
                $error = "Failed to send message.";
            }
            $stmt->close();
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="premium-card p-5">
                <h2 class="section-title text-center mb-4">
                    <i class="fa-solid fa-envelope me-2 text-gold"></i>Contact Us
                </h2>
                <p class="text-center text-muted mb-4">Have questions or feedback? We'd love to hear from you.</p>

                <?php if ($success): ?>
                    <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light">Name</label>
                            <input type="text" name="name" class="form-control form-control-cafe" required value="<?= htmlspecialchars($name) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Email</label>
                            <input type="email" name="email" class="form-control form-control-cafe" required value="<?= htmlspecialchars($email) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-light">Subject</label>
                            <input type="text" name="subject" class="form-control form-control-cafe" required value="<?= htmlspecialchars($subject ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-light">Message</label>
                            <textarea name="message" class="form-control form-control-cafe" rows="5" required><?= htmlspecialchars($message ?? '') ?></textarea>
                        </div>
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-cafe btn-lg px-5">
                                <i class="fa-solid fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </div>
                </form>

                <div class="row mt-5 text-center">
                    <div class="col-md-4">
                        <i class="fa-solid fa-location-dot text-gold mb-2"></i>
                        <p class="text-muted mb-0">University Campus, Block A</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fa-solid fa-phone text-gold mb-2"></i>
                        <p class="text-muted mb-0">+92 300 1234567</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fa-solid fa-envelope text-gold mb-2"></i>
                        <p class="text-muted mb-0">support@cfoms.edu</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
