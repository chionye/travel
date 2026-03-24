<?php
require_once dirname(__DIR__) . '/includes/auth.php';
if (isAdminLoggedIn()) { header('Location: ' . SITE_URL . '/admin/index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (loginAdmin($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: ' . SITE_URL . '/admin/index.php'); exit;
    }
    $error = 'Invalid username or password.';
}
$siteName = getSetting('site_name','SkyWave Travel');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login &mdash; <?= e($siteName) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-8">
      <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-shield-alt text-white text-2xl"></i>
      </div>
      <h1 class="text-2xl font-extrabold text-white"><?= e($siteName) ?></h1>
      <p class="text-slate-400 mt-1">Admin Portal</p>
    </div>
    <div class="bg-white rounded-3xl shadow-2xl p-8">
      <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl mb-5 text-sm"><?= e($error) ?></div>
      <?php endif; ?>
      <form method="post">
        <?= csrfField() ?>
        <div class="mb-4">
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Username</label>
          <div class="relative">
            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="username" class="w-full border border-gray-300 rounded-xl px-4 py-3 pl-11 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required autofocus>
          </div>
        </div>
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
          <div class="relative">
            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="password" name="password" id="admin-pass" class="w-full border border-gray-300 rounded-xl px-4 py-3 pl-11 pr-11 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required>
            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" onclick="var f=document.getElementById('admin-pass');f.type=f.type==='password'?'text':'password';"><i class="fas fa-eye"></i></button>
          </div>
        </div>
        <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-xl transition">
          <i class="fas fa-sign-in-alt mr-2"></i>Sign In
        </button>
      </form>
    </div>
    <p class="text-center text-slate-500 text-sm mt-6"><a href="<?= SITE_URL ?>" class="hover:text-sky-400">&larr; Back to Website</a></p>
  </div>
</body>
</html>
