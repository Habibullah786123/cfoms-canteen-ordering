<?php
// user/api/order_status.php - Poll for order status updates
require_once '../../config/db_connect.php';
require_once '../../includes/session.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($orderId <= 0) {
    echo json_encode(['error' => 'Invalid ID']);
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['status' => $row['status']]);
} else {
    echo json_encode(['error' => 'Order not found']);
}
$stmt->close();
