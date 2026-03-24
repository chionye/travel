<?php
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$pageTitle = 'Login';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (loginUser($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect); exit;
    }
    $error = 'Invalid email or password. Please try again.';
}
?>
<?php include 'includes/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-sky-50 to-teal-50 flex items-center justify-center py-12 px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <a href="<?= SITE_URL ?>" class="inline-flex items-center gap-2 mb-4">
        <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-teal-500 rounded-2xl flex items-center justify-center">
          <i class="fas fa-paper-plane text-white text-xl"></i>
        </div>
        <span class="font-extrabold text-2xl text-sky-700"><?= e(getSetting('site_name','SkyWave Travel')) ?></span>
      </a>
      <h1 class="text-3xl font-extrabold text-gray-900">Welcome back!</h1>
      <p class="text-gray-500 mt-2">Sign in to manage your bookings</p>
    </div>
    <div class="bg-white rounded-3xl shadow-xl p-8">
      <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5 flex items-center gap-2">
          <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
      <?php endif; ?>
      <?= renderFlash() ?>
      <form method="post">
        <?= csrfField() ?>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="relative">
            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" class="form-input pl-11" placeholder="you@example.com" required autofocus>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="relative">
            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="password" name="password" class="form-input pl-11 pr-11" placeholder="Your password" required id="pass-field">
            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="var f=document.getElementById('pass-field');f.type=f.type==='password'?'text':'password';">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-4 text-base mt-2">
          <i class="fas fa-sign-in-alt mr-2"></i>Sign In
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-6">
        Don't have an account? <a href="register.php" class="text-sky-600 font-semibold hover:text-sky-700">Sign up free</a>
      </p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
