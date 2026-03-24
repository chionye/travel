<?php
require_once 'includes/auth.php';
$pageTitle = 'Search Hotels';

$city     = trim($_GET['city'] ?? '');
$country  = trim($_GET['country'] ?? '');
$checkin  = trim($_GET['checkin'] ?? '');
$checkout = trim($_GET['checkout'] ?? '');
$stars    = (int)($_GET['stars'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 0);

$where  = ['h.is_active = 1'];
$params = [];

if ($city) {
    $where[]  = '(h.city LIKE ? OR h.name LIKE ? OR h.country LIKE ? OR h.location LIKE ?)';
    $params[] = "%$city%";
    $params[] = "%$city%";
    $params[] = "%$city%";
    $params[] = "%$city%";
}
if ($country) {
    $where[]  = 'h.country LIKE ?';
    $params[] = "%$country%";
}
if ($stars > 0) {
    $where[]  = 'h.star_rating >= ?';
    $params[] = $stars;
}
if ($maxPrice > 0) {
    $where[]  = 'h.price_per_night <= ?';
    $params[] = $maxPrice;
}

$sql    = 'SELECT * FROM hotels h WHERE ' . implode(' AND ', $where) . ' ORDER BY h.star_rating DESC, h.price_per_night ASC';
$stmt   = db()->prepare($sql);
$stmt->execute($params);
$hotels = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<!-- Search Bar -->
<div class="bg-sky-800 py-6">
  <div class="max-w-7xl mx-auto px-4">
    <form method="get" action="hotels.php" class="flex flex-wrap gap-3 items-end">
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">Destination</label>
        <input name="city" value="<?= e($city) ?>" placeholder="City, Hotel, Region" class="form-input w-48">
      </div>
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">Check-in</label>
        <input name="checkin" value="<?= e($checkin) ?>" placeholder="Check-in" class="form-input datepicker-checkin w-36">
      </div>
      <div>
        <label class="block text-sky-200 text-xs font-medium mb-1">Check-out</label>
        <input name="checkout" value="<?= e($checkout) ?>" placeholder="Check-out" class="form-input datepicker-checkout w-36">
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

    <!-- Filters -->
    <div class="lg:w-64 shrink-0">
      <div class="card p-5 sticky top-20">
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-sliders-h text-sky-500"></i> Filters</h3>
        <form method="get" action="hotels.php" id="filter-form">
          <input type="hidden" name="city" value="<?= e($city) ?>">
          <input type="hidden" name="checkin" value="<?= e($checkin) ?>">
          <input type="hidden" name="checkout" value="<?= e($checkout) ?>">

          <div class="mb-5">
            <p class="text-xs font-bold text-gray-500 uppercase mb-3">Star Rating</p>
            <?php foreach ([0=>'All Stars',5=>'5 Stars',4=>'4+ Stars',3=>'3+ Stars'] as $v=>$l): ?>
              <label class="flex items-center gap-2 mb-2 cursor-pointer">
                <input type="radio" name="stars" value="<?= $v ?>" <?= $stars===$v?'checked':'' ?> class="text-sky-600">
                <span class="text-sm text-gray-700">
                  <?= $v>0 ? str_repeat('★',$v).'<span class="text-gray-400">'.str_repeat('★',5-$v).'</span>' : $l ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>

          <div class="mb-5">
            <p class="text-xs font-bold text-gray-500 uppercase mb-3">Max Price / Night</p>
            <input type="number" name="max_price" value="<?= $maxPrice ?: '' ?>" placeholder="e.g. 300" class="form-input">
          </div>

          <button type="submit" class="btn-primary btn-sm w-full justify-center">Apply</button>
          <a href="hotels.php" class="btn-secondary btn-sm w-full justify-center mt-2">Clear</a>
        </form>
      </div>
    </div>

    <!-- Hotel Cards -->
    <div class="flex-1">
      <div class="flex items-center justify-between mb-4">
        <p class="text-gray-600">Found <strong><?= count($hotels) ?></strong> hotel<?= count($hotels)!=1?'s':'' ?>
          <?= $city ? ' in <strong>' . e($city) . '</strong>' : '' ?></p>
      </div>

      <?php if (empty($hotels)): ?>
        <div class="card p-12 text-center">
          <div class="w-20 h-20 bg-sky-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-hotel text-3xl text-sky-400"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-800 mb-2">No Hotels Found</h3>
          <p class="text-gray-500 mb-6">Try a different destination or adjust your filters.</p>
          <a href="hotels.php" class="btn-primary">Browse All Hotels</a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-6">
          <?php foreach ($hotels as $h): ?>
            <?php
            $nights = ($checkin && $checkout) ? nightsBetween($checkin, $checkout) : 1;
            $total  = $h['price_per_night'] * $nights;
            ?>
            <div class="hotel-card">
              <!-- Image -->
              <div class="relative h-52 bg-gradient-to-br from-sky-200 to-teal-200 overflow-hidden">
                <?php if ($h['image_url']): ?>
                  <img src="<?= e($h['image_url']) ?>" alt="<?= e($h['name']) ?>" class="w-full h-full object-cover" onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute top-3 left-3"><?= str_repeat('<i class="fas fa-star text-yellow-400 text-xs"></i>', $h['star_rating']) ?></div>
                <div class="absolute top-3 right-3 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                  <?= formatPrice($h['price_per_night']) ?>/night
                </div>
              </div>
              <!-- Content -->
              <div class="p-5">
                <h3 class="font-bold text-gray-900 text-lg mb-1"><?= e($h['name']) ?></h3>
                <p class="text-sm text-gray-500 flex items-center gap-1 mb-3">
                  <i class="fas fa-map-marker-alt text-sky-500"></i> <?= e($h['city']) ?>, <?= e($h['country']) ?>
                </p>
                <?php if ($h['description']): ?>
                  <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= e(substr($h['description'], 0, 120)) ?>...</p>
                <?php endif; ?>
                <?php if ($h['amenities']): ?>
                  <div class="flex flex-wrap gap-1 mb-4">
                    <?php foreach (array_slice(explode(',', $h['amenities']), 0, 4) as $am): ?>
                      <span class="bg-sky-50 text-sky-600 text-xs px-2 py-1 rounded-full"><?= e(trim($am)) ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <div class="flex items-center justify-between">
                  <div>
                    <?php if ($checkin && $checkout && $nights > 1): ?>
                      <p class="text-xs text-gray-400"><?= $nights ?> nights</p>
                      <p class="text-lg font-extrabold text-sky-700"><?= formatPrice($total) ?> total</p>
                    <?php else: ?>
                      <p class="text-xs text-gray-400">Starting from</p>
                      <p class="text-lg font-extrabold text-sky-700"><?= formatPrice($h['price_per_night']) ?>/night</p>
                    <?php endif; ?>
                  </div>
                  <a href="book.php?type=hotel&hotel_id=<?= $h['id'] ?>&checkin=<?= urlencode($checkin) ?>&checkout=<?= urlencode($checkout) ?>" class="btn-primary btn-sm">
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

<?php include 'includes/footer.php'; ?>
