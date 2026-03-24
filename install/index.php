<?php
/**
 * SkyWave Travel - Installation Wizard
 * Run this once to set up your database and configuration.
 */

$step    = (int)($_GET['step'] ?? 1);
$rootDir = dirname(__DIR__);
$configFile = $rootDir . '/includes/config.php';

// If already installed and not forcing reinstall
if (file_exists($configFile) && !isset($_GET['force'])) {
    $step = 0; // show already installed
}

$error   = '';
$success = '';

// Step 2: Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $dbHost   = trim($_POST['db_host'] ?? 'localhost');
    $dbName   = trim($_POST['db_name'] ?? '');
    $dbUser   = trim($_POST['db_user'] ?? '');
    $dbPass   = $_POST['db_pass'] ?? '';
    $siteUrl  = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminPass2 = $_POST['admin_pass2'] ?? '';

    if (!$dbName || !$dbUser) {
        $error = 'Database name and username are required.';
    } elseif (strlen($adminPass) < 6) {
        $error = 'Admin password must be at least 6 characters.';
    } elseif ($adminPass !== $adminPass2) {
        $error = 'Passwords do not match.';
    } else {
        // Test connection
        try {
            $dsn = "mysql:host={$dbHost};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            // Create DB if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`','', $dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . str_replace('`','', $dbName) . "`");
            // Run schema
            $sql = file_get_contents(__DIR__ . '/schema.sql');
            // Split by semicolon and run each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if ($stmt) $pdo->exec($stmt);
            }
            // Create admin
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare('INSERT INTO admins (username, password, email) VALUES (?,?,?) ON DUPLICATE KEY UPDATE password=?, email=?');
            $ins->execute([$adminUser, $hash, $adminEmail, $hash, $adminEmail]);
            // Update site_url
            $upd = $pdo->prepare('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?');
            $upd->execute(['site_url', $siteUrl, $siteUrl]);

            // Write config.php
            $configContent = "<?php\n// SkyWave Travel - Configuration\n// Generated: " . date('Y-m-d H:i:s') . "\n\ndefine('DB_HOST',    '" . addslashes($dbHost) . "');\ndefine('DB_NAME',    '" . addslashes($dbName) . "');\ndefine('DB_USER',    '" . addslashes($dbUser) . "');\ndefine('DB_PASS',    '" . addslashes($dbPass) . "');\ndefine('DB_CHARSET', 'utf8mb4');\n\ndefine('SITE_URL',    '" . addslashes($siteUrl) . "');\ndefine('ROOT_PATH',   dirname(__DIR__) . '/');\ndefine('UPLOAD_PATH', ROOT_PATH . 'uploads/');\ndefine('UPLOAD_URL',  SITE_URL . '/uploads/');\n\ndefine('SESSION_NAME', 'skywave_sess');\ndefine('APP_VERSION',  '1.0.0');\n?>\n";

            if (file_put_contents($configFile, $configContent) === false) {
                $error = 'Could not write config.php. Please check folder permissions (chmod 755 on includes/).';
            } else {
                header('Location: index.php?step=3');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

if ($step === 2 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $step = 2;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SkyWave Travel - Installation Wizard</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-900 via-sky-800 to-teal-700 flex items-center justify-center p-4">
<div class="w-full max-w-lg">
  <!-- Logo -->
  <div class="text-center mb-8">
    <div class="text-4xl mb-2">&#9992;</div>
    <h1 class="text-3xl font-extrabold text-white">SkyWave Travel</h1>
    <p class="text-sky-200 mt-1">Installation Wizard</p>
  </div>

  <!-- Steps -->
  <div class="flex items-center justify-center gap-2 mb-8">
    <?php foreach ([1=>'Requirements',2=>'Database',3=>'Complete'] as $n => $label): ?>
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold <?= ($step>=$n)?'bg-white text-sky-800':'bg-sky-700 text-sky-300' ?>"><?= $n ?></div>
        <span class="text-sm <?= ($step>=$n)?'text-white':'text-sky-400' ?> hidden sm:inline"><?= $label ?></span>
      </div>
      <?php if ($n < 3): ?><div class="w-8 h-px bg-sky-600"></div><?php endif; ?>
    <?php endforeach; ?>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl p-8">

  <?php if ($step === 0): ?>
    <div class="text-center">
      <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-check-circle text-3xl text-green-500"></i>
      </div>
      <h2 class="text-xl font-bold text-gray-800 mb-2">Already Installed</h2>
      <p class="text-gray-500 mb-6">SkyWave Travel is already configured.</p>
      <div class="flex gap-3 justify-center">
        <a href="../" class="bg-sky-600 text-white px-6 py-2 rounded-lg hover:bg-sky-700">Go to Website</a>
        <a href="../admin/" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900">Admin Panel</a>
        <a href="?force=1&step=2" class="text-red-500 text-sm underline mt-3 block">Reinstall</a>
      </div>
    </div>

  <?php elseif ($step === 1): ?>
    <h2 class="text-xl font-bold text-gray-800 mb-6">&#128270; System Requirements</h2>
    <?php
    $checks = [
        'PHP 7.4+' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'File Uploads' => ini_get('file_uploads'),
        'includes/ writable' => is_writable($rootDir . '/includes'),
        'uploads/ writable' => is_writable($rootDir . '/uploads') || !is_dir($rootDir . '/uploads'),
    ];
    $allOk = !in_array(false, $checks, true);
    foreach ($checks as $label => $ok): ?>
      <div class="flex items-center justify-between py-3 border-b last:border-0">
        <span class="text-gray-700"><?= $label ?></span>
        <?php if ($ok): ?>
          <span class="flex items-center gap-1 text-green-600 font-medium"><i class="fas fa-check-circle"></i> OK</span>
        <?php else: ?>
          <span class="flex items-center gap-1 text-red-600 font-medium"><i class="fas fa-times-circle"></i> Failed</span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$allOk): ?>
      <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">Please fix the failed requirements before continuing.</div>
    <?php endif; ?>
    <div class="mt-6 text-right">
      <a href="?step=2" class="<?= $allOk?'bg-sky-600 hover:bg-sky-700':'bg-gray-300 pointer-events-none' ?> text-white px-6 py-3 rounded-xl font-semibold inline-block">
        Continue <i class="fas fa-arrow-right ml-1"></i>
      </a>
    </div>

  <?php elseif ($step === 2): ?>
    <h2 class="text-xl font-bold text-gray-800 mb-6">&#128203; Configuration</h2>
    <?php if ($error): ?>
      <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg mb-4 text-sm"><i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="?step=2">
      <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Database</p>
      <div class="grid grid-cols-2 gap-3 mb-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">DB Host</label>
          <input name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
          <input name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">DB Username</label>
          <input name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">DB Password</label>
          <input type="password" name="db_pass" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
        </div>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Site URL <span class="text-gray-400">(no trailing slash)</span></label>
        <input name="site_url" value="<?= htmlspecialchars($_POST['site_url'] ?? 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required>
      </div>
      <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 mt-5">Admin Account</p>
      <div class="grid grid-cols-2 gap-3 mb-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
          <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" name="admin_pass" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required minlength="6">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <input type="password" name="admin_pass2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none" required>
        </div>
      </div>
      <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3 rounded-xl font-bold mt-4 transition">
        <i class="fas fa-rocket mr-2"></i> Install SkyWave Travel
      </button>
    </form>

  <?php elseif ($step === 3): ?>
    <div class="text-center">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-check-circle text-4xl text-green-500"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Installation Complete!</h2>
      <p class="text-gray-500 mb-6">SkyWave Travel has been successfully installed.</p>
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 text-left">
        <p class="text-amber-800 font-semibold text-sm"><i class="fas fa-shield-alt mr-2"></i>Security Notice</p>
        <p class="text-amber-700 text-sm mt-1">For security, please delete or rename the <code class="bg-amber-100 px-1 rounded">install/</code> folder after setup.</p>
      </div>
      <div class="flex gap-3 justify-center flex-wrap">
        <a href="../" class="bg-sky-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-sky-700"><i class="fas fa-home mr-2"></i>Visit Website</a>
        <a href="../admin/" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-900"><i class="fas fa-cog mr-2"></i>Admin Panel</a>
      </div>
    </div>
  <?php endif; ?>

  </div>
  <p class="text-center text-sky-300 text-sm mt-6">SkyWave Travel &copy; <?= date('Y') ?></p>
</div>
</body>
</html>
