<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Stats
$totalBookings  = db()->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$totalRevenue   = db()->query('SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE payment_status="approved"')->fetchColumn();
$pendingPayment = db()->query('SELECT COUNT(*) FROM bookings WHERE payment_status="pending"')->fetchColumn();
$totalUsers     = db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalFlights   = db()->query('SELECT COUNT(*) FROM flights WHERE is_active=1')->fetchColumn();
$totalHotels    = db()->query('SELECT COUNT(*) FROM hotels WHERE is_active=1')->fetchColumn();
$recentBookings = db()->query('SELECT b.*, u.first_name, u.last_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id ORDER BY b.created_at DESC LIMIT 8')->fetchAll();
?>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
  <?php
  $stats = [
    ['label'=>'Total Bookings','value'=>$totalBookings,'icon'=>'fa-ticket-alt','color'=>'from-sky-500 to-sky-600'],
    ['label'=>'Revenue','value'=>formatPrice($totalRevenue),'icon'=>'fa-dollar-sign','color'=>'from-green-500 to-green-600'],
    ['label'=>'Pending Payments','value'=>$pendingPayment,'icon'=>'fa-clock','color'=>'from-orange-500 to-orange-600'],
    ['label'=>'Registered Users','value'=>$totalUsers,'icon'=>'fa-users','color'=>'from-purple-500 to-purple-600'],
    ['label'=>'Active Flights','value'=>$totalFlights,'icon'=>'fa-plane','color'=>'from-teal-500 to-teal-600'],
    ['label'=>'Active Hotels','value'=>$totalHotels,'icon'=>'fa-hotel','color'=>'from-pink-500 to-pink-600'],
  ];
  foreach ($stats as $s): ?>
    <div class="card-admin p-5">
      <div class="w-10 h-10 bg-gradient-to-br <?= $s['color'] ?> rounded-xl flex items-center justify-center mb-3">
        <i class="fas <?= $s['icon'] ?> text-white text-sm"></i>
      </div>
      <p class="text-2xl font-extrabold text-gray-900"><?= $s['value'] ?></p>
      <p class="text-xs text-gray-500 mt-0.5"><?= $s['label'] ?></p>
    </div>
  <?php endforeach; ?>
</div>

<!-- Recent Bookings + Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 card-admin">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
      <h2 class="font-bold text-gray-900">Recent Bookings</h2>
      <a href="bookings.php" class="text-sm text-sky-600 font-medium">View all &rarr;</a>
    </div>
    <div class="overflow-x-auto">
      <table class="admin-table">
        <thead><tr><th>Ref</th><th>Customer</th><th>Type</th><th>Amount</th><th>Payment</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentBookings as $b): ?>
          <tr>
            <td><a href="booking-view.php?id=<?= $b['id'] ?>" class="font-mono font-bold text-sky-600 hover:underline text-xs"><?= e($b['booking_ref']) ?></a></td>
            <td><span class="font-medium"><?= e(trim($b['first_name'] . ' ' . $b['last_name'])) ?: 'Guest' ?></span></td>
            <td><span class="capitalize text-xs bg-sky-50 text-sky-700 px-2 py-1 rounded-full font-medium"><?= e($b['booking_type']) ?></span></td>
            <td class="font-bold text-sky-700"><?= formatPrice($b['total_amount']) ?></td>
            <td><?= paymentBadge($b['payment_status']) ?></td>
            <td class="text-gray-400 text-xs"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recentBookings): ?>
          <tr><td colspan="6" class="text-center text-gray-400 py-8">No bookings yet</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="space-y-4">
    <div class="card-admin p-5">
      <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
      <div class="space-y-3">
        <a href="flights.php?action=add" class="flex items-center gap-3 p-3 rounded-xl hover:bg-sky-50 border border-dashed border-sky-200 transition">
          <div class="w-9 h-9 bg-sky-100 rounded-lg flex items-center justify-center"><i class="fas fa-plus text-sky-600 text-sm"></i></div>
          <div><p class="font-semibold text-sm text-gray-800">Add New Flight</p><p class="text-xs text-gray-400">Create a new flight listing</p></div>
        </a>
        <a href="hotels.php?action=add" class="flex items-center gap-3 p-3 rounded-xl hover:bg-teal-50 border border-dashed border-teal-200 transition">
          <div class="w-9 h-9 bg-teal-100 rounded-lg flex items-center justify-center"><i class="fas fa-plus text-teal-600 text-sm"></i></div>
          <div><p class="font-semibold text-sm text-gray-800">Add New Hotel</p><p class="text-xs text-gray-400">Create a new hotel listing</p></div>
        </a>
        <a href="bookings.php?payment=pending" class="flex items-center gap-3 p-3 rounded-xl hover:bg-orange-50 border border-dashed border-orange-200 transition">
          <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center"><i class="fas fa-clock text-orange-600 text-sm"></i></div>
          <div><p class="font-semibold text-sm text-gray-800">Review Payments</p><p class="text-xs text-gray-400"><?= $pendingPayment ?> pending review</p></div>
        </a>
        <a href="settings.php" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 border border-dashed border-gray-200 transition">
          <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-cog text-gray-600 text-sm"></i></div>
          <div><p class="font-semibold text-sm text-gray-800">Site Settings</p><p class="text-xs text-gray-400">Configure your website</p></div>
        </a>
      </div>
    </div>
  </div>
</div>

</main></div></div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body></html>
