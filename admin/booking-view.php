<?php
$pageTitle = 'Booking Details';
require_once __DIR__ . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/email.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT b.*, u.first_name, u.last_name, u.email as user_email, f.airline, f.flight_number, f.origin, f.destination, f.origin_code, f.destination_code, f.departure_date, f.departure_time, f.arrival_time, f.class as flight_class, f.duration, f.baggage, h.name as hotel_name, h.city as hotel_city, h.country as hotel_country, h.star_rating FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN flights f ON b.flight_id=f.id LEFT JOIN hotels h ON b.hotel_id=h.id WHERE b.id=?');
$stmt->execute([$id]);
$booking = $stmt->fetch();
if (!$booking) { echo '<p>Booking not found.</p>'; goto END; }

$stmt = db()->prepare('SELECT * FROM payments WHERE booking_id=? ORDER BY submitted_at DESC');
$stmt->execute([$id]);
$payments = $stmt->fetchAll();
$latestPayment = $payments[0] ?? null;

$passengers = json_decode($booking['passenger_details'] ?? '[]', true);

// Handle approve / decline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrf();
    $action     = $_POST['action'];
    $paymentId  = (int)($_POST['payment_id'] ?? 0);
    $adminNote  = trim($_POST['admin_note'] ?? '');

    if ($action === 'approve' && $paymentId) {
        db()->prepare('UPDATE payments SET status="approved", admin_note=?, reviewed_at=NOW() WHERE id=?')->execute([$adminNote, $paymentId]);
        db()->prepare('UPDATE bookings SET payment_status="approved", booking_status="confirmed" WHERE id=?')->execute([$id]);
        // Send confirmation email
        $flight = $hotel = null;
        if ($booking['flight_id']) {
            $r = db()->prepare('SELECT * FROM flights WHERE id=?'); $r->execute([$booking['flight_id']]); $flight = $r->fetch();
        }
        if ($booking['hotel_id']) {
            $r = db()->prepare('SELECT * FROM hotels WHERE id=?'); $r->execute([$booking['hotel_id']]); $hotel = $r->fetch();
        }
        sendBookingConfirmationEmail($booking, $flight, $hotel);
        flash('success', 'Payment approved and confirmation email sent to ' . $booking['contact_email']);
    } elseif ($action === 'decline' && $paymentId) {
        db()->prepare('UPDATE payments SET status="declined", admin_note=?, reviewed_at=NOW() WHERE id=?')->execute([$adminNote, $paymentId]);
        db()->prepare('UPDATE bookings SET payment_status="declined" WHERE id=?')->execute([$id]);
        sendPaymentDeclinedEmail($booking, $adminNote);
        flash('error', 'Payment declined. Email notification sent.');
    } elseif ($action === 'cancel_booking') {
        db()->prepare('UPDATE bookings SET booking_status="cancelled" WHERE id=?')->execute([$id]);
        flash('info', 'Booking cancelled.');
    }
    header('Location: booking-view.php?id=' . $id); exit;
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Main -->
  <div class="lg:col-span-2 space-y-6">
    <!-- Summary -->
    <div class="card-admin p-6">
      <div class="flex items-start justify-between mb-5">
        <div>
          <p class="text-sm text-gray-500 mb-1">Booking Reference</p>
          <p class="text-2xl font-extrabold text-sky-700 font-mono"><?= e($booking['booking_ref']) ?></p>
        </div>
        <div class="text-right">
          <?= bookingBadge($booking['booking_status']) ?>
          <div class="mt-1"><?= paymentBadge($booking['payment_status']) ?></div>
        </div>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm border-t border-gray-100 pt-4">
        <div><p class="text-gray-400 text-xs">Type</p><p class="font-semibold capitalize"><?= e($booking['booking_type']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Amount</p><p class="font-bold text-sky-700 text-lg"><?= formatPrice($booking['total_amount']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Passengers</p><p class="font-semibold"><?= $booking['adults'] ?> adult<?= $booking['adults']>1?'s':'' ?><?= $booking['children']>0?', '.$booking['children'].' child':'' ?></p></div>
        <div><p class="text-gray-400 text-xs">Booked</p><p class="font-semibold"><?= date('M j, Y H:i', strtotime($booking['created_at'])) ?></p></div>
      </div>
    </div>

    <!-- Contact -->
    <div class="card-admin p-6">
      <h3 class="font-bold text-gray-900 mb-4"><i class="fas fa-user mr-2 text-sky-500"></i>Contact Information</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
        <div><p class="text-gray-400 text-xs">Name</p><p class="font-semibold"><?= e($booking['contact_name']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Email</p><a href="mailto:<?= e($booking['contact_email']) ?>" class="font-semibold text-sky-600"><?= e($booking['contact_email']) ?></a></div>
        <div><p class="text-gray-400 text-xs">Phone</p><p class="font-semibold"><?= e($booking['contact_phone']) ?></p></div>
      </div>
      <?php if ($booking['special_requests']): ?>
      <div class="mt-4 p-3 bg-amber-50 rounded-xl">
        <p class="text-xs font-bold text-amber-600 mb-1">Special Requests</p>
        <p class="text-sm text-gray-700"><?= e($booking['special_requests']) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Flight -->
    <?php if ($booking['airline']): ?>
    <div class="card-admin p-6">
      <h3 class="font-bold text-gray-900 mb-4"><i class="fas fa-plane mr-2 text-sky-500"></i>Flight Details</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div><p class="text-gray-400 text-xs">Airline</p><p class="font-semibold"><?= e($booking['airline']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Flight No.</p><p class="font-semibold"><?= e($booking['flight_number']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Route</p><p class="font-semibold"><?= e($booking['origin_code']) ?> &rarr; <?= e($booking['destination_code']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Class</p><p class="font-semibold"><?= ucfirst($booking['flight_class'] ?? '') ?></p></div>
        <div><p class="text-gray-400 text-xs">Date</p><p class="font-semibold"><?= formatDate($booking['departure_date']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Departs</p><p class="font-semibold"><?= formatTime($booking['departure_time']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Arrives</p><p class="font-semibold"><?= formatTime($booking['arrival_time']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Duration</p><p class="font-semibold"><?= e($booking['duration'] ?? 'N/A') ?></p></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Hotel -->
    <?php if ($booking['hotel_name']): ?>
    <div class="card-admin p-6">
      <h3 class="font-bold text-gray-900 mb-4"><i class="fas fa-hotel mr-2 text-teal-500"></i>Hotel Details</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
        <div><p class="text-gray-400 text-xs">Hotel</p><p class="font-semibold"><?= e($booking['hotel_name']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Location</p><p class="font-semibold"><?= e($booking['hotel_city']) ?>, <?= e($booking['hotel_country']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Stars</p><p><?= starRating($booking['star_rating'] ?? 3) ?></p></div>
        <div><p class="text-gray-400 text-xs">Check-in</p><p class="font-semibold"><?= formatDate($booking['check_in']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Check-out</p><p class="font-semibold"><?= formatDate($booking['check_out']) ?></p></div>
        <div><p class="text-gray-400 text-xs">Nights</p><p class="font-semibold"><?= $booking['nights'] ?></p></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Passengers -->
    <?php if ($passengers): ?>
    <div class="card-admin p-6">
      <h3 class="font-bold text-gray-900 mb-4"><i class="fas fa-users mr-2 text-purple-500"></i>Passenger Details</h3>
      <div class="space-y-3">
      <?php foreach ($passengers as $i=>$p): ?>
        <div class="p-3 bg-gray-50 rounded-xl text-sm grid grid-cols-2 md:grid-cols-4 gap-3">
          <div><p class="text-gray-400 text-xs">Name</p><p class="font-semibold"><?= e($p['first_name'].' '.$p['last_name']) ?></p></div>
          <?php if ($p['passport']): ?><div><p class="text-gray-400 text-xs">Passport</p><p class="font-mono"><?= e($p['passport']) ?></p></div><?php endif; ?>
          <?php if ($p['nationality']): ?><div><p class="text-gray-400 text-xs">Nationality</p><p><?= e($p['nationality']) ?></p></div><?php endif; ?>
          <?php if ($p['dob']): ?><div><p class="text-gray-400 text-xs">DOB</p><p><?= e($p['dob']) ?></p></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar: Payments -->
  <div class="space-y-5">
    <!-- Payment Review -->
    <?php if ($latestPayment): ?>
    <div class="card-admin p-5">
      <h3 class="font-bold text-gray-900 mb-4">Payment Proof</h3>
      <div class="mb-3 text-sm space-y-2">
        <div class="flex justify-between"><span class="text-gray-500">Method</span><span class="font-semibold capitalize"><?= e($latestPayment['payment_method']) ?></span></div>
        <?php if ($latestPayment['crypto_type']): ?>
          <div class="flex justify-between"><span class="text-gray-500">Crypto</span><span class="font-semibold"><?= e($latestPayment['crypto_type']) ?></span></div>
        <?php endif; ?>
        <div class="flex justify-between"><span class="text-gray-500">Amount</span><span class="font-bold text-sky-700"><?= formatPrice($latestPayment['amount']) ?></span></div>
        <div class="flex justify-between"><span class="text-gray-500">Submitted</span><span><?= date('M j, Y H:i', strtotime($latestPayment['submitted_at'])) ?></span></div>
        <?php if ($latestPayment['transaction_ref']): ?>
          <div><p class="text-gray-500 text-xs">Tx Ref</p><p class="font-mono text-xs break-all"><?= e($latestPayment['transaction_ref']) ?></p></div>
        <?php endif; ?>
        <div class="flex justify-between"><span class="text-gray-500">Status</span><?= paymentBadge($latestPayment['status']) ?></div>
      </div>

      <!-- Proof Image -->
      <?php if ($latestPayment['proof_image']): ?>
        <div class="mb-4">
          <p class="text-xs text-gray-500 mb-2">Payment Proof:</p>
          <?php
          $ext = strtolower(pathinfo($latestPayment['proof_image'], PATHINFO_EXTENSION));
          if ($ext === 'pdf'): ?>
            <a href="<?= e(UPLOAD_URL . $latestPayment['proof_image']) ?>" target="_blank" class="flex items-center gap-2 p-3 bg-red-50 rounded-xl text-red-600 text-sm font-medium">
              <i class="fas fa-file-pdf text-xl"></i> View PDF Proof
            </a>
          <?php else: ?>
            <a href="<?= e(UPLOAD_URL . $latestPayment['proof_image']) ?>" target="_blank">
              <img src="<?= e(UPLOAD_URL . $latestPayment['proof_image']) ?>" class="w-full rounded-xl border border-gray-200 hover:opacity-90 transition" alt="Payment Proof">
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Approve / Decline -->
      <?php if ($latestPayment['status'] === 'pending'): ?>
      <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="payment_id" value="<?= $latestPayment['id'] ?>">
        <div class="mb-3">
          <label class="admin-label">Admin Note (optional)</label>
          <textarea name="admin_note" rows="2" class="admin-input" placeholder="Reason for approval or decline..."></textarea>
        </div>
        <div class="flex gap-3">
          <button type="submit" name="action" value="approve" class="btn-success flex-1 justify-center">
            <i class="fas fa-check mr-2"></i>Approve
          </button>
          <button type="submit" name="action" value="decline" class="btn-danger flex-1 justify-center" onclick="return confirm('Decline this payment?')">
            <i class="fas fa-times mr-2"></i>Decline
          </button>
        </div>
      </form>
      <?php elseif ($latestPayment['admin_note']): ?>
        <div class="p-3 bg-gray-50 rounded-xl text-sm">
          <p class="text-gray-400 text-xs mb-1">Admin Note</p>
          <p><?= e($latestPayment['admin_note']) ?></p>
        </div>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="card-admin p-5 text-center">
      <i class="fas fa-receipt text-3xl text-gray-200 mb-3"></i>
      <p class="text-gray-500 text-sm">No payment submitted yet</p>
    </div>
    <?php endif; ?>

    <!-- Cancel Booking -->
    <?php if ($booking['booking_status'] !== 'cancelled'): ?>
    <div class="card-admin p-5">
      <h3 class="font-bold text-gray-900 mb-3">Actions</h3>
      <form method="post">
        <?= csrfField() ?>
        <button type="submit" name="action" value="cancel_booking" class="btn-danger w-full justify-center" onclick="return confirm('Cancel this booking?')">
          <i class="fas fa-ban mr-2"></i>Cancel Booking
        </button>
      </form>
    </div>
    <?php endif; ?>

    <!-- Payment History -->
    <?php if (count($payments) > 1): ?>
    <div class="card-admin p-5">
      <h3 class="font-bold text-gray-900 mb-3">Payment History</h3>
      <?php foreach ($payments as $p): ?>
        <div class="flex justify-between text-sm py-2 border-b border-gray-50 last:border-0">
          <span class="text-gray-500"><?= date('M j, H:i', strtotime($p['submitted_at'])) ?></span>
          <?= paymentBadge($p['status']) ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php END: ?>
</main></div></div></body></html>
