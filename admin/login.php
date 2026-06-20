<?php
// admin/login.php - Super Admin Login
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in as super_admin, redirect
if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
    header('Location: dashboard.php');
    exit();
}

// Check if admin user exists, if not create default super_admin
$checkAdmin = $mysqli->query("SELECT id, role FROM users WHERE username = 'admin' LIMIT 1");
if ($checkAdmin->num_rows === 0) {
    $defaultPassword = password_hash('admin1234', PASSWORD_DEFAULT);
    $mysqli->query("INSERT INTO users (username, password_hash, role) VALUES ('admin', '$defaultPassword', 'super_admin')");
} else {
    $admin = $checkAdmin->fetch_assoc();
    if ($admin['role'] !== 'super_admin') {
        $mysqli->query("UPDATE users SET role = 'super_admin' WHERE username = 'admin'");
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $mysqli->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? AND role = 'super_admin'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials or not a super admin account.';
        }
        $stmt->close();
    } else {
        $error = 'Please fill in both fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Super Admin - CFOMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 400px;
            width: 100%;
        }
        .login-card h3 { color: #fff; }
        .login-card .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .login-card .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #7c3aed;
            box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
            color: #fff;
        }
        .login-card .form-label { color: rgba(255,255,255,0.8); }
        .btn-admin {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            border: none;
            color: #fff;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
            color: #fff;
        }
        .admin-badge {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <span class="admin-badge mb-3 d-inline-block"><i class="fa-solid fa-shield-halved me-1"></i>SUPER ADMIN</span>
        <h3><i class="fa-solid fa-mug-hot me-2"></i>CFOMS</h3>
        <p class="text-white-50">System Administration</p>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus />
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required />
        </div>
        <button type="submit" class="btn btn-admin w-100 mb-3">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Login
        </button>
    </form>
    <div class="text-center">
        <a href="../user/dashboard.php" class="text-white-50 small"><i class="fa-solid fa-arrow-left me-1"></i>Back to Home</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
