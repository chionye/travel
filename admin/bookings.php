<?php
$pageTitle = 'All Bookings';
require_once __DIR__ . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/email.php';

$filterPayment = $_GET['payment'] ?? '';
$filterType    = $_GET['type'] ?? '';
$search        = trim($_GET['q'] ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 20;
$offset        = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($filterPayment) { $where[] = 'b.payment_status = ?'; $params[] = $filterPayment; }
if ($filterType)    { $where[] = 'b.booking_type = ?';   $params[] = $filterType; }
if ($search) {
    $where[]  = '(b.booking_ref LIKE ? OR b.contact_name LIKE ? OR b.contact_email LIKE ?)';
    $like = "%$search%"; $params[] = $like; $params[] = $like; $params[] = $like;
}

$countSql = 'SELECT COUNT(*) FROM bookings b WHERE ' . implode(' AND ', $where);
$countStmt = db()->prepare($countSql); $countStmt->execute($params);
$total = $countStmt->fetchColumn();

$sql  = 'SELECT b.*, u.first_name, u.last_name, f.origin_code, f.destination_code, f.departure_date, h.name as hotel_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN flights f ON b.flight_id=f.id LEFT JOIN hotels h ON b.hotel_id=h.id WHERE ' . implode(' AND ', $where) . ' ORDER BY b.created_at DESC LIMIT ? OFFSET ?';
$stmt = db()->prepare($sql); $stmt->execute(array_merge($params, [$perPage, $offset]));
$bookings = $stmt->fetchAll();
?>

<!-- Filters -->
<div class="flex flex-wrap items-center gap-3 mb-6">
  <form method="get" class="flex flex-wrap gap-3 flex-1">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search ref, name, email..." class="admin-input w-56">
    <select name="payment" class="admin-input w-40">
      <option value="">All Payments</option>
      <?php foreach (['unpaid'=>'Unpaid','pending'=>'Pending Review','approved'=>'Approved','declined'=>'Declined'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= $filterPayment===$v?'selected':'' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
    <select name="type" class="admin-input w-36">
      <option value="">All Types</option>
      <?php foreach (['flight','hotel','both'] as $t): ?>
        <option value="<?= $t ?>" <?= $filterType===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-admin"><i class="fas fa-search mr-2"></i>Filter</button>
    <a href="bookings.php" class="btn-gray">Clear</a>
  </form>
</div>

<!-- Table -->
<div class="card-admin overflow-hidden">
  <div class="p-4 border-b border-gray-100 flex items-center justify-between">
    <p class="text-sm text-gray-500">Showing <strong><?= count($bookings) ?></strong> of <strong><?= $total ?></strong> bookings</p>
  </div>
  <div class="overflow-x-auto">
    <table class="admin-table">
      <thead><tr>
        <th>Ref</th><th>Customer</th><th>Details</th><th>Amount</th><th>Booking</th><th>Payment</th><th>Date</th><th>Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($bookings as $b): ?>
        <tr>
          <td><a href="booking-view.php?id=<?= $b['id'] ?>" class="font-mono text-xs font-bold text-sky-600 hover:underline"><?= e($b['booking_ref']) ?></a></td>
          <td>
            <p class="font-medium text-sm"><?= e(trim(($b['first_name']??'') . ' ' . ($b['last_name']??''))) ?: '<span class="text-gray-400">Guest</span>' ?></p>
            <p class="text-xs text-gray-400"><?= e($b['contact_email']) ?></p>
          </td>
          <td class="text-sm">
            <?php if ($b['origin_code']): ?>
              <p class="font-semibold"><?= e($b['origin_code']) ?> &rarr; <?= e($b['destination_code']) ?></p>
              <p class="text-xs text-gray-400"><?= formatDate($b['departure_date']) ?></p>
            <?php endif; ?>
            <?php if ($b['hotel_name']): ?>
              <p class="font-semibold"><?= e($b['hotel_name']) ?></p>
              <p class="text-xs text-gray-400"><?= $b['nights'] ?> nights</p>
            <?php endif; ?>
          </td>
          <td class="font-bold text-sky-700"><?= formatPrice($b['total_amount']) ?></td>
          <td><?= bookingBadge($b['booking_status']) ?></td>
          <td><?= paymentBadge($b['payment_status']) ?></td>
          <td class="text-xs text-gray-400"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
          <td>
            <a href="booking-view.php?id=<?= $b['id'] ?>" class="btn-admin text-xs px-3 py-1.5"><i class="fas fa-eye"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$bookings): ?>
        <tr><td colspan="8" class="text-center text-gray-400 py-10">No bookings found</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
// Pagination
$pages = ceil($total / $perPage);
if ($pages > 1):
    $qs = http_build_query(array_filter(['q'=>$search,'payment'=>$filterPayment,'type'=>$filterType]));
?>
<div class="flex gap-1 mt-4">
  <?php for ($i=1;$i<=$pages;$i++): ?>
    <a href="?<?= $qs ?>&page=<?= $i ?>" class="px-3 py-2 rounded-lg text-sm border <?= $i===$page?'bg-sky-600 text-white border-sky-600':'bg-white text-gray-600 hover:bg-sky-50' ?>"><?= $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

</main></div></div></body></html>
