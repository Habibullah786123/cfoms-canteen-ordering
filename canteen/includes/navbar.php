<?php
$canteenName = $_SESSION['canteen_name'] ?? 'Canteen';
$canteenId = $_SESSION['canteen_id'] ?? 0;

// Fetch status (optimized to only fetch if not already set, but for now simple query is fine)
$statusIcon = '';
if ($canteenId) {
    // We need $mysqli. Check if it's available, if not assume included by parent.
    // Ideally navbar is included after db_connect.
    if (isset($mysqli)) {
        $stmt = $mysqli->prepare("SELECT is_active FROM canteens WHERE id = ?");
        $stmt->bind_param('i', $canteenId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if ($row['is_active']) {
                $statusIcon = '<span class="badge bg-success ms-2" title="Active"><i class="fa-solid fa-check-circle"></i></span>';
            } else {
                $statusIcon = '<span class="badge bg-danger ms-2" title="Inactive - Contact Admin"><i class="fa-solid fa-circle-xmark"></i></span>';
            }
        }
        $stmt->close();
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-cafe sticky-top">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
      <i class="fa-solid fa-store me-2"></i><?= htmlspecialchars($canteenName) ?><?= $statusIcon ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#canteenNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="canteenNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-chart-line me-1"></i>Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="menu.php"><i class="fa-solid fa-utensils me-1"></i>Menu</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="orders.php"><i class="fa-solid fa-receipt me-1"></i>Orders</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reviews.php"><i class="fa-solid fa-star me-1"></i>Reviews</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="../user/dashboard.php" target="_blank"><i class="fa-solid fa-external-link-alt me-1"></i>Visit Site</a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="profile.php"><i class="fa-solid fa-gear me-1"></i>Settings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../auth/logout.php"><i class="fa-solid fa-sign-out-alt me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
