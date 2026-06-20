<?php
// auth/login.php - Combined Login for Users and Canteen Owners
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'super_admin') {
        header('Location: ../admin/dashboard.php');
    } elseif ($_SESSION['role'] === 'canteen_owner') {
        header('Location: ../canteen/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

$error = '';
$activeTab = isset($_GET['type']) && $_GET['type'] === 'canteen' ? 'canteen' : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $loginType = $_POST['login_type'] ?? 'user';

    if ($username && $password) {
        $targetRole = $loginType === 'canteen' ? 'canteen_owner' : 'user';
        
        if ($loginType === 'canteen') {
            $stmt = $mysqli->prepare("SELECT u.id, u.username, u.password_hash, u.role, c.id as canteen_id, c.name as canteen_name 
                                      FROM users u 
                                      LEFT JOIN canteens c ON c.owner_id = u.id 
                                      WHERE u.username = ? AND u.role = 'canteen_owner'");
        } else {
            $stmt = $mysqli->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? AND role = 'user'");
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($loginType === 'canteen') {
                    $_SESSION['canteen_id'] = $user['canteen_id'];
                    $_SESSION['canteen_name'] = $user['canteen_name'];
                    header('Location: ../canteen/dashboard.php');
                } else {
                    header('Location: ../user/dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials or account type.';
        }
        $stmt->close();
    } else {
        $error = 'Please fill in both fields.';
    }
    $activeTab = $_POST['login_type'] ?? 'user';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - CFOMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="/CFOMS/assets/css/style.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2520 50%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(45, 45, 45, 0.95);
            border: 1px solid rgba(212, 165, 116, 0.2);
            border-radius: 20px;
            padding: 2rem;
            max-width: 420px;
            width: 100%;
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
        .nav-tabs-custom .nav-link:hover:not(.active) {
            color: #d4a574;
        }
        .form-control-login {
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid rgba(212, 165, 116, 0.2);
            color: #f5f5f5;
            padding: 0.75rem 1rem;
            border-radius: 10px;
        }
        .form-control-login:focus {
            border-color: #d4a574;
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.15);
            background: rgba(26, 26, 26, 0.8);
            color: #f5f5f5;
        }
        .form-label { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-3">
        <i class="fa-solid fa-mug-hot fa-2x text-gold mb-2"></i>
        <h4 class="text-gold">Welcome Back</h4>
        <p class="text-muted small">Login to your account</p>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs-custom nav-fill" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'user' ? 'active' : '' ?>" href="?type=user" onclick="switchTab('user'); return false;">
                <i class="fa-solid fa-user me-1"></i>Student
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'canteen' ? 'active' : '' ?>" href="?type=canteen" onclick="switchTab('canteen'); return false;">
                <i class="fa-solid fa-store me-1"></i>Canteen
            </a>
        </li>
    </ul>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
        <?= csrf_field() ?>
        <input type="hidden" name="login_type" id="loginType" value="<?= $activeTab ?>">
        
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control form-control-login" placeholder="Enter username" required autofocus />
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control form-control-login" placeholder="Enter password" required />
        </div>
        <button type="submit" class="btn btn-cafe w-100 mb-3">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Login
        </button>
    </form>

    <div class="text-center">
        <p class="text-muted mb-2">Don't have an account? <a href="register.php<?= $activeTab === 'canteen' ? '?type=canteen' : '' ?>" class="text-gold">Register here</a></p>
        <a href="../user/dashboard.php" class="text-muted small"><i class="fa-solid fa-arrow-left me-1"></i>Back to Home</a>
    </div>
</div>

<script>
function switchTab(type) {
    document.getElementById('loginType').value = type;
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    event.target.closest('.nav-link').classList.add('active');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
