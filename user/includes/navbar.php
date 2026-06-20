<?php
// Detect base path
$basePath = dirname($_SERVER['PHP_SELF']);
$basePath = str_replace('\\', '/', $basePath);
$isInViews = str_contains($basePath, '/views');
$linkPrefix = $isInViews ? '../' : '';

// Check if logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : '';

// Cart count
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<nav class="navbar navbar-expand-lg navbar-cafe sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= $linkPrefix ?>dashboard.php"><i class="fa-solid fa-mug-hot me-2"></i>CFOMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="userNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= $linkPrefix ?>dashboard.php"><i class="fa-solid fa-home me-1"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $linkPrefix ?>canteens.php"><i class="fa-solid fa-store me-1"></i>Canteens</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $linkPrefix ?>views/order.php"><i class="fa-solid fa-utensils me-1"></i>Menu</a></li>
        <?php if ($isLoggedIn): ?>
        <li class="nav-item"><a class="nav-link" href="<?= $linkPrefix ?>views/history.php"><i class="fa fa-history me-1"></i>Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $linkPrefix ?>views/track.php"><i class="fa fa-truck me-1"></i>Track</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="<?= $linkPrefix ?>about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $linkPrefix ?>contact.php">Contact</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if ($isLoggedIn && $cartCount > 0): ?>
        <li class="nav-item">
          <a class="nav-link position-relative" href="<?= $linkPrefix ?>checkout.php">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
              <?= $cartCount ?>
            </span>
          </a>
        </li>
        <?php endif; ?>
        <?php if ($isLoggedIn): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="fa-solid fa-user me-1"></i><?= $username ?></a>
          <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= $linkPrefix ?>profile.php"><i class="fa-solid fa-user-gear me-2"></i>My Profile</a></li>
            <li><a class="dropdown-item" href="<?= $linkPrefix ?>views/history.php"><i class="fa-solid fa-clock-rotate-left me-2"></i>Order History</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= $linkPrefix ?>../auth/logout.php"><i class="fa-solid fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= $linkPrefix ?>../auth/login.php"><i class="fa fa-sign-in-alt me-1"></i>Login</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn btn-cafe btn-sm ms-2 px-3" href="<?= $linkPrefix ?>../auth/register.php"><i class="fa fa-user-plus me-1"></i>Register</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>