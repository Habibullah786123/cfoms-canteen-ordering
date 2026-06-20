<?php
// user/profile.php - Student Profile Settings
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('user');

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user data
$user = $mysqli->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    
    // Update basic info
    $stmt = $mysqli->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param('ssi', $phone, $address, $userId);
    
    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
        
        // Update password if provided
        if (!empty($newPassword)) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param('si', $hash, $userId);
            $stmt->execute();
            $message .= " Password changed.";
        }
        
        // Refresh data
        $user = $mysqli->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
    } else {
        $error = "Failed to update profile.";
    }
    $stmt->close();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5" style="max-width: 800px;">
    <h2 class="text-gold mb-4 text-center"><i class="fa-solid fa-user-gear me-2"></i>My Profile</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="premium-card p-5">
        <form method="POST">
            <h5 class="text-light mb-4 border-bottom border-secondary pb-2">Personal Information</h5>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label text-light">Username</label>
                    <input type="text" class="form-control form-control-cafe" value="<?= htmlspecialchars($user['username']) ?>" disabled />
                    <small class="text-muted">Username cannot be changed.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light">Phone Number</label>
                    <input type="tel" name="phone" class="form-control form-control-cafe" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="0300-1234567" />
                </div>
                <div class="col-12">
                    <label class="form-label text-light">Delivery Address (Room/Hostel)</label>
                    <input type="text" name="address" class="form-control form-control-cafe" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Room 101, Block A" />
                </div>
            </div>

            <h5 class="text-light mb-4 border-bottom border-secondary pb-2">Security</h5>
            <div class="mb-4">
                <label class="form-label text-light">New Password</label>
                <input type="password" name="new_password" class="form-control form-control-cafe" placeholder="Leave blank to keep current" />
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-cafe btn-lg">
                    <i class="fa-solid fa-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
