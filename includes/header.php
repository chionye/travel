<?php
require_once __DIR__ . '/auth.php';
$siteName = getSetting('site_name', 'SkyWave Travel');
$siteLogo = getSetting('site_logo');
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= e($siteName) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          sky: { 50:'#f0f9ff',100:'#e0f2fe',200:'#bae6fd',300:'#7dd3fc',400:'#38bdf8',500:'#0ea5e9',600:'#0284c7',700:'#0369a1',800:'#075985',900:'#0c4a6e' },
          teal:{ 50:'#f0fdfa',100:'#ccfbf1',400:'#2dd4bf',500:'#14b8a6',600:'#0d9488',700:'#0f766e' }
        }
      }
    }
  }
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="bg-slate-50 text-slate-800">

<!-- Navigation -->
<nav class="bg-white shadow-sm sticky top-0 z-50" id="navbar">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <!-- Logo -->
      <a href="<?= SITE_URL ?>" class="flex items-center gap-2 group">
        <?php if ($siteLogo): ?>
          <img src="<?= e(UPLOAD_URL . $siteLogo) ?>" alt="<?= e($siteName) ?>" class="h-10 w-auto">
        <?php else: ?>
          <div class="w-10 h-10 bg-gradient-to-br from-sky-500 to-teal-500 rounded-xl flex items-center justify-center">
            <i class="fas fa-paper-plane text-white text-lg"></i>
          </div>
          <span class="font-extrabold text-xl text-sky-700 group-hover:text-sky-600 transition"><?= e($siteName) ?></span>
        <?php endif; ?>
      </a>

      <!-- Desktop Nav -->
      <div class="hidden md:flex items-center gap-1">
        <a href="<?= SITE_URL ?>" class="nav-link <?= $currentPage==='index.php'?'active':'' ?>">Home</a>
        <a href="<?= SITE_URL ?>/flights.php" class="nav-link <?= $currentPage==='flights.php'?'active':'' ?>">Flights</a>
        <a href="<?= SITE_URL ?>/hotels.php" class="nav-link <?= $currentPage==='hotels.php'?'active':'' ?>">Hotels</a>
      </div>

      <!-- Auth -->
      <div class="hidden md:flex items-center gap-3">
        <?php if (isLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/dashboard.php" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-sky-600">
            <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center">
              <i class="fas fa-user text-sky-600 text-xs"></i>
            </div>
            <span><?= e($_SESSION['user_name']) ?></span>
          </a>
          <a href="<?= SITE_URL ?>/logout.php" class="text-sm text-gray-500 hover:text-red-500">Logout</a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/login.php" class="text-sm font-medium text-gray-700 hover:text-sky-600 px-4 py-2">Login</a>
          <a href="<?= SITE_URL ?>/register.php" class="btn-primary text-sm px-4 py-2">Sign Up</a>
        <?php endif; ?>
      </div>

      <!-- Mobile menu button -->
      <button class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100" id="mobileMenuBtn">
        <i class="fas fa-bars text-xl"></i>
      </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div class="md:hidden hidden bg-white border-t" id="mobileMenu">
    <div class="px-4 py-3 space-y-1">
      <a href="<?= SITE_URL ?>" class="block py-2 text-gray-700 font-medium">Home</a>
      <a href="<?= SITE_URL ?>/flights.php" class="block py-2 text-gray-700 font-medium">Flights</a>
      <a href="<?= SITE_URL ?>/hotels.php" class="block py-2 text-gray-700 font-medium">Hotels</a>
      <?php if (isLoggedIn()): ?>
        <a href="<?= SITE_URL ?>/dashboard.php" class="block py-2 text-sky-600 font-medium">My Dashboard</a>
        <a href="<?= SITE_URL ?>/logout.php" class="block py-2 text-red-500">Logout</a>
      <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php" class="block py-2 text-gray-700 font-medium">Login</a>
        <a href="<?= SITE_URL ?>/register.php" class="block py-2 text-sky-600 font-semibold">Sign Up Free</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
