<?php
// canteen/api/stats.php - API for retrieving analytics data
require_once '../../config/db_connect.php';
require_once '../../includes/session.php';
// Ensure only canteen owners
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'canteen_owner') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$canteenId = $_SESSION['canteen_id'];
header('Content-Type: application/json');

// 1. Weekly Sales (Last 7 Days)
$weeklySales = [];
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('D', strtotime($date)); // Mon, Tue...
    
    $stmt = $mysqli->prepare("SELECT SUM(total_price) as total FROM orders WHERE canteen_id = ? AND DATE(created_at) = ? AND status != 'Cancelled'");
    $stmt->bind_param('is', $canteenId, $date);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $weeklySales[] = $res['total'] ?? 0;
    $stmt->close();
}

// 2. Top Selling Items (Top 5)
$topItems = [];
$itemLabels = [];
$itemCounts = [];

// Since items are JSON, we have to rely on a complex query or just parsing recently.
// For simplicity/performance in this structure, we'll parse the last 100 orders or use the 'inventory' reduction if we had it,
// but actually, we don't have a normalized order_items table.
// WORKAROUND: We will fetch all delivered orders and process in PHP (okay for MVP).
$res = $mysqli->query("SELECT items FROM orders WHERE canteen_id = $canteenId AND status != 'Cancelled' ORDER BY created_at DESC LIMIT 200");
$itemTally = [];
while ($row = $res->fetch_assoc()) {
    $items = json_decode($row['items'], true);
    if ($items) {
        foreach ($items as $item) {
            $name = $item['name'];
            $qty = $item['quantity'];
            if (!isset($itemTally[$name])) $itemTally[$name] = 0;
            $itemTally[$name] += $qty;
        }
    }
}
arsort($itemTally);
$top5 = array_slice($itemTally, 0, 5);
$itemLabels = array_keys($top5);
$itemCounts = array_values($top5);

echo json_encode([
    'sales' => [
        'labels' => $dates,
        'data' => $weeklySales
    ],
    'items' => [
        'labels' => $itemLabels,
        'data' => $itemCounts
    ]
]);
