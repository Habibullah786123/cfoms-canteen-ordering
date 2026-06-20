<?php
// admin/users.php - Manage All Users
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('super_admin');

$message = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    $userId = intval($_POST['delete']);
    // Don't allow deleting self
    if ($userId !== $_SESSION['user_id']) {
        $mysqli->query("DELETE FROM users WHERE id = $userId AND role = 'user'");
        $message = 'User deleted.';
    }
}

// Filter
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$whereClause = "1=1";
if ($roleFilter) {
    $whereClause = "role = '" . $mysqli->real_escape_string($roleFilter) . "'";
}

$users = $mysqli->query("SELECT * FROM users WHERE $whereClause ORDER BY created_at DESC");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-admin mb-0"><i class="fa-solid fa-users me-2"></i>Manage Users</h2>
        <div class="btn-group">
            <a href="users.php" class="btn btn-sm <?= !$roleFilter ? 'btn-admin' : 'btn-admin-outline' ?>">All</a>
            <a href="?role=user" class="btn btn-sm <?= $roleFilter === 'user' ? 'btn-admin' : 'btn-admin-outline' ?>">Students</a>
            <a href="?role=canteen_owner" class="btn btn-sm <?= $roleFilter === 'canteen_owner' ? 'btn-admin' : 'btn-admin-outline' ?>">Canteen Owners</a>
            <a href="?role=super_admin" class="btn btn-sm <?= $roleFilter === 'super_admin' ? 'btn-admin' : 'btn-admin-outline' ?>">Admins</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="admin-card p-4">
        <table class="table table-admin table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td>
                        <span class="badge bg-<?= 
                            $u['role'] === 'super_admin' ? 'danger' : 
                            ($u['role'] === 'canteen_owner' ? 'warning text-dark' : 'info') 
                        ?>">
                            <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                        </span>
                    </td>
                    <td class="text-white-50"><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                    <td class="text-white-50"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['role'] === 'user'): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="delete" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-white-50">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
