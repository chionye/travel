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
/* Sidebar links */
.sidebar-link {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.75rem 1rem; border-radius: 0.75rem;
  color: #94a3b8; font-size: 0.875rem; font-weight: 500;
  text-decoration: none; transition: all 0.2s;
}
.sidebar-link:hover { background: #334155; color: #fff; }
.sidebar-link.active { background: #0284c7; color: #fff; box-shadow: 0 4px 12px rgba(2,132,199,0.3); }
.sidebar-link .icon { width: 1.25rem; text-align: center; }

/* Admin form inputs */
.admin-input {
  width: 100%; border: 1px solid #d1d5db; border-radius: 0.75rem;
  padding: 0.625rem 1rem; font-size: 0.875rem; background: #fff;
  outline: none; transition: all 0.2s; color: #111827;
}
.admin-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
.admin-label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }

/* Admin buttons */
.btn-admin {
  display: inline-flex; align-items: center; justify-content: center;
  background: #0284c7; color: #fff; font-weight: 600;
  border-radius: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.875rem;
  border: none; cursor: pointer; text-decoration: none; transition: all 0.2s;
}
.btn-admin:hover { background: #0369a1; }

.btn-danger {
  display: inline-flex; align-items: center; justify-content: center;
  background: #ef4444; color: #fff; font-weight: 600;
  border-radius: 0.75rem; padding: 0.5rem 1rem; font-size: 0.875rem;
  border: none; cursor: pointer; text-decoration: none; transition: all 0.2s;
}
.btn-danger:hover { background: #dc2626; }

.btn-success {
  display: inline-flex; align-items: center; justify-content: center;
  background: #22c55e; color: #fff; font-weight: 600;
  border-radius: 0.75rem; padding: 0.5rem 1rem; font-size: 0.875rem;
  border: none; cursor: pointer; text-decoration: none; transition: all 0.2s;
}
.btn-success:hover { background: #16a34a; }

.btn-gray {
  display: inline-flex; align-items: center; justify-content: center;
  background: #f3f4f6; color: #374151; font-weight: 600;
  border-radius: 0.75rem; padding: 0.5rem 1rem; font-size: 0.875rem;
  border: none; cursor: pointer; text-decoration: none; transition: all 0.2s;
}
.btn-gray:hover { background: #e5e7eb; }

/* Admin cards */
.card-admin {
  background: #fff; border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f3f4f6;
}

/* Admin table */
table.admin-table { width: 100%; font-size: 0.875rem; border-collapse: collapse; }
table.admin-table th {
  padding: 0.75rem 1rem; text-align: left;
  font-size: 0.75rem; font-weight: 700; color: #6b7280;
  text-transform: uppercase; letter-spacing: 0.05em; background: #f9fafb;
}
table.admin-table td { padding: 0.875rem 1rem; border-bottom: 1px solid #f9fafb; }
table.admin-table tr:last-child td { border-bottom: none; }
table.admin-table tbody tr:hover { background: rgba(240,249,255,0.4); }
</style>
</head>
<body class="bg-slate-100" style="font-family:ui-sans-serif,system-ui,sans-serif;">

<div class="flex h-screen overflow-hidden">
  <!-- Sidebar -->
  <aside class="w-64 bg-slate-900 flex-shrink-0 flex flex-col" id="sidebar">
    <!-- Logo -->
    <div class="p-5 border-b border-slate-800">
      <a href="<?= SITE_URL ?>" target="_blank" class="flex items-center gap-2" style="text-decoration:none">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#0ea5e9,#14b8a6)">
          <i class="fas fa-paper-plane text-white" style="font-size:14px"></i>
        </div>
        <span style="font-weight:800;color:#fff;font-size:0.875rem;line-height:1.2">
          <?= e($siteName) ?><br>
          <span style="color:#38bdf8;font-weight:400;font-size:0.75rem">Admin Panel</span>
        </span>
      </a>
    </div>

    <!-- Nav -->
    <nav class="flex-1 p-4 overflow-y-auto" style="display:flex;flex-direction:column;gap:2px">
      <a href="<?= SITE_URL ?>/admin/index.php" class="sidebar-link <?= $currentPage==='index.php'&&$currentDir==='admin'?'active':'' ?>">
        <i class="fas fa-th-large icon"></i> Dashboard
      </a>

      <p style="font-size:0.65rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.1em;padding:0.75rem 1rem 0.25rem">Inventory</p>
      <a href="<?= SITE_URL ?>/admin/flights.php" class="sidebar-link <?= $currentPage==='flights.php'?'active':'' ?>">
        <i class="fas fa-plane icon"></i> Flights
      </a>
      <a href="<?= SITE_URL ?>/admin/hotels.php" class="sidebar-link <?= $currentPage==='hotels.php'?'active':'' ?>">
        <i class="fas fa-hotel icon"></i> Hotels
      </a>

      <p style="font-size:0.65rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.1em;padding:0.75rem 1rem 0.25rem">Bookings</p>
      <a href="<?= SITE_URL ?>/admin/bookings.php" class="sidebar-link <?= $currentPage==='bookings.php'?'active':'' ?>">
        <i class="fas fa-ticket-alt icon"></i> All Bookings
      </a>
      <a href="<?= SITE_URL ?>/admin/bookings.php?payment=pending" class="sidebar-link" style="position:relative">
        <i class="fas fa-clock icon"></i> Pending Payments
        <?php
        $pCount = db()->query('SELECT COUNT(*) FROM bookings b INNER JOIN payments p ON b.id=p.booking_id WHERE p.status="pending"')->fetchColumn();
        if ($pCount > 0): ?>
          <span style="margin-left:auto;background:#f97316;color:#fff;font-size:0.7rem;font-weight:700;padding:2px 7px;border-radius:9999px"><?= $pCount ?></span>
        <?php endif; ?>
      </a>

      <p style="font-size:0.65rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.1em;padding:0.75rem 1rem 0.25rem">System</p>
      <a href="<?= SITE_URL ?>/admin/settings.php" class="sidebar-link <?= $currentPage==='settings.php'?'active':'' ?>">
        <i class="fas fa-cog icon"></i> Settings
      </a>
      <a href="<?= SITE_URL ?>" target="_blank" class="sidebar-link">
        <i class="fas fa-external-link-alt icon"></i> View Website
      </a>
    </nav>

    <!-- Admin user -->
    <div class="p-4 border-t border-slate-800">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#0284c7">
          <i class="fas fa-user-shield text-white" style="font-size:13px"></i>
        </div>
        <div style="flex:1;min-width:0">
          <p style="color:#fff;font-size:0.875rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($_SESSION['admin_user'] ?? 'Admin') ?></p>
          <p style="color:#64748b;font-size:0.75rem">Administrator</p>
        </div>
        <a href="<?= SITE_URL ?>/admin/logout.php" style="color:#64748b;transition:color 0.2s" title="Logout" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#64748b'">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- Main content area -->
  <div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <h1 style="font-size:1.25rem;font-weight:700;color:#111827;margin:0"><?= $pageTitle ?? 'Dashboard' ?></h1>
      <div class="flex items-center gap-4">
        <?php
        $pendingCount = db()->query('SELECT COUNT(*) FROM bookings WHERE payment_status="pending"')->fetchColumn();
        if ($pendingCount > 0): ?>
          <a href="<?= SITE_URL ?>/admin/bookings.php?payment=pending"
             style="display:flex;align-items:center;gap:6px;background:#fff7ed;color:#c2410c;padding:6px 14px;border-radius:10px;font-size:0.8rem;font-weight:600;text-decoration:none">
            <i class="fas fa-bell"></i> <?= $pendingCount ?> pending
          </a>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/admin/logout.php" style="font-size:0.875rem;color:#6b7280;text-decoration:none" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#6b7280'">
          <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </a>
      </div>
    </header>

    <!-- Page content -->
    <main class="flex-1 overflow-y-auto p-6">
      <?php
      if (isset($_SESSION['flash'])) {
          $f = $_SESSION['flash']; unset($_SESSION['flash']);
          $colorMap = [
            'success' => 'background:#f0fdf4;border-color:#4ade80;color:#166534',
            'error'   => 'background:#fef2f2;border-color:#f87171;color:#991b1b',
            'info'    => 'background:#f0f9ff;border-color:#38bdf8;color:#0c4a6e',
          ];
          $iconMap = ['success'=>'fa-check-circle','error'=>'fa-times-circle','info'=>'fa-info-circle'];
          $style = $colorMap[$f['type']] ?? $colorMap['info'];
          $icon  = $iconMap[$f['type']] ?? 'fa-info-circle';
          echo '<div style="border-left:4px solid;padding:1rem;border-radius:0.5rem;margin-bottom:1.25rem;' . $style . '"><i class="fas ' . $icon . ' mr-2"></i>' . htmlspecialchars($f['message']) . '</div>';
      }
      ?>
