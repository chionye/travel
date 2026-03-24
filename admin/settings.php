<?php
$pageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';

$tab = $_GET['tab'] ?? 'site';
$tabs = [
    'site'    => ['icon'=>'fa-globe',        'label'=>'Site Info'],
    'payment' => ['icon'=>'fa-credit-card',  'label'=>'Payment Methods'],
    'admin'   => ['icon'=>'fa-user-shield',  'label'=>'Admin Account'],
];

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $postTab = $_POST['tab'] ?? $tab;

    if ($postTab === 'site') {
        $fields = ['site_name','contact_email','contact_phone','contact_address','site_url','currency','currency_symbol','social_facebook','social_instagram','social_twitter'];
        foreach ($fields as $k) { updateSetting($k, trim($_POST[$k] ?? '')); }
        // Logo upload
        if (!empty($_FILES['site_logo']['name'])) {
            $up = uploadFile($_FILES['site_logo'], 'logos');
            if (isset($up['error'])) { $error = $up['error']; }
            else { updateSetting('site_logo', $up['path']); }
        }
        if (!$error) { flash('success', 'Site settings saved.'); header('Location: settings.php?tab=site'); exit; }
    }

    if ($postTab === 'payment') {
        $fields = ['btc_wallet','eth_wallet','usdt_wallet','usdt_network','bank_name','bank_account_name','bank_account_number','bank_routing','bank_swift','bank_additional','smtp_from'];
        foreach ($fields as $k) { updateSetting($k, trim($_POST[$k] ?? '')); }
        flash('success', 'Payment settings saved.'); header('Location: settings.php?tab=payment'); exit;
    }

    if ($postTab === 'admin') {
        $newUser = trim($_POST['admin_username'] ?? '');
        $newPass = $_POST['admin_password'] ?? '';
        $newPass2 = $_POST['admin_password2'] ?? '';
        $currentPass = $_POST['current_password'] ?? '';
        $adminId = $_SESSION['admin_id'];

        // Verify current password
        $stmt = db()->prepare('SELECT * FROM admins WHERE id=?'); $stmt->execute([$adminId]); $admin = $stmt->fetch();
        if (!password_verify($currentPass, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (!$newUser) {
            $error = 'Username cannot be empty.';
        } elseif ($newPass && strlen($newPass) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($newPass && $newPass !== $newPass2) {
            $error = 'New passwords do not match.';
        } else {
            if ($newPass) {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                db()->prepare('UPDATE admins SET username=?, password=?, email=? WHERE id=?')->execute([$newUser, $hash, trim($_POST['admin_email']??''), $adminId]);
            } else {
                db()->prepare('UPDATE admins SET username=?, email=? WHERE id=?')->execute([$newUser, trim($_POST['admin_email']??''), $adminId]);
            }
            $_SESSION['admin_user'] = $newUser;
            flash('success', 'Admin account updated.'); header('Location: settings.php?tab=admin'); exit;
        }
    }

    $tab = $postTab;
}

$settings = getAllSettings();
$adminId  = $_SESSION['admin_id'];
$stmt     = db()->prepare('SELECT * FROM admins WHERE id=?'); $stmt->execute([$adminId]); $adminRow = $stmt->fetch();
?>

<!-- Tab Nav -->
<div class="flex gap-2 mb-6 border-b border-gray-200">
  <?php foreach ($tabs as $key=>$t): ?>
    <a href="?tab=<?= $key ?>" class="flex items-center gap-2 px-5 py-3 text-sm font-semibold border-b-2 transition <?= $tab===$key?'border-sky-600 text-sky-600':'border-transparent text-gray-500 hover:text-gray-700' ?>">
      <i class="fas <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if ($error): ?><div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5"><?= e($error) ?></div><?php endif; ?>

<!-- Site Info -->
<?php if ($tab === 'site'): ?>
<form method="post" enctype="multipart/form-data" class="space-y-6">
  <?= csrfField() ?>
  <input type="hidden" name="tab" value="site">

  <div class="card-admin p-6">
    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="fas fa-globe text-sky-500"></i> Website Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div>
        <label class="admin-label">Website Name <span class="text-red-500">*</span></label>
        <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>" class="admin-input" required>
      </div>
      <div>
        <label class="admin-label">Website URL</label>
        <input type="text" name="site_url" value="<?= e($settings['site_url'] ?? SITE_URL) ?>" class="admin-input" placeholder="https://yoursite.com">
      </div>
      <div>
        <label class="admin-label">Contact Email(s)</label>
        <input type="text" name="contact_email" value="<?= e($settings['contact_email'] ?? '') ?>" class="admin-input" placeholder="info@site.com, support@site.com">
        <p class="text-xs text-gray-400 mt-1">Separate multiple emails with commas</p>
      </div>
      <div>
        <label class="admin-label">Contact Phone(s)</label>
        <input type="text" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>" class="admin-input" placeholder="+1 555-000-0000">
      </div>
      <div class="md:col-span-2">
        <label class="admin-label">Office Address</label>
        <input type="text" name="contact_address" value="<?= e($settings['contact_address'] ?? '') ?>" class="admin-input">
      </div>
      <div>
        <label class="admin-label">Currency Code</label>
        <input type="text" name="currency" value="<?= e($settings['currency'] ?? 'USD') ?>" class="admin-input" maxlength="5" placeholder="USD">
      </div>
      <div>
        <label class="admin-label">Currency Symbol</label>
        <input type="text" name="currency_symbol" value="<?= e($settings['currency_symbol'] ?? '$') ?>" class="admin-input" maxlength="5" placeholder="$">
      </div>
    </div>
  </div>

  <div class="card-admin p-6">
    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="fas fa-image text-sky-500"></i> Logo</h3>
    <?php if (!empty($settings['site_logo'])): ?>
      <div class="mb-4">
        <img src="<?= e(UPLOAD_URL . $settings['site_logo']) ?>" class="h-16 w-auto" alt="Current Logo">
        <p class="text-xs text-gray-400 mt-1">Current logo</p>
      </div>
    <?php endif; ?>
    <label class="admin-label">Upload New Logo</label>
    <input type="file" name="site_logo" accept="image/*" class="admin-input py-2">
    <p class="text-xs text-gray-400 mt-1">Recommended: PNG or SVG with transparent background. Max 2MB.</p>
  </div>

  <div class="card-admin p-6">
    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="fas fa-share-alt text-sky-500"></i> Social Media</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div>
        <label class="admin-label"><i class="fab fa-facebook text-blue-600 mr-1"></i> Facebook URL</label>
        <input type="url" name="social_facebook" value="<?= e($settings['social_facebook'] ?? '') ?>" class="admin-input">
      </div>
      <div>
        <label class="admin-label"><i class="fab fa-instagram text-pink-500 mr-1"></i> Instagram URL</label>
        <input type="url" name="social_instagram" value="<?= e($settings['social_instagram'] ?? '') ?>" class="admin-input">
      </div>
      <div>
        <label class="admin-label"><i class="fab fa-twitter text-sky-500 mr-1"></i> Twitter/X URL</label>
        <input type="url" name="social_twitter" value="<?= e($settings['social_twitter'] ?? '') ?>" class="admin-input">
      </div>
    </div>
  </div>

  <button type="submit" class="btn-admin"><i class="fas fa-save mr-2"></i>Save Site Settings</button>
</form>

<!-- Payment Methods -->
<?php elseif ($tab === 'payment'): ?>
<form method="post" class="space-y-6">
  <?= csrfField() ?>
  <input type="hidden" name="tab" value="payment">

  <!-- Crypto -->
  <div class="card-admin p-6">
    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="fab fa-bitcoin text-amber-500 text-xl"></i> Cryptocurrency Wallets</h3>
    <p class="text-sm text-gray-500 mb-5">Enter your crypto wallet addresses. Leave blank to hide that option from users.</p>
    <div class="space-y-5">
      <div>
        <label class="admin-label"><i class="fab fa-bitcoin text-amber-500 mr-2"></i>Bitcoin (BTC) Wallet Address</label>
        <input type="text" name="btc_wallet" value="<?= e($settings['btc_wallet'] ?? '') ?>" class="admin-input font-mono" placeholder="bc1q...">
      </div>
      <div>
        <label class="admin-label"><i class="fab fa-ethereum text-purple-500 mr-2"></i>Ethereum (ETH) Wallet Address</label>
        <input type="text" name="eth_wallet" value="<?= e($settings['eth_wallet'] ?? '') ?>" class="admin-input font-mono" placeholder="0x...">
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="admin-label"><i class="fas fa-dollar-sign text-green-500 mr-2"></i>USDT Wallet Address</label>
          <input type="text" name="usdt_wallet" value="<?= e($settings['usdt_wallet'] ?? '') ?>" class="admin-input font-mono" placeholder="T...">
        </div>
        <div>
          <label class="admin-label">USDT Network</label>
          <select name="usdt_network" class="admin-input">
            <?php foreach (['TRC20','ERC20','BEP20','Polygon'] as $net): ?>
              <option <?= ($settings['usdt_network']??'TRC20')===$net?'selected':'' ?>><?= $net ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Bank Transfer -->
  <div class="card-admin p-6">
    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="fas fa-university text-sky-600 text-xl"></i> Bank Transfer Details</h3>
    <p class="text-sm text-gray-500 mb-5">These details are shown to users who choose bank transfer. Leave blank to hide bank transfer option.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div>
        <label class="admin-label">Bank Name</label>
        <input type="text" name="bank_name" value="<?= e($settings['bank_name'] ?? '') ?>" class="admin-input" placeholder="Chase Bank">
      </div>
      <div>
        <label class="admin-label">Account Holder Name</label>
        <input type="text" name="bank_account_name" value="<?= e($settings['bank_account_name'] ?? '') ?>" class="admin-input">
      </div>
      <div>
        <label class="admin-label">Account Number / IBAN</label>
        <input type="text" name="bank_account_number" value="<?= e($settings['bank_account_number'] ?? '') ?>" class="admin-input font-mono">
      </div>
      <div>
        <label class="admin-label">Routing / Sort Code</label>
        <input type="text" name="bank_routing" value="<?= e($settings['bank_routing'] ?? '') ?>" class="admin-input font-mono">
      </div>
      <div>
        <label class="admin-label">SWIFT / BIC Code</label>
        <input type="text" name="bank_swift" value="<?= e($settings['bank_swift'] ?? '') ?>" class="admin-input font-mono">
      </div>
      <div>
        <label class="admin-label">Additional Info</label>
        <input type="text" name="bank_additional" value="<?= e($settings['bank_additional'] ?? '') ?>" class="admin-input" placeholder="e.g. Reference your booking number">
      </div>
    </div>
  </div>

  <!-- Email -->
  <div class="card-admin p-6">
    <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="fas fa-envelope text-sky-500"></i> Email Settings</h3>
    <p class="text-sm text-gray-500 mb-4">Emails are sent via PHP <code>mail()</code>. Set your sender address below.</p>
    <div>
      <label class="admin-label">From Email Address</label>
      <input type="email" name="smtp_from" value="<?= e($settings['smtp_from'] ?? '') ?>" class="admin-input" placeholder="noreply@yoursite.com">
    </div>
  </div>

  <button type="submit" class="btn-admin"><i class="fas fa-save mr-2"></i>Save Payment Settings</button>
</form>

<!-- Admin Account -->
<?php elseif ($tab === 'admin'): ?>
<form method="post" class="card-admin p-6">
  <?= csrfField() ?>
  <input type="hidden" name="tab" value="admin">
  <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="fas fa-user-shield text-sky-500"></i> Change Admin Credentials</h3>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
      <label class="admin-label">Username <span class="text-red-500">*</span></label>
      <input type="text" name="admin_username" value="<?= e($adminRow['username'] ?? '') ?>" class="admin-input" required>
    </div>
    <div>
      <label class="admin-label">Admin Email</label>
      <input type="email" name="admin_email" value="<?= e($adminRow['email'] ?? '') ?>" class="admin-input">
    </div>
    <div class="md:col-span-2 border-t border-gray-100 pt-5 mt-2">
      <p class="text-sm font-semibold text-gray-700 mb-4">Change Password <span class="text-gray-400 font-normal">(leave new password blank to keep current)</span></p>
    </div>
    <div class="md:col-span-2">
      <label class="admin-label">Current Password <span class="text-red-500">*</span></label>
      <input type="password" name="current_password" class="admin-input" required placeholder="Enter your current password">
    </div>
    <div>
      <label class="admin-label">New Password</label>
      <input type="password" name="admin_password" class="admin-input" placeholder="Min. 6 characters" minlength="6">
    </div>
    <div>
      <label class="admin-label">Confirm New Password</label>
      <input type="password" name="admin_password2" class="admin-input" placeholder="Repeat new password">
    </div>
  </div>
  <div class="mt-6 pt-5 border-t border-gray-100">
    <button type="submit" class="btn-admin"><i class="fas fa-save mr-2"></i>Update Account</button>
  </div>
</form>
<?php endif; ?>

</main></div></div></body></html>
