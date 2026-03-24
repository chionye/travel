<?php
require_once 'includes/auth.php';
logoutUser();
header('Location: ' . SITE_URL . '/login.php');
exit;
