<nav class="navbar navbar-expand-lg navbar-admin sticky-top">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php"><i class="fa-solid fa-shield-halved me-2"></i>CFOMS Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-chart-pie me-1"></i>Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="canteens.php"><i class="fa-solid fa-store me-1"></i>Canteens</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="users.php"><i class="fa-solid fa-users me-1"></i>Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="messages.php"><i class="fa-solid fa-envelope me-1"></i>Messages</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../user/dashboard.php" target="_blank"><i class="fa-solid fa-external-link-alt me-1"></i>Visit Site</a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link text-admin"><i class="fa-solid fa-user-shield me-1"></i><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../auth/logout.php"><i class="fa-solid fa-sign-out-alt me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>