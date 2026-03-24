<?php
require_once 'includes/auth.php';
requireLogin();
$pageTitle = 'My Dashboard';
$user = currentUser();

// Stats
$stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE user_id=?'); $stmt->execute([$user['id']]);
$totalBookings = $stmt->fetchColumn();
$stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE user_id=? AND booking_status="confirmed"'); $stmt->execute([$user['id']]);
$confirmed = $stmt->fetchColumn();
$stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE user_id=? AND payment_status="pending"'); $stmt->execute([$user['id']]);
$pendingPayment = $stmt->fetchColumn();

// Bookings
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;
$stmt = db()->prepare('SELECT b.*, f.airline, f.flight_number, f.origin_code, f.destination_code, f.departure_date, h.name as hotel_name, h.city as hotel_city FROM bookings b LEFT JOIN flights f ON b.flight_id=f.id LEFT JOIN hotels h ON b.hotel_id=h.id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT ? OFFSET ?');
$stmt->execute([$user['id'], $perPage, $offset]);
$bookings = $stmt->fetchAll();
$stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE user_id=?'); $stmt->execute([$user['id']]);
$totalRows = $stmt->fetchColumn();
?>
<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-10">
  <?= renderFlash() ?>

  <!-- Header -->
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-extrabold text-gray-900">My Dashboard</h1>
      <p class="text-gray-500 mt-1">Welcome back, <strong><?= e($user['first_name']) ?></strong>! Here are your travel details.</p>
    </div>
    <div class="flex gap-3">
      <a href="flights.php" class="btn-primary btn-sm"><i class="fas fa-plane mr-2"></i>Book Flight</a>
      <a href="hotels.php" class="btn-secondary btn-sm"><i class="fas fa-hotel mr-2"></i>Book Hotel</a>
    </div>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="stat-card">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-sky-100 rounded-2xl flex items-center justify-center">
          <i class="fas fa-ticket-alt text-sky-600 text-2xl"></i>
        </div>
        <div>
          <p class="text-3xl font-extrabold text-gray-900"><?= $totalBookings ?></p>
          <p class="text-sm text-gray-500">Total Bookings</p>
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center">
          <i class="fas fa-check-circle text-green-600 text-2xl"></i>
        </div>
        <div>
          <p class="text-3xl font-extrabold text-gray-900"><?= $confirmed ?></p>
          <p class="text-sm text-gray-500">Confirmed</p>
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-yellow-100 rounded-2xl flex items-center justify-center">
          <i class="fas fa-clock text-yellow-600 text-2xl"></i>
        </div>
        <div>
          <p class="text-3xl font-extrabold text-gray-900"><?= $pendingPayment ?></p>
          <p class="text-sm text-gray-500">Pending Review</p>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Bookings Table -->
    <div class="lg:col-span-2">
      <div class="card">
        <div class="p-5 border-b border-gray-100">
          <h2 class="font-bold text-gray-900 text-lg">My Bookings</h2>
        </div>
        <?php if (empty($bookings)): ?>
          <div class="p-12 text-center">
            <i class="fas fa-suitcase text-4xl text-gray-200 mb-4"></i>
            <h3 class="font-bold text-gray-700 mb-2">No Bookings Yet</h3>
            <p class="text-gray-400 text-sm mb-4">Start planning your next adventure!</p>
            <a href="flights.php" class="btn-primary btn-sm">Search Flights</a>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="data-table">
              <thead><tr>
                <th>Ref</th><th>Details</th><th>Date</th><th>Amount</th><th>Status</th><th>Action</th>
              </tr></thead>
              <tbody>
              <?php foreach ($bookings as $b): ?>
                <tr>
                  <td><span class="font-mono font-bold text-sky-700 text-xs"><?= e($b['booking_ref']) ?></span></td>
                  <td>
                    <?php if ($b['airline']): ?>
                      <p class="font-semibold text-sm"><?= e($b['origin_code']) ?> &rarr; <?= e($b['destination_code']) ?></p>
                      <p class="text-xs text-gray-400"><?= e($b['airline']) ?> &bull; <?= formatDate($b['departure_date']) ?></p>
                    <?php endif; ?>
                    <?php if ($b['hotel_name']): ?>
                      <p class="font-semibold text-sm"><?= e($b['hotel_name']) ?></p>
                      <p class="text-xs text-gray-400"><?= e($b['hotel_city']) ?> &bull; <?= $b['nights'] ?> nights</p>
                    <?php endif; ?>
                  </td>
                  <td class="text-xs text-gray-500"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
                  <td class="font-bold text-sky-700"><?= formatPrice($b['total_amount']) ?></td>
                  <td>
                    <?= bookingBadge($b['booking_status']) ?><br>
                    <div class="mt-1"><?= paymentBadge($b['payment_status']) ?></div>
                  </td>
                  <td>
                    <div class="flex gap-2">
                      <a href="itinerary.php?ref=<?= urlencode($b['booking_ref']) ?>" class="text-xs bg-sky-50 text-sky-600 px-3 py-1 rounded-lg hover:bg-sky-100 font-medium"><i class="fas fa-file-alt mr-1"></i>View</a>
                      <?php if (in_array($b['payment_status'], ['unpaid','declined'])): ?>
                        <a href="payment.php?ref=<?= urlencode($b['booking_ref']) ?>" class="text-xs bg-orange-50 text-orange-600 px-3 py-1 rounded-lg hover:bg-orange-100 font-medium"><i class="fas fa-credit-card mr-1"></i>Pay</a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?= paginate($totalRows, $perPage, $page, 'dashboard.php?') ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Profile Sidebar -->
    <div class="lg:col-span-1 space-y-6">
      <!-- Profile Card -->
      <div class="card p-6">
        <div class="flex items-center gap-4 mb-5">
          <div class="w-16 h-16 bg-gradient-to-br from-sky-400 to-teal-400 rounded-2xl flex items-center justify-center">
            <i class="fas fa-user text-white text-2xl"></i>
          </div>
          <div>
            <p class="font-bold text-gray-900 text-lg"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></p>
            <p class="text-sm text-gray-500"><?= e($user['email']) ?></p>
          </div>
        </div>
        <div class="space-y-3 text-sm">
          <div class="flex items-center gap-2 text-gray-600"><i class="fas fa-envelope text-sky-500 w-4"></i><?= e($user['email']) ?></div>
          <?php if ($user['phone']): ?>
            <div class="flex items-center gap-2 text-gray-600"><i class="fas fa-phone text-sky-500 w-4"></i><?= e($user['phone']) ?></div>
          <?php endif; ?>
          <div class="flex items-center gap-2 text-gray-600"><i class="fas fa-calendar text-sky-500 w-4"></i>Member since <?= date('M Y', strtotime($user['created_at'])) ?></div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card p-6">
        <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-3">
          <a href="flights.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-sky-50 transition group">
            <div class="w-10 h-10 bg-sky-100 group-hover:bg-sky-200 rounded-xl flex items-center justify-center">
              <i class="fas fa-plane text-sky-600"></i>
            </div>
            <div>
              <p class="font-semibold text-gray-800 text-sm">Search Flights</p>
              <p class="text-xs text-gray-400">Find available flights</p>
            </div>
          </a>
          <a href="hotels.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-teal-50 transition group">
            <div class="w-10 h-10 bg-teal-100 group-hover:bg-teal-200 rounded-xl flex items-center justify-center">
              <i class="fas fa-hotel text-teal-600"></i>
            </div>
            <div>
              <p class="font-semibold text-gray-800 text-sm">Browse Hotels</p>
              <p class="text-xs text-gray-400">Find accommodation</p>
            </div>
          </a>
        </div>
      </div>

      <!-- Payment Help -->
      <div class="card p-5 border border-orange-100 bg-orange-50">
        <div class="flex items-start gap-3">
          <i class="fas fa-info-circle text-orange-500 mt-0.5"></i>
          <div>
            <p class="font-semibold text-orange-800 text-sm">Payment Process</p>
            <p class="text-xs text-orange-700 mt-1">After booking, submit your payment proof. We verify within 24 hours and send a confirmation email.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
