<?php
// admin/messages.php - View User Messages
require_once '../includes/session.php';
require_once '../config/db_connect.php';
require_login('super_admin');

$message = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF verification failed');
    }
    $msgId = intval($_POST['delete']);
    $stmt = $mysqli->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param('i', $msgId);
    if ($stmt->execute()) {
        $message = 'Message deleted.';
    }
    $stmt->close();
}

// Fetch messages
$messages = $mysqli->query("SELECT m.*, u.username as registered_name FROM messages m LEFT JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-admin mb-0"><i class="fa-solid fa-envelope me-2"></i>User Messages</h2>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($messages->num_rows === 0): ?>
    <div class="admin-card p-5 text-center">
        <i class="fa-solid fa-envelope-open fa-3x text-white-50 mb-3"></i>
        <p class="text-white-50">No messages yet.</p>
    </div>
    <?php else: ?>
    <div class="admin-card p-4">
        <div class="table-responsive">
            <table class="table table-admin table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($msg = $messages->fetch_assoc()): ?>
                    <tr>
                        <td><?= $msg['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($msg['name']) ?></strong>
                            <br><small class="text-white-50"><?= htmlspecialchars($msg['email']) ?></small>
                            <?php if ($msg['registered_name']): ?>
                                <br><span class="badge bg-info text-dark">User: <?= htmlspecialchars($msg['registered_name']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($msg['subject']) ?></td>
                        <td>
                            <div style="max-width: 300px; max-height: 100px; overflow-y: auto;">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                            </div>
                        </td>
                        <td class="text-white-50"><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this message?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="delete" value="<?= $msg['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
