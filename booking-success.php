<?php
require_once 'includes/auth.php';
requireLogin();
$pageTitle = 'Booking Submitted';

$ref  = trim($_GET['ref'] ?? '');
$user = currentUser();
$stmt = db()->prepare('SELECT b.*, f.airline, f.flight_number, f.origin_code, f.destination_code, f.departure_date, f.departure_time, f.arrival_time, h.name as hotel_name, h.city as hotel_city FROM bookings b LEFT JOIN flights f ON b.flight_id=f.id LEFT JOIN hotels h ON b.hotel_id=h.id WHERE b.booking_ref=? AND b.user_id=?');
$stmt->execute([$ref, $user['id']]);
$booking = $stmt->fetch();

if (!$booking) { header('Location: dashboard.php'); exit; }
?>
<?php include 'includes/header.php'; ?>

<div class="max-w-2xl mx-auto px-4 py-16 text-center">
  <!-- Steps -->
  <div class="flex items-center gap-0 mb-10 justify-center">
    <?php foreach (['Search','Booking Details','Payment','Confirmation'] as $i=>$s):
      $n=$i+1; $done=$n<5; $active=$n===4; ?>
      <div class="flex items-center">
        <div class="flex flex-col items-center">
          <div class="step-dot <?= $done?'done':'pending' ?>"><?= $done?'<i class="fas fa-check text-sm"></i>':$n ?></div>
          <span class="text-xs mt-1 <?= $active?'text-sky-600 font-semibold':'text-gray-400' ?>"><?= $s ?></span>
        </div>
        <?php if ($n<4): ?><div class="w-16 md:w-24 h-0.5 bg-green-400 mb-4 mx-1"></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card p-10">
    <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
      <i class="fas fa-clock text-yellow-500 text-4xl"></i>
    </div>
    <h1 class="text-3xl font-extrabold text-gray-900 mb-3">Payment Submitted!</h1>
    <p class="text-gray-500 mb-6">Your payment proof has been received. Our team will verify it and confirm your booking within <strong>24 hours</strong>. You'll receive a confirmation email.</p>

    <div class="bg-sky-50 rounded-2xl p-6 mb-8 text-left">
      <p class="text-center text-sm text-gray-500 mb-2">Booking Reference</p>
      <p class="text-center text-3xl font-extrabold text-sky-700 tracking-wider"><?= e($booking['booking_ref']) ?></p>
      <div class="mt-4 pt-4 border-t border-sky-100 space-y-2">
        <?php if ($booking['airline']): ?>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Flight</span>
            <span class="font-medium"><?= e($booking['origin_code']) ?> &rarr; <?= e($booking['destination_code']) ?></span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Date</span>
            <span class="font-medium"><?= formatDate($booking['departure_date']) ?></span>
          </div>
        <?php endif; ?>
        <?php if ($booking['hotel_name']): ?>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Hotel</span>
            <span class="font-medium"><?= e($booking['hotel_name']) ?></span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Check-in</span>
            <span class="font-medium"><?= formatDate($booking['check_in']) ?></span>
          </div>
        <?php endif; ?>
        <div class="flex justify-between text-sm font-bold border-t border-sky-100 pt-2 mt-2">
          <span>Total Paid</span>
          <span class="text-sky-700"><?= formatPrice($booking['total_amount']) ?></span>
        </div>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="dashboard.php" class="btn-primary"><i class="fas fa-th-large mr-2"></i>My Dashboard</a>
      <a href="index.php" class="btn-secondary"><i class="fas fa-home mr-2"></i>Back to Home</a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
