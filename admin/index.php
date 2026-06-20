<?php
// admin/index.php
require_once '../includes/session.php';

session_start();
require_login('admin');

header('Location: dashboard.php');
exit();
