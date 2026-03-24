<?php
require_once 'includes/auth.php';
requireLogin();
$pageTitle = 'Complete Your Booking';

$type      = in_array($_GET['type'] ?? '', ['flight','hotel','both']) ? $_GET['type'] : 'flight';
$flightId  = (int)($_GET['flight_id'] ?? 0);
$hotelId   = (int)($_GET['hotel_id'] ?? 0);
$adults    = max(1, (int)($_GET['adults'] ?? 1));
$children  = max(0, (int)($_GET['children'] ?? 0));
$checkin   = trim($_GET['checkin'] ?? '');
$checkout  = trim($_GET['checkout'] ?? '');

$flight = $hotel = null;

if ($flightId) {
    $stmt = db()->prepare('SELECT * FROM flights WHERE id=? AND is_active=1');
    $stmt->execute([$flightId]);
    $flight = $stmt->fetch();
}
if ($hotelId) {
    $stmt = db()->prepare('SELECT * FROM hotels WHERE id=? AND is_active=1');
    $stmt->execute([$hotelId]);
    $hotel = $stmt->fetch();
}

if ($type === 'flight' && !$flight) {
    flash('error', 'Flight not found or unavailable.');
    header('Location: flights.php'); exit;
}
if ($type === 'hotel' && !$hotel) {
    flash('error', 'Hotel not found or unavailable.');
    header('Location: hotels.php'); exit;
}

// Calculate price
$nights = 0;
if ($checkin && $checkout) $nights = nightsBetween($checkin, $checkout);
if ($nights < 1) $nights = 1;

$flightTotal = $flight ? $flight['price'] * ($adults + $children) : 0;
$hotelTotal  = $hotel  ? $hotel['price_per_night'] * $nights : 0;
$grandTotal  = ($type==='flight') ? $flightTotal : (($type==='hotel') ? $hotelTotal : $flightTotal + $hotelTotal);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $user = currentUser();

    $passengerData = [];
    $paxCount = (int)$_POST['pax_count'];
    for ($i = 0; $i < $paxCount; $i++) {
        $passengerData[] = [
            'first_name'  => trim($_POST['pax_first'][$i] ?? ''),
            'last_name'   => trim($_POST['pax_last'][$i] ?? ''),
            'passport'    => trim($_POST['pax_passport'][$i] ?? ''),
            'nationality' => trim($_POST['pax_nationality'][$i] ?? ''),
            'dob'         => trim($_POST['pax_dob'][$i] ?? ''),
        ];
    }

    $ref   = generateBookingRef();
    $total = (float)$_POST['total_amount'];

    $stmt = db()->prepare('
        INSERT INTO bookings
          (booking_ref, user_id, booking_type, flight_id, hotel_id, check_in, check_out, nights, adults, children,
           passenger_details, contact_name, contact_email, contact_phone, total_amount, currency, special_requests)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ');
    $stmt->execute([
        $ref,
        $user['id'],
        $type,
        $flight ? $flight['id'] : null,
        $hotel  ? $hotel['id']  : null,
        $checkin  ?: null,
        $checkout ?: null,
        $nights,
        $adults,
        $children,
        json_encode($passengerData),
        trim($_POST['contact_name']),
        trim($_POST['contact_email']),
        trim($_POST['contact_phone']),
        $total,
        getSetting('currency', 'USD'),
        trim($_POST['special_requests'] ?? ''),
    ]);

    header('Location: payment.php?ref=' . urlencode($ref));
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<div class="max-w-5xl mx-auto px-4 py-10">
  <!-- Steps -->
  <div class="flex items-center gap-0 mb-8 justify-center">
    <?php
    $steps = ['Search', 'Booking Details', 'Payment', 'Confirmation'];
    foreach ($steps as $i => $s):
      $n = $i + 1;
      $done   = $n < 2;
      $active = $n === 2;
    ?>
      <div class="flex items-center">
        <div class="flex flex-col items-center">
          <div class="step-dot <?= $done?'done':($active?'active':'pending') ?>">
            <?= $done ? '<i class="fas fa-check text-sm"></i>' : $n ?>
          </div>
          <span class="text-xs mt-1 <?= $active?'text-sky-600 font-semibold':'text-gray-400' ?>"><?= $s ?></span>
        </div>
        <?php if ($n < 4): ?><div class="w-16 md:w-24 h-0.5 <?= $n<2?'bg-green-400':'bg-gray-200' ?> mb-4 mx-1"></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Booking Form -->
    <div class="lg:col-span-2">
      <form method="post">
        <?= csrfField() ?>
        <input type="hidden" name="pax_count" value="<?= $adults + $children ?>">
        <input type="hidden" name="total_amount" id="total-hidden" value="<?= number_format($grandTotal, 2, '.', '') ?>">

        <!-- Passenger Details -->
        <div class="card p-6 mb-6">
          <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
            <i class="fas fa-users text-sky-500"></i> Traveler Details
          </h2>
          <?php $totalPax = $adults + $children;
          for ($i = 0; $i < $totalPax; $i++):
            $isAdult = $i < $adults;
          ?>
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
              <p class="text-sm font-semibold text-sky-700 mb-4">
                <?= $isAdult ? 'Adult ' . ($i + 1) : 'Child ' . ($i - $adults + 1) ?>
              </p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                  <label class="form-label">First Name <span class="text-red-500">*</span></label>
                  <input type="text" name="pax_first[]" class="form-input" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                  <input type="text" name="pax_last[]" class="form-input" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Passport / ID Number</label>
                  <input type="text" name="pax_passport[]" class="form-input">
                </div>
                <div class="form-group">
                  <label class="form-label">Nationality</label>
                  <input type="text" name="pax_nationality[]" class="form-input">
                </div>
                <div class="form-group">
                  <label class="form-label">Date of Birth</label>
                  <input type="date" name="pax_dob[]" class="form-input">
                </div>
              </div>
            </div>
          <?php endfor; ?>
        </div>

        <!-- Hotel dates if applicable -->
        <?php if ($type === 'hotel' || $type === 'both'): ?>
        <div class="card p-6 mb-6">
          <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
            <i class="fas fa-calendar text-sky-500"></i> Hotel Dates
          </h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
              <label class="form-label">Check-in <span class="text-red-500">*</span></label>
              <input type="text" name="checkin_override" value="<?= e($checkin) ?>" class="form-input datepicker-checkin" required>
            </div>
            <div class="form-group">
              <label class="form-label">Check-out <span class="text-red-500">*</span></label>
              <input type="text" name="checkout_override" value="<?= e($checkout) ?>" class="form-input datepicker-checkout" required>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Contact Info -->
        <div class="card p-6 mb-6">
          <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
            <i class="fas fa-envelope text-sky-500"></i> Contact Information
          </h2>
          <?php $user = currentUser(); ?>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
              <label class="form-label">Full Name <span class="text-red-500">*</span></label>
              <input type="text" name="contact_name" value="<?= e($user['first_name'] . ' ' . $user['last_name']) ?>" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email Address <span class="text-red-500">*</span></label>
              <input type="email" name="contact_email" value="<?= e($user['email']) ?>" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
              <input type="tel" name="contact_phone" value="<?= e($user['phone'] ?? '') ?>" class="form-input" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Special Requests <span class="text-gray-400 font-normal">(optional)</span></label>
            <textarea name="special_requests" rows="3" class="form-input" placeholder="Any special requests, dietary needs, accessibility requirements..."></textarea>
          </div>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-4 text-lg">
          <i class="fas fa-arrow-right mr-2"></i>Proceed to Payment
        </button>
      </form>
    </div>

    <!-- Order Summary -->
    <div class="lg:col-span-1">
      <div class="card p-6 sticky top-20">
        <h3 class="font-bold text-gray-900 mb-5 text-lg">Booking Summary</h3>

        <?php if ($flight): ?>
          <div class="bg-sky-50 rounded-xl p-4 mb-4">
            <p class="text-xs font-bold text-sky-600 uppercase mb-2"><i class="fas fa-plane mr-1"></i> Flight</p>
            <p class="font-bold text-gray-900"><?= e($flight['airline']) ?></p>
            <p class="text-sm text-gray-600"><?= e($flight['origin_code']) ?> &rarr; <?= e($flight['destination_code']) ?></p>
            <p class="text-sm text-gray-600"><?= formatDate($flight['departure_date']) ?></p>
            <p class="text-sm text-gray-600"><?= formatTime($flight['departure_time']) ?> &rarr; <?= formatTime($flight['arrival_time']) ?></p>
            <p class="text-sm"><?= classBadge($flight['class']) ?></p>
            <div class="border-t border-sky-100 mt-3 pt-3 flex justify-between">
              <span class="text-sm text-gray-600"><?= $adults + $children ?> x <?= formatPrice($flight['price']) ?></span>
              <span class="font-bold text-sky-700"><?= formatPrice($flightTotal) ?></span>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($hotel): ?>
          <div class="bg-teal-50 rounded-xl p-4 mb-4">
            <p class="text-xs font-bold text-teal-600 uppercase mb-2"><i class="fas fa-hotel mr-1"></i> Hotel</p>
            <p class="font-bold text-gray-900"><?= e($hotel['name']) ?></p>
            <p class="text-sm text-gray-600"><?= e($hotel['city']) ?>, <?= e($hotel['country']) ?></p>
            <?php if ($checkin && $checkout): ?>
              <p class="text-sm text-gray-600"><?= formatDate($checkin) ?> &rarr; <?= formatDate($checkout) ?></p>
              <p class="text-sm text-gray-600"><?= $nights ?> night<?= $nights>1?'s':'' ?></p>
            <?php endif; ?>
            <div class="border-t border-teal-100 mt-3 pt-3 flex justify-between">
              <span class="text-sm text-gray-600"><?= $nights ?> night<?= $nights>1?'s':'' ?> x <?= formatPrice($hotel['price_per_night']) ?></span>
              <span class="font-bold text-teal-700"><?= formatPrice($hotelTotal) ?></span>
            </div>
          </div>
        <?php endif; ?>

        <div class="border-t border-gray-100 pt-4">
          <div class="flex justify-between items-center">
            <span class="font-bold text-gray-900 text-lg">Total</span>
            <span class="text-2xl font-extrabold text-sky-700" id="total-display"><?= formatPrice($grandTotal) ?></span>
          </div>
          <p class="text-xs text-gray-400 mt-1">All taxes and fees included</p>
        </div>

        <div class="mt-5 p-3 bg-green-50 rounded-xl">
          <p class="text-xs text-green-700 flex items-center gap-2">
            <i class="fas fa-shield-alt"></i> Secure booking. Your data is protected.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
