<?php
require_once dirname(__DIR__) . '/includes/auth.php';
logoutAdmin();
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
