<?php
// includes/session.php - Multi-role session management

// Include functions if not already included
if (!function_exists('sanitize')) {
    require_once __DIR__ . '/functions.php';
}

// Start session only if not already started
// Configure session security
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '', // Default to current domain
        'secure' => isset($_SERVER['HTTPS']), // Only secure if HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user is a specific role
 */
function is_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if current user is super admin
 */
function is_super_admin() {
    return is_role('super_admin');
}

/**
 * Check if current user is canteen owner
 */
function is_canteen_owner() {
    return is_role('canteen_owner');
}

/**
 * Check if current user is a regular user (student)
 */
function is_user() {
    return is_role('user');
}

/**
 * Get current user's canteen ID (for canteen owners)
 */
function get_canteen_id() {
    return $_SESSION['canteen_id'] ?? null;
}

/**
 * Require login with optional role check
 * Redirects to appropriate login page based on required role
 */
function require_login($role = null) {
    if (!is_logged_in()) {
        if ($role === 'super_admin') {
            redirect('/CFOMS/admin/login.php');
        } elseif ($role === 'canteen_owner') {
            redirect('/CFOMS/canteen/login.php');
        } else {
            redirect('/CFOMS/auth/login.php');
        }
    }

    // If role is specified, check it
    if ($role !== null && !is_role($role)) {
        // Redirect to their correct dashboard
        if (is_super_admin()) {
            redirect('/CFOMS/admin/dashboard.php');
        } elseif (is_canteen_owner()) {
            redirect('/CFOMS/canteen/dashboard.php');
        } else {
            redirect('/CFOMS/user/dashboard.php');
        }
    }
}

/**
 * Redirect helper
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}
