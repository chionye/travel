<?php
require_once 'includes/auth.php';
requireLogin();
$pageTitle = 'Itinerary';
$user = currentUser();
$ref  = trim($_GET['ref'] ?? '');
$stmt = db()->prepare('SELECT b.*, f.airline, f.airline_code, f.flight_number, f.origin, f.destination, f.origin_code, f.destination_code, f.departure_date, f.departure_time, f.arrival_time, f.duration, f.class as flight_class, f.baggage, h.name as hotel_name, h.location as hotel_location, h.city as hotel_city, h.country as hotel_country, h.amenities as hotel_amenities, h.star_rating, p.payment_method, p.status as payment_status_detail, p.submitted_at as payment_submitted FROM bookings b LEFT JOIN flights f ON b.flight_id=f.id LEFT JOIN hotels h ON b.hotel_id=h.id LEFT JOIN payments p ON b.id=p.booking_id WHERE b.booking_ref=? AND b.user_id=?');
$stmt->execute([$ref, $user['id']]);
$b = $stmt->fetch();
if (!$b) { flash('error','Booking not found.'); header('Location: dashboard.php'); exit; }
$passengers = json_decode($b['passenger_details'] ?? '[]', true);
$siteName   = getSetting('site_name','SkyWave Travel');
?>
<?php include 'includes/header.php'; ?>

<div class="max-w-3xl mx-auto px-4 py-10">
  <!-- Print / Back -->
  <div class="flex items-center justify-between mb-6 no-print">
    <a href="dashboard.php" class="text-sky-600 hover:text-sky-700 flex items-center gap-2 font-medium">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <button onclick="window.print()" class="btn-secondary btn-sm">
      <i class="fas fa-print mr-2"></i>Print Itinerary
    </button>
  </div>

  <!-- Itinerary Card -->
  <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-sky-700 to-teal-600 p-8 text-white">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sky-200 text-sm font-medium mb-1"><?= e($siteName) ?> &bull; Travel Itinerary</p>
          <h1 class="text-3xl font-extrabold">Booking Confirmed</h1>
          <p class="mt-2 text-sky-100">Reference: <strong class="text-white text-xl"><?= e($b['booking_ref']) ?></strong></p>
        </div>
        <div class="text-right">
          <?= bookingBadge($b['booking_status']) ?>
          <div class="mt-2"><?= paymentBadge($b['payment_status']) ?></div>
          <p class="text-sky-200 text-xs mt-2">Booked <?= date('M j, Y', strtotime($b['created_at'])) ?></p>
        </div>
      </div>
    </div>

    <div class="p-8">
      <!-- Contact -->
      <div class="mb-8 p-4 bg-gray-50 rounded-2xl">
        <p class="text-xs font-bold text-gray-500 uppercase mb-3">Contact Information</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
          <div><p class="text-gray-400">Name</p><p class="font-semibold"><?= e($b['contact_name']) ?></p></div>
          <div><p class="text-gray-400">Email</p><p class="font-semibold"><?= e($b['contact_email']) ?></p></div>
          <div><p class="text-gray-400">Phone</p><p class="font-semibold"><?= e($b['contact_phone']) ?></p></div>
        </div>
      </div>

      <!-- Flight -->
      <?php if ($b['airline']): ?>
      <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-plane text-sky-600 text-sm"></i>
          </div>
          <h2 class="font-bold text-gray-900 text-lg">Flight Details</h2>
        </div>
        <div class="border border-gray-100 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <div class="text-center">
              <p class="text-4xl font-extrabold text-gray-900"><?= formatTime($b['departure_time']) ?></p>
              <p class="text-xl font-bold text-sky-700 mt-1"><?= e($b['origin_code']) ?></p>
              <p class="text-sm text-gray-500"><?= e($b['origin']) ?></p>
            </div>
            <div class="flex-1 flex flex-col items-center px-6">
              <p class="text-sm text-gray-400 mb-2"><?= e($b['duration'] ?? '') ?></p>
              <div class="flex items-center w-full">
                <div class="h-0.5 flex-1 bg-sky-200"></div>
                <i class="fas fa-plane text-sky-500 mx-3"></i>
                <div class="h-0.5 flex-1 bg-sky-200"></div>
              </div>
              <p class="text-xs text-gray-400 mt-2">Non-stop</p>
            </div>
            <div class="text-center">
              <p class="text-4xl font-extrabold text-gray-900"><?= formatTime($b['arrival_time']) ?></p>
              <p class="text-xl font-bold text-sky-700 mt-1"><?= e($b['destination_code']) ?></p>
              <p class="text-sm text-gray-500"><?= e($b['destination']) ?></p>
            </div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 text-sm">
            <div><p class="text-gray-400">Airline</p><p class="font-semibold"><?= e($b['airline']) ?></p></div>
            <div><p class="text-gray-400">Flight</p><p class="font-semibold"><?= e($b['flight_number']) ?></p></div>
            <div><p class="text-gray-400">Date</p><p class="font-semibold"><?= formatDate($b['departure_date']) ?></p></div>
            <div><p class="text-gray-400">Class</p><p class="font-semibold"><?= ucfirst($b['flight_class'] ?? '') ?></p></div>
            <div><p class="text-gray-400">Passengers</p><p class="font-semibold"><?= $b['adults'] ?> Adult<?= $b['adults']>1?'s':'' ?><?= $b['children']>0 ? ', '.$b['children'].' Child' : '' ?></p></div>
            <div><p class="text-gray-400">Baggage</p><p class="font-semibold"><?= e($b['baggage'] ?? '20kg') ?></p></div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Hotel -->
      <?php if ($b['hotel_name']): ?>
      <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-hotel text-teal-600 text-sm"></i>
          </div>
          <h2 class="font-bold text-gray-900 text-lg">Hotel Details</h2>
        </div>
        <div class="border border-gray-100 rounded-2xl p-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="font-bold text-gray-900 text-xl"><?= e($b['hotel_name']) ?></h3>
              <p class="text-gray-500 text-sm mt-1"><i class="fas fa-map-marker-alt text-teal-500 mr-1"></i><?= e($b['hotel_city']) ?>, <?= e($b['hotel_country']) ?></p>
              <div class="flex mt-1"><?= starRating($b['star_rating'] ?? 3) ?></div>
            </div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><p class="text-gray-400">Check-in</p><p class="font-semibold"><?= formatDate($b['check_in']) ?></p></div>
            <div><p class="text-gray-400">Check-out</p><p class="font-semibold"><?= formatDate($b['check_out']) ?></p></div>
            <div><p class="text-gray-400">Duration</p><p class="font-semibold"><?= $b['nights'] ?> Night<?= $b['nights']>1?'s':'' ?></p></div>
            <div><p class="text-gray-400">Guests</p><p class="font-semibold"><?= $b['adults'] + $b['children'] ?> Guest<?= ($b['adults']+$b['children'])>1?'s':'' ?></p></div>
          </div>
          <?php if ($b['hotel_amenities']): ?>
          <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 mb-2">Amenities</p>
            <div class="flex flex-wrap gap-2">
              <?php foreach (explode(',', $b['hotel_amenities']) as $am): ?>
                <span class="bg-teal-50 text-teal-700 text-xs px-3 py-1 rounded-full"><?= e(trim($am)) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Passengers -->
      <?php if ($passengers): ?>
      <div class="mb-8">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-users text-purple-600 text-sm"></i>
          </div>
          <h2 class="font-bold text-gray-900 text-lg">Passenger Details</h2>
        </div>
        <div class="space-y-3">
          <?php foreach ($passengers as $i=>$p): ?>
          <div class="border border-gray-100 rounded-xl p-4">
            <p class="font-semibold text-gray-800"><?= e($p['first_name'] . ' ' . $p['last_name']) ?> <span class="text-xs text-gray-400 ml-2">Passenger <?= $i+1 ?></span></p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2 text-sm">
              <?php if ($p['passport']): ?><div><p class="text-gray-400 text-xs">Passport</p><p><?= e($p['passport']) ?></p></div><?php endif; ?>
              <?php if ($p['nationality']): ?><div><p class="text-gray-400 text-xs">Nationality</p><p><?= e($p['nationality']) ?></p></div><?php endif; ?>
              <?php if ($p['dob']): ?><div><p class="text-gray-400 text-xs">Date of Birth</p><p><?= e($p['dob']) ?></p></div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Special Requests -->
      <?php if ($b['special_requests']): ?>
      <div class="mb-8 p-4 bg-amber-50 rounded-2xl">
        <p class="text-xs font-bold text-amber-600 uppercase mb-2">Special Requests</p>
        <p class="text-sm text-gray-700"><?= e($b['special_requests']) ?></p>
      </div>
      <?php endif; ?>

      <!-- Payment Summary -->
      <div class="border-2 border-sky-100 rounded-2xl p-6">
        <p class="text-xs font-bold text-gray-500 uppercase mb-4">Payment Summary</p>
        <div class="flex items-center justify-between">
          <span class="text-gray-600">Total Amount</span>
          <span class="text-2xl font-extrabold text-sky-700"><?= formatPrice($b['total_amount']) ?></span>
        </div>
        <div class="flex items-center justify-between mt-3">
          <span class="text-gray-600">Payment Status</span>
          <?= paymentBadge($b['payment_status']) ?>
        </div>
        <?php if (in_array($b['payment_status'], ['unpaid','declined'])): ?>
          <a href="payment.php?ref=<?= urlencode($b['booking_ref']) ?>" class="btn-orange btn-sm w-full justify-center mt-4">
            <i class="fas fa-credit-card mr-2"></i>Submit Payment
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
