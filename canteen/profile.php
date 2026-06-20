<?php
// canteen/profile.php - Canteen Profile Settings
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('canteen_owner');

$canteenId = $_SESSION['canteen_id'];
$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current canteen data
$canteen = $mysqli->query("SELECT * FROM canteens WHERE id = $canteenId")->fetch_assoc();
$user = $mysqli->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $canteenName = sanitize($_POST['canteen_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    if (empty($canteenName)) {
        $error = 'Canteen name is required.';
    } else {
        // Handle logo upload
        $logoName = $canteen['logo'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($_FILES['logo']['tmp_name']);
            
            if (in_array($fileType, $allowedTypes)) {
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logoName = 'canteen_' . $canteenId . '_' . time() . '.' . $ext;
                $uploadDir = '../uploads/canteens/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
            }
        }
        
        // Update canteen
        $stmt = $mysqli->prepare("UPDATE canteens SET name = ?, description = ?, logo = ? WHERE id = ?");
        $stmt->bind_param('sssi', $canteenName, $description, $logoName, $canteenId);
        $stmt->execute();
        $stmt->close();
        
        // Update user phone
        $stmt = $mysqli->prepare("UPDATE users SET phone = ? WHERE id = ?");
        $stmt->bind_param('si', $phone, $userId);
        $stmt->execute();
        $stmt->close();
        
        // Update session
        $_SESSION['canteen_name'] = $canteenName;
        $message = 'Profile updated successfully.';
        
        // Refresh data
        $canteen = $mysqli->query("SELECT * FROM canteens WHERE id = $canteenId")->fetch_assoc();
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <h2 class="text-gold mb-4"><i class="fa-solid fa-gear me-2"></i>Canteen Settings</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="premium-card p-4">
                <form method="POST" enctype="multipart/form-data">
                    <h5 class="text-gold mb-3">Canteen Information</h5>
                    <div class="mb-3">
                        <label class="form-label text-light">Canteen Name *</label>
                        <input type="text" name="canteen_name" class="form-control form-control-cafe" required value="<?= htmlspecialchars($canteen['name']) ?>" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Description</label>
                        <textarea name="description" class="form-control form-control-cafe" rows="3"><?= htmlspecialchars($canteen['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light">Phone Number</label>
                        <input type="tel" name="phone" class="form-control form-control-cafe" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" />
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-light">Logo</label>
                        <input type="file" name="logo" class="form-control form-control-cafe" accept="image/*" />
                    </div>
                    <button type="submit" class="btn btn-cafe"><i class="fa-solid fa-save me-2"></i>Save Changes</button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-4 text-center">
                <h5 class="text-gold mb-3">Current Logo</h5>
                <?php if ($canteen['logo']): ?>
                    <img src="../uploads/canteens/<?= htmlspecialchars($canteen['logo']) ?>" class="img-fluid rounded" style="max-height: 200px;" alt="Logo" />
                <?php else: ?>
                    <div class="bg-dark-card p-5 rounded">
                        <i class="fa-solid fa-store fa-4x text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No logo uploaded</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
