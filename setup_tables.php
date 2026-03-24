<?php
/**
 * SkyWave Travel - Table Setup Script
 * Use this if you already configured config.php manually
 * and just need to create the database tables.
 *
 * VISIT: yoursite.com/setup_tables.php
 * DELETE this file after running it.
 */

$rootDir    = __DIR__;
$configFile = $rootDir . '/includes/config.php';
$error      = '';
$success    = '';
$done       = false;

// Load config if it exists
$configLoaded = false;
if (file_exists($configFile)) {
    require_once $configFile;
    $configLoaded = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If config not loaded, use posted values
    $dbHost  = $configLoaded ? DB_HOST : trim($_POST['db_host'] ?? 'localhost');
    $dbName  = $configLoaded ? DB_NAME : trim($_POST['db_name'] ?? '');
    $dbUser  = $configLoaded ? DB_USER : trim($_POST['db_user'] ?? '');
    $dbPass  = $configLoaded ? DB_PASS : ($_POST['db_pass'] ?? '');

    $adminUser  = trim($_POST['admin_user'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass  = $_POST['admin_pass'] ?? '';
    $adminPass2 = $_POST['admin_pass2'] ?? '';

    if (!$dbName || !$dbUser) {
        $error = 'Database name and username are required.';
    } elseif (strlen($adminPass) < 6) {
        $error = 'Admin password must be at least 6 characters.';
    } elseif ($adminPass !== $adminPass2) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Create tables
            $tables = [
"CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `flights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `airline` varchar(100) NOT NULL,
  `airline_code` varchar(10) DEFAULT NULL,
  `flight_number` varchar(20) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `origin_code` varchar(10) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `destination_code` varchar(10) NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `class` enum('economy','business','first') NOT NULL DEFAULT 'economy',
  `price` decimal(10,2) NOT NULL,
  `available_seats` int(11) NOT NULL DEFAULT 100,
  `baggage` varchar(100) DEFAULT '20kg',
  `stops` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `hotels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `star_rating` int(11) NOT NULL DEFAULT 3,
  `price_per_night` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_ref` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_type` enum('flight','hotel','both') NOT NULL,
  `flight_id` int(11) DEFAULT NULL,
  `hotel_id` int(11) DEFAULT NULL,
  `check_in` date DEFAULT NULL,
  `check_out` date DEFAULT NULL,
  `nights` int(11) DEFAULT NULL,
  `adults` int(11) NOT NULL DEFAULT 1,
  `children` int(11) NOT NULL DEFAULT 0,
  `passenger_details` text DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `booking_status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','pending','approved','declined') NOT NULL DEFAULT 'unpaid',
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_ref` (`booking_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `payment_method` enum('crypto','bank') NOT NULL,
  `crypto_type` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `proof_image` varchar(500) DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            ];

            foreach ($tables as $sql) {
                $pdo->exec($sql);
            }

            // Insert default settings
            $defaults = [
                'site_name','site_logo','contact_email','contact_phone','contact_address',
                'currency','currency_symbol','btc_wallet','eth_wallet','usdt_wallet',
                'usdt_network','bank_name','bank_account_name','bank_account_number',
                'bank_routing','bank_swift','bank_additional','smtp_from',
                'social_facebook','social_instagram','social_twitter',
            ];
            $defaultVals = ['site_name'=>'SkyWave Travel','currency'=>'USD','currency_symbol'=>'$','usdt_network'=>'TRC20'];
            $ins = $pdo->prepare('INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?,?)');
            foreach ($defaults as $k) {
                $ins->execute([$k, $defaultVals[$k] ?? '']);
            }

            // Update site_url in settings if SITE_URL is defined
            if ($configLoaded && defined('SITE_URL')) {
                $pdo->prepare('INSERT INTO settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?')
                    ->execute(['site_url', SITE_URL, SITE_URL]);
            }

            // Create admin account
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO admins (username,password,email) VALUES (?,?,?) ON DUPLICATE KEY UPDATE password=VALUES(password),email=VALUES(email)')
                ->execute([$adminUser, $hash, $adminEmail]);

            $success = "All tables created and admin account set up successfully!";
            $done    = true;

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SkyWave - Table Setup</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-900 to-teal-700 flex items-center justify-center p-4">
<div class="w-full max-w-lg">
  <div class="text-center mb-8">
    <div class="text-4xl mb-2">&#9992;</div>
    <h1 class="text-3xl font-extrabold text-white">SkyWave Travel</h1>
    <p class="text-sky-200 mt-1">Database Table Setup</p>
  </div>

  <div class="bg-white rounded-2xl shadow-2xl p-8">

    <?php if ($done): ?>
      <div class="text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-check-circle text-4xl text-green-500"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Setup Complete!</h2>
        <p class="text-gray-500 mb-2">All database tables have been created.</p>
        <p class="text-gray-500 mb-6">Admin username: <strong><?= htmlspecialchars($_POST['admin_user'] ?? 'admin') ?></strong></p>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
          <p class="text-red-700 font-semibold text-sm"><i class="fas fa-exclamation-triangle mr-2"></i>Important</p>
          <p class="text-red-600 text-sm mt-1">Delete <code class="bg-red-100 px-1 rounded">setup_tables.php</code> from your server now for security.</p>
        </div>
        <div class="flex gap-3 justify-center">
          <a href="/" class="bg-sky-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-sky-700"><i class="fas fa-home mr-2"></i>Website</a>
          <a href="/admin/" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-900"><i class="fas fa-cog mr-2"></i>Admin Panel</a>
        </div>
      </div>

    <?php else: ?>
      <h2 class="text-xl font-bold text-gray-800 mb-2">Create Database Tables</h2>

      <?php if ($configLoaded): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-5 text-sm">
          <i class="fas fa-check-circle text-green-600 mr-2"></i>
          <strong>config.php detected</strong> &mdash; using existing DB settings
          (<code><?= defined('DB_HOST') ? htmlspecialchars(DB_HOST) : '' ?></code> /
           <code><?= defined('DB_NAME') ? htmlspecialchars(DB_NAME) : '' ?></code>)
        </div>
      <?php else: ?>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-5 text-sm">
          <i class="fas fa-exclamation-triangle text-amber-600 mr-2"></i>
          config.php not found &mdash; please enter your database details below.
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg mb-4 text-sm">
          <i class="fas fa-times-circle mr-2"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <?php if (!$configLoaded): ?>
          <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Database</p>
          <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
              <input name="db_host" value="localhost" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
              <input name="db_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
              <input name="db_user" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input type="password" name="db_pass" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400">
            </div>
          </div>
        <?php endif; ?>

        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Admin Account</p>
        <div class="grid grid-cols-2 gap-3 mb-2">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input name="admin_user" value="admin" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-gray-400">(optional)</span></label>
            <input type="email" name="admin_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="admin_pass" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400" required minlength="6">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input type="password" name="admin_pass2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-400" required>
          </div>
        </div>
        <p class="text-xs text-gray-400 mb-4">If an admin account with this username already exists, its password will be updated.</p>

        <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3 rounded-xl font-bold transition">
          <i class="fas fa-database mr-2"></i> Create Tables &amp; Admin Account
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
