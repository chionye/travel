<?php
require_once dirname(__DIR__) . '/../includes/auth.php';
requireAdmin();
$siteName    = getSetting('site_name', 'SkyWave Travel');
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Admin &mdash; <?= e($siteName) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
  .sidebar-link { @apply flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-700 hover:text-white transition-all text-sm font-medium; }
  .sidebar-link.active { @apply bg-sky-600 text-white shadow-lg; }
  .sidebar-link .icon { @apply w-5 text-center; }
  .admin-input { @apply w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white transition; }
  .admin-label { @apply block text-sm font-semibold text-gray-700 mb-1.5; }
  .btn-admin { @apply inline-flex items-center justify-center bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl px-5 py-2.5 text-sm transition; }
  .btn-danger { @apply inline-flex items-center justify-center bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl px-4 py-2 text-sm transition; }
  .btn-success { @apply inline-flex items-center justify-center bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl px-4 py-2 text-sm transition; }
  .btn-gray { @apply inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl px-4 py-2 text-sm transition; }
  .card-admin { @apply bg-white rounded-2xl shadow-sm border border-gray-100; }
  table.admin-table { @apply w-full text-sm; }
  table.admin-table th { @apply px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider bg-gray-50; }
  table.admin-table td { @apply px-4 py-3.5 border-b border-gray-50; }
  table.admin-table tr:last-child td { @apply border-0; }
  table.admin-table tbody tr:hover { @apply bg-sky-50/30; }
</style>
</head>
<body class="bg-slate-100 font-sans">

<div class="flex h-screen overflow-hidden">
  <!-- Sidebar -->
  <aside class="w-64 bg-slate-900 flex-shrink-0 flex flex-col" id="sidebar">
    <!-- Logo -->
    <div class="p-5 border-b border-slate-800">
      <a href="<?= SITE_URL ?>" target="_blank" class="flex items-center gap-2">
        <div class="w-9 h-9 bg-gradient-to-br from-sky-500 to-teal-500 rounded-xl flex items-center justify-center">
          <i class="fas fa-paper-plane text-white text-sm"></i>
        </div>
        <span class="font-extrabold text-white text-sm leading-tight"><?= e($siteName) ?><br><span class="text-sky-400 font-normal text-xs">Admin Panel</span></span>
      </a>
    </div>

    <!-- Nav -->
    <nav class="flex-1 p-4 overflow-y-auto space-y-1">
      <a href="<?= SITE_URL ?>/admin/index.php" class="sidebar-link <?= $currentPage==='index.php'&&$currentDir==='admin'?'active':'' ?>">
        <i class="fas fa-th-large icon"></i> Dashboard
      </a>
      <div class="pt-3 pb-1"><p class="text-xs font-bold text-slate-600 uppercase tracking-wider px-4">Inventory</p></div>
      <a href="<?= SITE_URL ?>/admin/flights.php" class="sidebar-link <?= $currentPage==='flights.php'?'active':'' ?>">
        <i class="fas fa-plane icon"></i> Flights
      </a>
      <a href="<?= SITE_URL ?>/admin/hotels.php" class="sidebar-link <?= $currentPage==='hotels.php'?'active':'' ?>">
        <i class="fas fa-hotel icon"></i> Hotels
      </a>
      <div class="pt-3 pb-1"><p class="text-xs font-bold text-slate-600 uppercase tracking-wider px-4">Bookings</p></div>
      <a href="<?= SITE_URL ?>/admin/bookings.php" class="sidebar-link <?= $currentPage==='bookings.php'?'active':'' ?>">
        <i class="fas fa-ticket-alt icon"></i> All Bookings
      </a>
      <a href="<?= SITE_URL ?>/admin/bookings.php?payment=pending" class="sidebar-link">
        <i class="fas fa-clock icon"></i> Pending Payments
        <?php
        $pCount = db()->query('SELECT COUNT(*) FROM bookings b INNER JOIN payments p ON b.id=p.booking_id WHERE p.status="pending"')->fetchColumn();
        if ($pCount > 0): ?>
          <span class="ml-auto bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $pCount ?></span>
        <?php endif; ?>
      </a>
      <div class="pt-3 pb-1"><p class="text-xs font-bold text-slate-600 uppercase tracking-wider px-4">System</p></div>
      <a href="<?= SITE_URL ?>/admin/settings.php" class="sidebar-link <?= $currentPage==='settings.php'?'active':'' ?>">
        <i class="fas fa-cog icon"></i> Settings
      </a>
      <a href="<?= SITE_URL ?>" target="_blank" class="sidebar-link">
        <i class="fas fa-external-link-alt icon"></i> View Website
      </a>
    </nav>

    <!-- Admin User -->
    <div class="p-4 border-t border-slate-800">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-sky-600 rounded-xl flex items-center justify-center">
          <i class="fas fa-user-shield text-white text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-white text-sm font-semibold truncate"><?= e($_SESSION['admin_user'] ?? 'Admin') ?></p>
          <p class="text-slate-400 text-xs">Administrator</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="text-slate-400 hover:text-red-400 transition" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Bar -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-900"><?= $pageTitle ?? 'Dashboard' ?></h1>
      </div>
      <div class="flex items-center gap-4">
        <?php
        $pendingCount = db()->query('SELECT COUNT(*) FROM bookings WHERE payment_status="pending"')->fetchColumn();
        if ($pendingCount > 0): ?>
          <a href="<?= SITE_URL ?>/admin/bookings.php?payment=pending" class="flex items-center gap-2 bg-orange-50 text-orange-600 px-3 py-2 rounded-xl text-sm font-semibold hover:bg-orange-100">
            <i class="fas fa-bell"></i> <?= $pendingCount ?> pending
          </a>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="flex items-center gap-2 text-sm text-gray-500 hover:text-red-500">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </header>
    <main class="flex-1 overflow-y-auto p-6">
      <?php
      if (isset($_SESSION['flash'])) {
          $f = $_SESSION['flash']; unset($_SESSION['flash']);
          $colors = ['success'=>'bg-green-50 border-green-400 text-green-800','error'=>'bg-red-50 border-red-400 text-red-800','info'=>'bg-sky-50 border-sky-400 text-sky-800'];
          $icons  = ['success'=>'fa-check-circle','error'=>'fa-times-circle','info'=>'fa-info-circle'];
          $c = $colors[$f['type']] ?? $colors['info']; $ic = $icons[$f['type']] ?? $icons['info'];
          echo '<div class="border-l-4 p-4 rounded-lg mb-5 ' . $c . '"><i class="fas ' . $ic . ' mr-2"></i>' . htmlspecialchars($f['message']) . '</div>';
      }
      ?>
