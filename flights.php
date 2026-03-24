<?php
require_once 'includes/auth.php';
$pageTitle = 'Search Flights';

$origin      = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$date        = trim($_GET['date'] ?? '');
$adults      = max(1, (int)($_GET['adults'] ?? 1));
$children    = max(0, (int)($_GET['children'] ?? 0));
$class       = in_array($_GET['class'] ?? '', ['economy','business','first']) ? $_GET['class'] : '';
$maxPrice    = (float)($_GET['max_price'] ?? 0);
$stops       = $_GET['stops'] ?? '';

$flights = [];
$searched = $origin || $destination || $date;

if ($searched) {
    $where  = ['f.is_active = 1'];
    $params = [];
    if ($origin) {
        $where[]  = '(f.origin LIKE ? OR f.origin_code LIKE ?)';
        $params[] = "%$origin%";
        $params[] = "%$origin%";
    }
    if ($destination) {
        $where[]  = '(f.destination LIKE ? OR f.destination_code LIKE ?)';
        $params[] = "%$destination%";
        $params[] = "%$destination%";
    }
    if ($date) {
        $where[]  = 'f.departure_date = ?';
        $params[] = $date;
    }
    if ($class) {
        $where[]  = 'f.class = ?';
        $params[] = $class;
    }
    if ($maxPrice > 0) {
        $where[]  = 'f.price <= ?';
        $params[] = $maxPrice;
    }
    if ($stops !== '') {
        $where[]  = 'f.stops = ?';
        $params[] = (int)$stops;
    }
    $where[] = 'f.available_seats > 0';
    $sql  = 'SELECT f.* FROM flights f WHERE ' . implode(' AND ', $where) . ' ORDER BY f.price ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $flights = $stmt->fetchAll();
} else {
    // Show all upcoming flights
    $flights = db()->query('SELECT * FROM flights WHERE is_active=1 AND departure_date >= CURDATE() AND available_seats > 0 ORDER BY departure_date ASC, price ASC LIMIT 30')->fetchAll();
}
?>
<?php include 'includes/header.php'; ?>

<!-- Search Bar -->
<div class="bg-sky-800 py-6">
  <div class="max-w-7xl mx-auto px-4">
    <form method="get" action="flights.php" class="flex flex-wrap gap-3 items-end">
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">From</label>
        <input name="origin" value="<?= e($origin) ?>" placeholder="Origin" class="form-input w-40">
      </div>
      <div class="flex items-end pb-0.5">
        <button type="button" id="swapBtn" class="p-2 text-sky-300 hover:text-white" title="Swap">
          <i class="fas fa-exchange-alt"></i>
        </button>
      </div>
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">To</label>
        <input name="destination" value="<?= e($destination) ?>" placeholder="Destination" class="form-input w-40">
      </div>
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">Date</label>
        <input name="date" value="<?= e($date) ?>" placeholder="Date" class="form-input datepicker w-36">
      </div>
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">Adults</label>
        <input type="number" name="adults" value="<?= $adults ?>" min="1" max="9" class="form-input w-20">
      </div>
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">Class</label>
        <select name="class" class="form-input w-32">
          <option value="">Any Class</option>
          <option value="economy"  <?= $class==='economy'?'selected':'' ?>>Economy</option>
          <option value="business" <?= $class==='business'?'selected':'' ?>>Business</option>
          <option value="first"    <?= $class==='first'?'selected':'' ?>>First</option>
        </select>
      </div>
      <button type="submit" class="btn-primary">
        <i class="fas fa-search mr-2"></i>Search
      </button>
    </form>
  </div>
</div>

<!-- Results -->
<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex flex-col lg:flex-row gap-8">

    <!-- Filters Sidebar -->
    <div class="lg:w-64 shrink-0">
      <div class="card p-5 sticky top-20">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-sliders-h text-sky-500"></i> Filters</h3>
        <form method="get" action="flights.php" id="filter-form">
          <input type="hidden" name="origin" value="<?= e($origin) ?>">
          <input type="hidden" name="destination" value="<?= e($destination) ?>">
          <input type="hidden" name="date" value="<?= e($date) ?>">
          <input type="hidden" name="adults" value="<?= $adults ?>">

          <div class="mb-5">
            <p class="text-xs font-bold text-gray-500 uppercase mb-3">Stops</p>
            <?php foreach (['' => 'Any', '0' => 'Non-stop', '1' => '1 Stop', '2' => '2+ Stops'] as $v => $l): ?>
              <label class="flex items-center gap-2 mb-2 cursor-pointer">
                <input type="radio" name="stops" value="<?= $v ?>" <?= $stops===$v?'checked':'' ?> class="text-sky-600">
                <span class="text-sm text-gray-700"><?= $l ?></span>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="mb-5">
            <p class="text-xs font-bold text-gray-500 uppercase mb-3">Class</p>
            <?php foreach (['' => 'All Classes', 'economy' => 'Economy', 'business' => 'Business', 'first' => 'First Class'] as $v => $l): ?>
              <label class="flex items-center gap-2 mb-2 cursor-pointer">
                <input type="radio" name="class" value="<?= $v ?>" <?= $class===$v?'checked':'' ?> class="text-sky-600">
                <span class="text-sm text-gray-700"><?= $l ?></span>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="mb-5">
            <p class="text-xs font-bold text-gray-500 uppercase mb-3">Max Price</p>
            <input type="number" name="max_price" value="<?= $maxPrice ?: '' ?>" placeholder="e.g. 500" class="form-input">
          </div>

          <button type="submit" class="btn-primary btn-sm w-full justify-center">Apply Filters</button>
          <a href="flights.php" class="btn-secondary btn-sm w-full justify-center mt-2">Clear</a>
        </form>
      </div>
    </div>

    <!-- Flight Results -->
    <div class="flex-1">
      <div class="flex items-center justify-between mb-4">
        <p class="text-gray-600">
          <?php if ($searched): ?>
            Found <strong><?= count($flights) ?></strong> flight<?= count($flights)!=1?'s':'' ?>
            <?= $origin ? " from <strong>" . e($origin) . "</strong>" : '' ?>
            <?= $destination ? " to <strong>" . e($destination) . "</strong>" : '' ?>
          <?php else: ?>
            Showing <strong><?= count($flights) ?></strong> available flights
          <?php endif; ?>
        </p>
      </div>

      <?php if (empty($flights)): ?>
        <div class="card p-12 text-center">
          <div class="w-20 h-20 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-plane text-3xl text-sky-400"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-800 mb-2">No Flights Found</h3>
          <p class="text-gray-500 mb-6">Try different dates, destinations, or adjust your filters.</p>
          <a href="flights.php" class="btn-primary">Clear Search</a>
        </div>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($flights as $f): ?>
            <div class="flight-card">
              <div class="flex flex-col md:flex-row md:items-center gap-4">
                <!-- Airline -->
                <div class="flex items-center gap-3 md:w-40">
                  <div class="w-12 h-12 bg-sky-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plane text-sky-600"></i>
                  </div>
                  <div>
                    <p class="font-bold text-gray-900 text-sm"><?= e($f['airline']) ?></p>
                    <p class="text-xs text-gray-500"><?= e($f['flight_number']) ?></p>
                    <?= classBadge($f['class']) ?>
                  </div>
                </div>

                <!-- Route -->
                <div class="flex-1 flex items-center gap-3">
                  <div class="text-center">
                    <p class="text-2xl font-extrabold text-gray-900"><?= formatTime($f['departure_time']) ?></p>
                    <p class="text-sm font-bold text-sky-700"><?= e($f['origin_code']) ?></p>
                    <p class="text-xs text-gray-500"><?= e($f['origin']) ?></p>
                  </div>
                  <div class="flex-1 flex flex-col items-center">
                    <p class="text-xs text-gray-400 mb-1"><?= e($f['duration'] ?? '') ?></p>
                    <div class="flex items-center w-full">
                      <div class="h-px flex-1 bg-gray-300"></div>
                      <div class="mx-2 text-gray-400"><?= $f['stops'] == 0 ? '<i class="fas fa-plane text-xs text-sky-400"></i>' : '<span class="text-xs text-orange-500">' . $f['stops'] . ' stop</span>' ?></div>
                      <div class="h-px flex-1 bg-gray-300"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1"><?= $f['stops'] == 0 ? 'Non-stop' : ($f['stops'] . ' stop(s)') ?></p>
                  </div>
                  <div class="text-center">
                    <p class="text-2xl font-extrabold text-gray-900"><?= formatTime($f['arrival_time']) ?></p>
                    <p class="text-sm font-bold text-sky-700"><?= e($f['destination_code']) ?></p>
                    <p class="text-xs text-gray-500"><?= e($f['destination']) ?></p>
                  </div>
                </div>

                <!-- Info -->
                <div class="flex items-center gap-4 text-xs text-gray-500">
                  <div class="text-center">
                    <p class="font-medium"><?= formatDate($f['departure_date']) ?></p>
                    <p class="flex items-center gap-1 mt-0.5"><i class="fas fa-suitcase"></i><?= e($f['baggage'] ?? '20kg') ?></p>
                    <p class="flex items-center gap-1 mt-0.5"><i class="fas fa-chair"></i><?= $f['available_seats'] ?> seats</p>
                  </div>
                </div>

                <!-- Price + CTA -->
                <div class="text-right md:text-center md:w-36">
                  <p class="text-3xl font-extrabold text-sky-700"><?= formatPrice($f['price']) ?></p>
                  <p class="text-xs text-gray-400 mb-3">per person</p>
                  <a href="book.php?type=flight&flight_id=<?= $f['id'] ?>&adults=<?= $adults ?>&children=<?= $children ?>" class="btn-primary btn-sm w-full justify-center">
                    Book Now
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
$('#swapBtn').on('click', function() {
  var from = $('input[name="origin"]'), to = $('input[name="destination"]');
  var tmp = from.val();
  from.val(to.val());
  to.val(tmp);
});
</script>

<?php include 'includes/footer.php'; ?>
