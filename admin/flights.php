<?php
$pageTitle = 'Manage Flights';
require_once __DIR__ . '/includes/header.php';

$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);
$error  = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $data = [
        'airline'        => trim($_POST['airline'] ?? ''),
        'airline_code'   => strtoupper(trim($_POST['airline_code'] ?? '')),
        'flight_number'  => strtoupper(trim($_POST['flight_number'] ?? '')),
        'origin'         => trim($_POST['origin'] ?? ''),
        'origin_code'    => strtoupper(trim($_POST['origin_code'] ?? '')),
        'destination'    => trim($_POST['destination'] ?? ''),
        'destination_code' => strtoupper(trim($_POST['destination_code'] ?? '')),
        'departure_date' => $_POST['departure_date'] ?? '',
        'departure_time' => $_POST['departure_time'] ?? '',
        'arrival_time'   => $_POST['arrival_time'] ?? '',
        'duration'       => trim($_POST['duration'] ?? ''),
        'class'          => $_POST['class'] ?? 'economy',
        'price'          => (float)($_POST['price'] ?? 0),
        'available_seats'=> (int)($_POST['available_seats'] ?? 100),
        'baggage'        => trim($_POST['baggage'] ?? '20kg'),
        'stops'          => (int)($_POST['stops'] ?? 0),
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
    ];

    if (!$data['airline'] || !$data['flight_number'] || !$data['origin'] || !$data['destination'] || !$data['departure_date'] || $data['price'] <= 0) {
        $error = 'Please fill all required fields with valid values.';
    } else {
        $postAction = $_POST['form_action'] ?? '';
        if ($postAction === 'add') {
            $stmt = db()->prepare('INSERT INTO flights (airline,airline_code,flight_number,origin,origin_code,destination,destination_code,departure_date,departure_time,arrival_time,duration,class,price,available_seats,baggage,stops,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute(array_values($data));
            flash('success', 'Flight added successfully.');
            header('Location: flights.php'); exit;
        } elseif ($postAction === 'edit' && $editId) {
            $stmt = db()->prepare('UPDATE flights SET airline=?,airline_code=?,flight_number=?,origin=?,origin_code=?,destination=?,destination_code=?,departure_date=?,departure_time=?,arrival_time=?,duration=?,class=?,price=?,available_seats=?,baggage=?,stops=?,is_active=? WHERE id=?');
            $stmt->execute(array_merge(array_values($data), [$editId]));
            flash('success', 'Flight updated successfully.');
            header('Location: flights.php'); exit;
        }
    }
    $action = ($postAction === 'add') ? 'add' : 'edit';
}

// Delete
if (isset($_GET['delete']) && (int)$_GET['delete']) {
    db()->prepare('DELETE FROM flights WHERE id=?')->execute([(int)$_GET['delete']]);
    flash('success', 'Flight deleted.');
    header('Location: flights.php'); exit;
}

// Toggle active
if (isset($_GET['toggle']) && (int)$_GET['toggle']) {
    $tid = (int)$_GET['toggle'];
    $cur = db()->prepare('SELECT is_active FROM flights WHERE id=?'); $cur->execute([$tid]); $row = $cur->fetch();
    db()->prepare('UPDATE flights SET is_active=? WHERE id=?')->execute([$row['is_active']?0:1, $tid]);
    header('Location: flights.php'); exit;
}

$editFlight = null;
if ($action === 'edit' && $editId) {
    $stmt = db()->prepare('SELECT * FROM flights WHERE id=?'); $stmt->execute([$editId]);
    $editFlight = $stmt->fetch();
    if (!$editFlight) { $action = 'list'; }
}

if ($action === 'add' || $action === 'edit'):
$f = $editFlight ?? array_fill_keys(['airline','airline_code','flight_number','origin','origin_code','destination','destination_code','departure_date','departure_time','arrival_time','duration','class','price','available_seats','baggage','stops','is_active'], '');
if (!$editFlight) { $f['available_seats'] = 100; $f['baggage'] = '20kg'; $f['class'] = 'economy'; $f['stops'] = 0; $f['is_active'] = 1; }
?>

<div class="flex items-center gap-3 mb-6">
  <a href="flights.php" class="btn-gray"><i class="fas fa-arrow-left mr-2"></i>Back to Flights</a>
  <h2 class="font-bold text-gray-900"><?= $action==='add'?'Add New Flight':'Edit Flight' ?></h2>
</div>

<?php if ($error): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-5"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" class="card-admin p-6">
  <?= csrfField() ?>
  <input type="hidden" name="form_action" value="<?= $action ?>">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <div>
      <label class="admin-label">Airline Name <span class="text-red-500">*</span></label>
      <input type="text" name="airline" value="<?= e($f['airline']) ?>" class="admin-input" required placeholder="e.g. Emirates">
    </div>
    <div>
      <label class="admin-label">Airline Code</label>
      <input type="text" name="airline_code" value="<?= e($f['airline_code']) ?>" class="admin-input" placeholder="e.g. EK" maxlength="5">
    </div>
    <div>
      <label class="admin-label">Flight Number <span class="text-red-500">*</span></label>
      <input type="text" name="flight_number" value="<?= e($f['flight_number']) ?>" class="admin-input" required placeholder="e.g. EK201">
    </div>
    <div>
      <label class="admin-label">Origin City <span class="text-red-500">*</span></label>
      <input type="text" name="origin" value="<?= e($f['origin']) ?>" class="admin-input" required placeholder="e.g. New York">
    </div>
    <div>
      <label class="admin-label">Origin Code <span class="text-red-500">*</span></label>
      <input type="text" name="origin_code" value="<?= e($f['origin_code']) ?>" class="admin-input" required placeholder="e.g. JFK" maxlength="5">
    </div>
    <div>
      <label class="admin-label">Destination City <span class="text-red-500">*</span></label>
      <input type="text" name="destination" value="<?= e($f['destination']) ?>" class="admin-input" required placeholder="e.g. London">
    </div>
    <div>
      <label class="admin-label">Destination Code <span class="text-red-500">*</span></label>
      <input type="text" name="destination_code" value="<?= e($f['destination_code']) ?>" class="admin-input" required placeholder="e.g. LHR" maxlength="5">
    </div>
    <div>
      <label class="admin-label">Departure Date <span class="text-red-500">*</span></label>
      <input type="date" name="departure_date" value="<?= e($f['departure_date']) ?>" class="admin-input" required>
    </div>
    <div>
      <label class="admin-label">Departure Time <span class="text-red-500">*</span></label>
      <input type="time" name="departure_time" value="<?= e($f['departure_time']) ?>" class="admin-input" required>
    </div>
    <div>
      <label class="admin-label">Arrival Time <span class="text-red-500">*</span></label>
      <input type="time" name="arrival_time" value="<?= e($f['arrival_time']) ?>" class="admin-input" required>
    </div>
    <div>
      <label class="admin-label">Duration</label>
      <input type="text" name="duration" value="<?= e($f['duration']) ?>" class="admin-input" placeholder="e.g. 7h 30m">
    </div>
    <div>
      <label class="admin-label">Class <span class="text-red-500">*</span></label>
      <select name="class" class="admin-input">
        <?php foreach (['economy'=>'Economy','business'=>'Business','first'=>'First Class'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= ($f['class']===$v)?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="admin-label">Price (<?= getSetting('currency_symbol','$') ?>) <span class="text-red-500">*</span></label>
      <input type="number" name="price" value="<?= e($f['price']) ?>" class="admin-input" required step="0.01" min="0" placeholder="299.99">
    </div>
    <div>
      <label class="admin-label">Available Seats</label>
      <input type="number" name="available_seats" value="<?= e($f['available_seats']) ?>" class="admin-input" min="0" max="999">
    </div>
    <div>
      <label class="admin-label">Baggage Allowance</label>
      <input type="text" name="baggage" value="<?= e($f['baggage']) ?>" class="admin-input" placeholder="e.g. 23kg">
    </div>
    <div>
      <label class="admin-label">Stops</label>
      <select name="stops" class="admin-input">
        <option value="0" <?= ($f['stops']==0)?'selected':'' ?>>Non-stop</option>
        <option value="1" <?= ($f['stops']==1)?'selected':'' ?>>1 Stop</option>
        <option value="2" <?= ($f['stops']==2)?'selected':'' ?>>2 Stops</option>
      </select>
    </div>
    <div class="flex items-center gap-3 mt-6">
      <input type="checkbox" name="is_active" id="is_active" value="1" <?= $f['is_active']?'checked':'' ?> class="w-4 h-4 text-sky-600">
      <label for="is_active" class="text-sm font-medium text-gray-700">Active (visible to users)</label>
    </div>
  </div>
  <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
    <button type="submit" class="btn-admin">
      <i class="fas fa-save mr-2"></i><?= $action==='add'?'Add Flight':'Update Flight' ?>
    </button>
    <a href="flights.php" class="btn-gray">Cancel</a>
  </div>
</form>

<?php else: // LIST VIEW
$search  = trim($_GET['q'] ?? '');
$flights = [];
$where   = ['1=1']; $params = [];
if ($search) { $where[] = '(airline LIKE ? OR flight_number LIKE ? OR origin LIKE ? OR destination LIKE ?)'; $like="%$search%"; $params=array_fill(0,4,$like); }
$stmt = db()->prepare('SELECT * FROM flights WHERE '.implode(' AND ',$where).' ORDER BY departure_date DESC, created_at DESC'); $stmt->execute($params); $flights = $stmt->fetchAll();
?>

<div class="flex flex-wrap items-center gap-3 mb-6">
  <form method="get" class="flex gap-3 flex-1">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search airline, flight number, route..." class="admin-input w-64">
    <button type="submit" class="btn-admin"><i class="fas fa-search"></i></button>
  </form>
  <a href="flights.php?action=add" class="btn-admin"><i class="fas fa-plus mr-2"></i>Add New Flight</a>
</div>

<div class="card-admin overflow-hidden">
  <div class="p-4 border-b border-gray-100">
    <p class="text-sm text-gray-500"><strong><?= count($flights) ?></strong> flights found</p>
  </div>
  <div class="overflow-x-auto">
    <table class="admin-table">
      <thead><tr><th>Flight</th><th>Route</th><th>Date</th><th>Time</th><th>Class</th><th>Price</th><th>Seats</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($flights as $f): ?>
        <tr>
          <td>
            <p class="font-bold text-sm"><?= e($f['flight_number']) ?></p>
            <p class="text-xs text-gray-400"><?= e($f['airline']) ?></p>
          </td>
          <td>
            <p class="font-semibold text-sm"><?= e($f['origin_code']) ?> &rarr; <?= e($f['destination_code']) ?></p>
            <p class="text-xs text-gray-400"><?= e($f['origin']) ?> &rarr; <?= e($f['destination']) ?></p>
          </td>
          <td class="text-sm"><?= formatDate($f['departure_date']) ?></td>
          <td class="text-sm"><?= formatTime($f['departure_time']) ?> &rarr; <?= formatTime($f['arrival_time']) ?></td>
          <td><?= classBadge($f['class']) ?></td>
          <td class="font-bold text-sky-700"><?= formatPrice($f['price']) ?></td>
          <td class="text-sm"><?= $f['available_seats'] ?></td>
          <td>
            <a href="flights.php?toggle=<?= $f['id'] ?>" class="text-xs px-2 py-1 rounded-full font-semibold <?= $f['is_active']?'bg-green-100 text-green-700':'bg-gray-100 text-gray-500' ?>">
              <?= $f['is_active']?'Active':'Inactive' ?>
            </a>
          </td>
          <td>
            <div class="flex gap-2">
              <a href="flights.php?action=edit&id=<?= $f['id'] ?>" class="btn-admin text-xs px-3 py-1.5"><i class="fas fa-edit"></i></a>
              <a href="flights.php?delete=<?= $f['id'] ?>" onclick="return confirm('Delete this flight? Existing bookings will not be affected.')" class="btn-danger text-xs px-3 py-1.5"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$flights): ?>
        <tr><td colspan="9" class="text-center text-gray-400 py-10">
          No flights yet. <a href="flights.php?action=add" class="text-sky-600 underline">Add your first flight</a>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

</main></div></div></body></html>
