<?php
// auth/register.php - Combined Registration for Users and Canteen Owners
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'canteen_owner') {
        header('Location: ../canteen/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

$errors = [];
$success = '';
$activeTab = isset($_GET['type']) && $_GET['type'] === 'canteen' ? 'canteen' : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    $regType = $_POST['reg_type'] ?? 'user';
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    // Validation
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // Check username exists
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Username already exists.";
        }
        $stmt->close();
    }

    // Canteen-specific validation
    if ($regType === 'canteen') {
        $canteenName = sanitize($_POST['canteen_name'] ?? '');
        $canteenDescription = sanitize($_POST['canteen_description'] ?? '');
        
        if (empty($canteenName)) {
            $errors[] = "Canteen name is required.";
        }
    }

    if (empty($errors)) {
        $mysqli->begin_transaction();
        
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = $regType === 'canteen' ? 'canteen_owner' : 'user';
            
            $stmt = $mysqli->prepare("INSERT INTO users (username, password_hash, role, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $username, $passwordHash, $role, $phone, $address);
            $stmt->execute();
            $userId = $stmt->insert_id;
            $stmt->close();

            // If canteen, also create canteen record
            if ($regType === 'canteen') {
                // Handle logo upload
                $logoName = null;
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = mime_content_type($_FILES['logo']['tmp_name']);
                    
                    if (in_array($fileType, $allowedTypes)) {
                        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                        $logoName = 'canteen_' . $userId . '_' . time() . '.' . $ext;
                        $uploadDir = '../uploads/canteens/';
                        
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
                    }
                }

                $stmt = $mysqli->prepare("INSERT INTO canteens (name, description, logo, owner_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('sssi', $canteenName, $canteenDescription, $logoName, $userId);
                $stmt->execute();
                $stmt->close();
            }

            $mysqli->commit();
            $success = "Registration successful! You can now login.";
        } catch (Exception $e) {
            $mysqli->rollback();
            $errors[] = "Registration failed. Please try again.";
        }
    }
    $activeTab = $regType;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - CFOMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="/CFOMS/assets/css/style.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2520 50%, #1a1a1a 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .register-card {
            background: rgba(45, 45, 45, 0.95);
            border: 1px solid rgba(212, 165, 116, 0.2);
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
        }
        .nav-tabs-custom {
            border: none;
            background: rgba(26, 26, 26, 0.6);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 1.5rem;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            border-radius: 10px;
            color: rgba(255,255,255,0.6);
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s;
        }
        .nav-tabs-custom .nav-link.active {
            background: linear-gradient(135deg, #d4a574 0%, #e8b86d 100%);
            color: #1a1a1a;
        }
        .form-control-reg {
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid rgba(212, 165, 116, 0.2);
            color: #f5f5f5;
            padding: 0.75rem 1rem;
            border-radius: 10px;
        }
        .form-control-reg:focus {
            border-color: #d4a574;
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.15);
            background: rgba(26, 26, 26, 0.8);
            color: #f5f5f5;
        }
        .form-label { color: rgba(255,255,255,0.8); }
        .canteen-fields { display: none; }
        .canteen-fields.active { display: block; }
    </style>
</head>
<body>
<div class="container">
    <div class="register-card">
        <div class="text-center mb-3">
            <i class="fa-solid fa-user-plus fa-2x text-gold mb-2"></i>
            <h4 class="text-gold">Create Account</h4>
            <p class="text-muted small">Join the CFOMS network</p>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs-custom nav-fill" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'user' ? 'active' : '' ?>" href="#" onclick="switchTab('user'); return false;">
                    <i class="fa-solid fa-user me-1"></i>Student
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab === 'canteen' ? 'active' : '' ?>" href="#" onclick="switchTab('canteen'); return false;">
                    <i class="fa-solid fa-store me-1"></i>Canteen Owner
                </a>
            </li>
        </ul>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <div class="text-center">
                <a href="login.php<?= $activeTab === 'canteen' ? '?type=canteen' : '' ?>" class="btn btn-cafe">Login Now</a>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="reg_type" id="regType" value="<?= $activeTab ?>">
                
                <!-- Common Fields -->
                <div class="mb-3">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control form-control-reg" required minlength="3" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control form-control-reg" required minlength="6" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control form-control-reg" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control form-control-reg" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" />
                </div>
                
                <!-- User-only: Address -->
                <div class="user-fields <?= $activeTab === 'user' ? 'active' : '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control form-control-reg" rows="2"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Canteen Fields -->
                <div class="canteen-fields <?= $activeTab === 'canteen' ? 'active' : '' ?>">
                    <hr class="border-secondary my-3">
                    <h6 class="text-gold mb-3"><i class="fa-solid fa-store me-2"></i>Canteen Details</h6>
                    <div class="mb-3">
                        <label class="form-label">Canteen Name *</label>
                        <input type="text" name="canteen_name" class="form-control form-control-reg" value="<?= htmlspecialchars($_POST['canteen_name'] ?? '') ?>" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="canteen_description" class="form-control form-control-reg" rows="2"><?= htmlspecialchars($_POST['canteen_description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Canteen Logo</label>
                        <input type="file" name="logo" class="form-control form-control-reg" accept="image/*" />
                    </div>
                </div>

                <button type="submit" class="btn btn-cafe w-100 mb-3">
                    <i class="fa-solid fa-user-plus me-2"></i>Register
                </button>
            </form>

            <div class="text-center">
                <p class="text-muted mb-2">Already have an account? <a href="login.php<?= $activeTab === 'canteen' ? '?type=canteen' : '' ?>" class="text-gold">Login here</a></p>
                <a href="../user/dashboard.php" class="text-muted small"><i class="fa-solid fa-arrow-left me-1"></i>Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function switchTab(type) {
    document.getElementById('regType').value = type;
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    event.target.closest('.nav-link').classList.add('active');
    
    // Toggle field visibility
    document.querySelector('.canteen-fields').classList.toggle('active', type === 'canteen');
    document.querySelector('.user-fields').classList.toggle('active', type === 'user');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
