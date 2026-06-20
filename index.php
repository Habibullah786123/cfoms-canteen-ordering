<?php
// index.php - Main entry point with role-based redirection
require_once 'includes/session.php';

// If logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    if (is_super_admin()) {
        header('Location: /CFOMS/admin/dashboard.php');
    } elseif (is_canteen_owner()) {
        header('Location: /CFOMS/canteen/dashboard.php');
    } else {
        header('Location: /CFOMS/user/dashboard.php');
    }
    exit();
}

// Not logged in - redirect to user landing page (public)
header('Location: /CFOMS/user/dashboard.php');
exit();
