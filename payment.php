<?php
require_once 'includes/auth.php';
require_once 'includes/email.php';
requireLogin();
$pageTitle = 'Complete Payment';

$ref = trim($_GET['ref'] ?? '');
if (!$ref) { header('Location: dashboard.php'); exit; }

$user   = currentUser();
$stmt   = db()->prepare('SELECT b.*, f.airline, f.flight_number, f.origin, f.destination, f.origin_code, f.destination_code, f.departure_date, f.departure_time, f.arrival_time, f.class as flight_class, h.name as hotel_name, h.city as hotel_city, h.country as hotel_country FROM bookings b LEFT JOIN flights f ON b.flight_id=f.id LEFT JOIN hotels h ON b.hotel_id=h.id WHERE b.booking_ref=? AND b.user_id=?');
$stmt->execute([$ref, $user['id']]);
$booking = $stmt->fetch();

if (!$booking) { flash('error', 'Booking not found.'); header('Location: dashboard.php'); exit; }
if ($booking['payment_status'] === 'approved') { flash('info', 'Payment already approved.'); header('Location: dashboard.php'); exit; }

$settings = getAllSettings();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $method     = $_POST['payment_method'] ?? '';
    $cryptoType = trim($_POST['crypto_type'] ?? '');
    $txRef      = trim($_POST['transaction_ref'] ?? '');

    if (!in_array($method, ['crypto','bank'])) {
        $error = 'Please select a payment method.';
    } elseif (empty($_FILES['proof_image']['name'])) {
        $error = 'Please upload your payment proof.';
    } else {
        $upload = uploadFile($_FILES['proof_image']);
        if (isset($upload['error'])) {
            $error = $upload['error'];
        } else {
            $stmt = db()->prepare('INSERT INTO payments (booking_id, payment_method, crypto_type, amount, currency, proof_image, transaction_ref) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$booking['id'], $method, $cryptoType, $booking['total_amount'], $settings['currency'] ?? 'USD', $upload['path'], $txRef]);

            $stmt = db()->prepare('UPDATE bookings SET payment_status=? WHERE id=?');
            $stmt->execute(['pending', $booking['id']]);

            sendPaymentReceivedEmail($booking);

            flash('success', 'Payment proof submitted! We will verify and confirm your booking within 24 hours.');
            header('Location: booking-success.php?ref=' . urlencode($ref));
            exit;
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 py-10">
  <!-- Steps -->
  <div class="flex items-center gap-0 mb-8 justify-center">
    <?php foreach (['Search','Booking Details','Payment','Confirmation'] as $i=>$s):
      $n=$i+1; $done=$n<3; $active=$n===3; ?>
      <div class="flex items-center">
        <div class="flex flex-col items-center">
          <div class="step-dot <?= $done?'done':($active?'active':'pending') ?>"><?= $done?'<i class="fas fa-check text-sm"></i>':$n ?></div>
          <span class="text-xs mt-1 <?= $active?'text-sky-600 font-semibold':'text-gray-400' ?>"><?= $s ?></span>
        </div>
        <?php if ($n<4): ?><div class="w-16 md:w-24 h-0.5 <?= $n<3?'bg-green-400':'bg-gray-200' ?> mb-4 mx-1"></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6"><?= e($error) ?></div>
  <?php endif; ?>

  <!-- Booking Summary -->
  <div class="card p-5 mb-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-gray-500">Booking Reference</p>
        <p class="font-extrabold text-sky-700 text-xl"><?= e($booking['booking_ref']) ?></p>
      </div>
      <div class="text-right">
        <p class="text-xs text-gray-500">Amount Due</p>
        <p class="font-extrabold text-2xl text-gray-900"><?= formatPrice($booking['total_amount']) ?></p>
      </div>
    </div>
    <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
      <?php if ($booking['airline']): ?>
        <div><p class="text-gray-400 text-xs">Flight</p><p class="font-medium"><?= e($booking['origin_code']) ?> &rarr; <?= e($booking['destination_code']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Date</p><p class="font-medium"><?= formatDate($booking['departure_date']) ?></p></div>
      <?php endif; ?>
      <?php if ($booking['hotel_name']): ?>
        <div><p class="text-gray-400 text-xs">Hotel</p><p class="font-medium"><?= e($booking['hotel_name']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Nights</p><p class="font-medium"><?= $booking['nights'] ?> nights</p></div>
      <?php endif; ?>
    </div>
  </div>

  <form method="post" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="payment_method" id="selected-method" value="">
    <input type="hidden" name="crypto_type" id="crypto-type-hidden" value="">

    <!-- Choose Method -->
    <h2 class="text-xl font-bold text-gray-900 mb-4">Choose Payment Method</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
      <!-- Crypto -->
      <div class="payment-option" data-method="crypto" id="opt-crypto">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center">
            <i class="fab fa-bitcoin text-amber-500 text-2xl"></i>
          </div>
          <div>
            <p class="font-bold text-gray-900 text-lg">Cryptocurrency</p>
            <p class="text-sm text-gray-500">BTC, ETH, USDT</p>
          </div>
        </div>
      </div>
      <!-- Bank Transfer -->
      <div class="payment-option" data-method="bank" id="opt-bank">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-sky-100 rounded-2xl flex items-center justify-center">
            <i class="fas fa-university text-sky-600 text-2xl"></i>
          </div>
          <div>
            <p class="font-bold text-gray-900 text-lg">Bank Transfer</p>
            <p class="text-sm text-gray-500">Wire / Direct Transfer</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Crypto Details -->
    <div id="details-crypto" class="payment-details hidden card p-6 mb-6">
      <h3 class="font-bold text-gray-900 mb-4">Select Cryptocurrency</h3>
      <div class="flex flex-wrap gap-3 mb-6">
        <?php
        $cryptos = [
          'BTC'  => ['name'=>'Bitcoin','icon'=>'fa-bitcoin','color'=>'text-amber-500','key'=>'btc_wallet'],
          'ETH'  => ['name'=>'Ethereum','icon'=>'fa-ethereum','color'=>'text-purple-500','key'=>'eth_wallet'],
          'USDT' => ['name'=>'USDT','icon'=>'fa-dollar-sign','color'=>'text-green-500','key'=>'usdt_wallet'],
        ];
        $anyCrypto = false;
        foreach ($cryptos as $code=>$c):
          $wallet = $settings[$c['key']] ?? '';
          if (!$wallet) continue;
          $anyCrypto = true;
        ?>
          <button type="button" class="crypto-sub flex items-center gap-2 px-4 py-3 border-2 border-gray-200 rounded-xl hover:border-sky-400 transition" data-crypto="<?= $code ?>">
            <i class="fab <?= $c['icon'] ?> <?= $c['color'] ?> text-xl"></i>
            <span class="font-semibold"><?= $c['name'] ?></span>
          </button>
        <?php endforeach; ?>
        <?php if (!$anyCrypto): ?>
          <p class="text-gray-500 text-sm">No crypto wallets configured. Please contact support.</p>
        <?php endif; ?>
      </div>
      <?php foreach ($cryptos as $code=>$c):
        $wallet = $settings[$c['key']] ?? '';
        if (!$wallet) continue;
        $network = ($code==='USDT') ? ($settings['usdt_network'] ?? 'TRC20') : '';
      ?>
        <div id="wallet-<?= $code ?>" class="crypto-wallet-display hidden bg-gray-50 rounded-2xl p-5">
          <div class="flex items-center gap-3 mb-3">
            <i class="fab <?= $c['icon'] ?> <?= $c['color'] ?> text-2xl"></i>
            <div>
              <p class="font-bold text-gray-900"><?= $c['name'] ?> <?= $network ? "($network)" : '' ?> Address</p>
              <p class="text-xs text-gray-500">Send exactly <?= formatPrice($booking['total_amount']) ?> worth of <?= $code ?></p>
            </div>
          </div>
          <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-3">
            <code class="flex-1 text-sm font-mono text-gray-800 break-all"><?= e($wallet) ?></code>
            <button type="button" class="copy-btn shrink-0 text-sky-600 hover:text-sky-700 px-3 py-1 rounded-lg border border-sky-200 text-xs font-semibold" data-copy="<?= e($wallet) ?>">
              <i class="fas fa-copy mr-1"></i>Copy
            </button>
          </div>
          <?php if ($network): ?><p class="text-xs text-orange-600 mt-2"><i class="fas fa-exclamation-triangle mr-1"></i>Only send via <?= $network ?> network</p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Bank Details -->
    <div id="details-bank" class="payment-details hidden card p-6 mb-6">
      <h3 class="font-bold text-gray-900 mb-4">Bank Transfer Details</h3>
      <?php
      $bankFields = [
        'bank_name'           => 'Bank Name',
        'bank_account_name'   => 'Account Name',
        'bank_account_number' => 'Account Number',
        'bank_routing'        => 'Routing / Sort Code',
        'bank_swift'          => 'SWIFT / BIC Code',
        'bank_additional'     => 'Additional Info',
      ];
      $anyBank = false;
      foreach ($bankFields as $key=>$label):
        $val = $settings[$key] ?? '';
        if (!$val) continue;
        $anyBank = true;
      ?>
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
          <span class="text-gray-500 text-sm"><?= $label ?></span>
          <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-900"><?= e($val) ?></span>
            <button type="button" class="copy-btn text-sky-500 hover:text-sky-700 text-xs" data-copy="<?= e($val) ?>"><i class="fas fa-copy"></i></button>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$anyBank): ?>
        <p class="text-gray-500 text-sm">Bank transfer details not configured. Please contact support.</p>
      <?php endif; ?>
      <div class="mt-4 p-3 bg-sky-50 rounded-xl">
        <p class="text-sm text-sky-700"><i class="fas fa-info-circle mr-2"></i>Please include your booking reference <strong><?= e($booking['booking_ref']) ?></strong> in your transfer description.</p>
      </div>
    </div>

    <!-- Upload Proof -->
    <div class="card p-6 mb-6">
      <h3 class="font-bold text-gray-900 mb-2">Upload Payment Proof <span class="text-red-500">*</span></h3>
      <p class="text-sm text-gray-500 mb-4">Upload a screenshot or photo of your payment confirmation. Accepted: JPG, PNG, PDF (max 5MB)</p>

      <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center hover:border-sky-400 transition cursor-pointer" onclick="document.getElementById('proof_upload').click()">
        <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-3"></i>
        <p class="font-medium text-gray-600">Click or drag to upload</p>
        <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WEBP or PDF</p>
        <input type="file" name="proof_image" id="proof_upload" class="hidden" accept="image/*,.pdf" required>
      </div>

      <div id="proof-preview-box" class="hidden mt-4 p-4 bg-gray-50 rounded-xl flex items-center gap-4">
        <img id="proof-preview" src="" class="hidden w-24 h-24 object-cover rounded-lg border">
        <div id="proof-pdf-icon" class="hidden w-16 h-16 bg-red-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
        </div>
        <div>
          <p id="proof-file-name" class="font-medium text-gray-700"></p>
          <button type="button" onclick="document.getElementById('proof_upload').click()" class="text-sm text-sky-600 mt-1">Change file</button>
        </div>
      </div>
    </div>

    <!-- Transaction Ref -->
    <div class="card p-6 mb-6">
      <div class="form-group">
        <label class="form-label">Transaction ID / Reference <span class="text-gray-400 font-normal">(optional but recommended)</span></label>
        <input type="text" name="transaction_ref" class="form-input" placeholder="e.g. TX123456ABC or blockchain hash">
      </div>
    </div>

    <button type="submit" class="btn-primary w-full justify-center py-4 text-lg">
      <i class="fas fa-paper-plane mr-2"></i>Submit Payment Proof
    </button>
    <p class="text-center text-xs text-gray-400 mt-3">Your booking will be confirmed after payment verification (usually within 24 hours)</p>
  </form>
</div>

<script>
var SITE_URL = '<?= SITE_URL ?>';
// Auto-select first payment method for UX
$(function() {
  // Payment option click handler already in main.js
});
</script>

<?php include 'includes/footer.php'; ?>
