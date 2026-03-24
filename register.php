<?php
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$pageTitle = 'Create Account';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'password'   => $_POST['password'] ?? '',
    ];
    if (strlen($data['password']) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($data['password'] !== ($_POST['password2'] ?? '')) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($data);
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            loginUser($data['email'], $data['password']);
            flash('success', 'Welcome! Your account has been created.');
            header('Location: dashboard.php'); exit;
        }
    }
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
      <h1 class="text-3xl font-extrabold text-gray-900">Create your account</h1>
      <p class="text-gray-500 mt-2">Start booking your dream trips today</p>
    </div>
    <div class="bg-white rounded-3xl shadow-xl p-8">
      <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5 flex items-center gap-2">
          <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
      <?php endif; ?>
      <form method="post">
        <?= csrfField() ?>
        <div class="grid grid-cols-2 gap-4">
          <div class="form-group">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" class="form-input" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="relative">
            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" class="form-input pl-11" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <div class="relative">
            <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" class="form-input pl-11">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="relative">
            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="password" name="password" class="form-input pl-11" required minlength="6" placeholder="Min. 6 characters">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <div class="relative">
            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="password" name="password2" class="form-input pl-11" required>
          </div>
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-4 text-base mt-2">
          <i class="fas fa-user-plus mr-2"></i>Create Account
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-6">
        Already have an account? <a href="login.php" class="text-sky-600 font-semibold hover:text-sky-700">Sign in</a>
      </p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
