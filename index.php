<?php
require_once 'includes/auth.php';
$pageTitle = 'Book Flights & Hotels';
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section min-h-[90vh] flex items-center relative">
  <!-- Background overlay with travel imagery feel -->
  <div class="absolute inset-0 overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background:url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1920&q=80') center/cover no-repeat"></div>
    <!-- Animated circles -->
    <div class="absolute top-20 right-10 w-64 h-64 bg-teal-400/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-10 w-80 h-80 bg-sky-300/10 rounded-full blur-3xl"></div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10 w-full">
    <div class="text-center mb-10">
      <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-2 mb-6">
        <span class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></span>
        <span class="text-white/90 text-sm">Trusted by 50,000+ travelers worldwide</span>
      </div>
      <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white mb-6 leading-tight">
        Your Dream
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 to-sky-300"> Vacation</span><br>Awaits
      </h1>
      <p class="text-xl text-sky-100 max-w-2xl mx-auto">Search, compare and book flights &amp; hotels worldwide at the best prices. Your journey begins here.</p>
    </div>

    <!-- Search Box -->
    <div class="max-w-5xl mx-auto">
      <!-- Tabs -->
      <div class="flex items-center gap-2 mb-4 justify-center">
        <button class="search-tab active" data-tab="flights">
          <i class="fas fa-plane mr-2"></i>Flights
        </button>
        <button class="search-tab" data-tab="hotels">
          <i class="fas fa-hotel mr-2"></i>Hotels
        </button>
        <button class="search-tab" data-tab="both">
          <i class="fas fa-suitcase mr-2"></i>Flights + Hotels
        </button>
      </div>

      <!-- Search Panel -->
      <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-6">

        <!-- Flights Panel -->
        <div id="panel-flights" class="search-panel">
          <form action="flights.php" method="get">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-plane-departure mr-1 text-sky-500"></i> From</label>
                <input type="text" name="origin" placeholder="City or Airport" class="form-input" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-plane-arrival mr-1 text-sky-500"></i> To</label>
                <input type="text" name="destination" placeholder="City or Airport" class="form-input" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-calendar mr-1 text-sky-500"></i> Date</label>
                <input type="text" name="date" placeholder="Departure Date" class="form-input datepicker" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-users mr-1 text-sky-500"></i> Passengers</label>
                <div class="relative">
                  <button type="button" class="form-input text-left flex items-center justify-between" id="pax-trigger">
                    <span id="passenger-display">1 Adult</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                  </button>
                  <input type="hidden" name="adults" value="1">
                  <input type="hidden" name="children" value="0">
                  <div class="absolute top-full left-0 w-64 bg-white shadow-xl rounded-2xl p-4 z-20 hidden" id="pax-dropdown">
                    <div class="flex items-center justify-between mb-3">
                      <span class="font-medium text-gray-700">Adults</span>
                      <div class="flex items-center gap-3">
                        <button type="button" class="pax-btn w-8 h-8 rounded-full bg-sky-100 text-sky-600 font-bold" data-type="adults" data-action="dec">-</button>
                        <span class="font-semibold w-4 text-center" id="adults-val">1</span>
                        <button type="button" class="pax-btn w-8 h-8 rounded-full bg-sky-100 text-sky-600 font-bold" data-type="adults" data-action="inc">+</button>
                      </div>
                    </div>
                    <div class="flex items-center justify-between">
                      <span class="font-medium text-gray-700">Children</span>
                      <div class="flex items-center gap-3">
                        <button type="button" class="pax-btn w-8 h-8 rounded-full bg-sky-100 text-sky-600 font-bold" data-type="children" data-action="dec">-</button>
                        <span class="font-semibold w-4 text-center" id="children-val">0</span>
                        <button type="button" class="pax-btn w-8 h-8 rounded-full bg-sky-100 text-sky-600 font-bold" data-type="children" data-action="inc">+</button>
                      </div>
                    </div>
                    <button type="button" class="mt-3 w-full bg-sky-600 text-white py-2 rounded-xl text-sm font-semibold" id="pax-done">Done</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex items-center justify-between mt-4">
              <div class="flex gap-4">
                <?php foreach (['economy'=>'Economy','business'=>'Business','first'=>'First Class'] as $val=>$label): ?>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="class" value="<?= $val ?>" <?= $val==='economy'?'checked':'' ?> class="text-sky-600">
                    <span class="text-sm font-medium text-gray-700"><?= $label ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <button type="submit" class="btn-primary">
                <i class="fas fa-search mr-2"></i>Search Flights
              </button>
            </div>
          </form>
        </div>

        <!-- Hotels Panel -->
        <div id="panel-hotels" class="search-panel hidden">
          <form action="hotels.php" method="get">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="lg:col-span-2">
                <label class="form-label text-gray-700"><i class="fas fa-map-marker-alt mr-1 text-sky-500"></i> Destination</label>
                <input type="text" name="city" placeholder="City, Region or Hotel Name" class="form-input" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-calendar-check mr-1 text-sky-500"></i> Check-in</label>
                <input type="text" name="checkin" placeholder="Check-in Date" class="form-input datepicker-checkin" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-calendar-times mr-1 text-sky-500"></i> Check-out</label>
                <input type="text" name="checkout" placeholder="Check-out Date" class="form-input datepicker-checkout" required>
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button type="submit" class="btn-primary">
                <i class="fas fa-search mr-2"></i>Search Hotels
              </button>
            </div>
          </form>
        </div>

        <!-- Flights + Hotels Panel -->
        <div id="panel-both" class="search-panel hidden">
          <form action="flights.php" method="get">
            <input type="hidden" name="type" value="both">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-plane-departure mr-1 text-sky-500"></i> Flying From</label>
                <input type="text" name="origin" placeholder="City or Airport" class="form-input" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-plane-arrival mr-1 text-sky-500"></i> Flying To</label>
                <input type="text" name="destination" placeholder="City or Airport" class="form-input" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-calendar mr-1 text-sky-500"></i> Departure</label>
                <input type="text" name="date" placeholder="Date" class="form-input datepicker" required>
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-calendar-check mr-1 text-sky-500"></i> Hotel Check-in</label>
                <input type="text" name="checkin" placeholder="Check-in" class="form-input datepicker-checkin">
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-calendar-times mr-1 text-sky-500"></i> Hotel Check-out</label>
                <input type="text" name="checkout" placeholder="Check-out" class="form-input datepicker-checkout">
              </div>
              <div>
                <label class="form-label text-gray-700"><i class="fas fa-users mr-1 text-sky-500"></i> Guests</label>
                <input type="number" name="adults" min="1" max="9" value="1" class="form-input">
              </div>
            </div>
            <div class="flex justify-end mt-4">
              <button type="submit" class="btn-primary">
                <i class="fas fa-search mr-2"></i>Search Package
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Trust Badges -->
<section class="bg-white py-8 border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      <div class="flex flex-col items-center gap-2">
        <div class="text-3xl font-extrabold text-sky-700">50K+</div>
        <div class="text-sm text-gray-500">Happy Travelers</div>
      </div>
      <div class="flex flex-col items-center gap-2">
        <div class="text-3xl font-extrabold text-sky-700">200+</div>
        <div class="text-sm text-gray-500">Destinations</div>
      </div>
      <div class="flex flex-col items-center gap-2">
        <div class="text-3xl font-extrabold text-sky-700">500+</div>
        <div class="text-sm text-gray-500">Partner Hotels</div>
      </div>
      <div class="flex flex-col items-center gap-2">
        <div class="text-3xl font-extrabold text-sky-700">24/7</div>
        <div class="text-sm text-gray-500">Customer Support</div>
      </div>
    </div>
  </div>
</section>

<!-- Features -->
<section class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-14">
      <span class="text-sky-600 font-semibold text-sm uppercase tracking-wider">Why Choose Us</span>
      <h2 class="text-4xl font-extrabold text-gray-900 mt-2">Travel Smarter, Not Harder</h2>
      <p class="text-gray-500 mt-3 max-w-xl mx-auto">We make booking travel effortless with the best prices, seamless experience, and 24/7 support.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
      <?php
      $features = [
        ['icon'=>'fa-tag','color'=>'from-sky-400 to-sky-600','title'=>'Best Price Guarantee','desc'=>'We match the lowest prices. Find it cheaper and we refund the difference.'],
        ['icon'=>'fa-shield-alt','color'=>'from-teal-400 to-teal-600','title'=>'100% Secure Booking','desc'=>'Your payments and personal data are protected with bank-level encryption.'],
        ['icon'=>'fa-headset','color'=>'from-orange-400 to-orange-600','title'=>'24/7 Support','desc'=>'Our travel experts are always ready to help you, day or night.'],
        ['icon'=>'fa-bolt','color'=>'from-purple-400 to-purple-600','title'=>'Instant Confirmation','desc'=>'Get your booking confirmation immediately after payment approval.'],
      ];
      foreach ($features as $f): ?>
        <div class="card p-8 text-center group">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br <?= $f['color'] ?> flex items-center justify-center mx-auto mb-5 group-hover:scale-110 transition-transform">
            <i class="fas <?= $f['icon'] ?> text-white text-2xl"></i>
          </div>
          <h3 class="font-bold text-gray-900 mb-2 text-lg"><?= $f['title'] ?></h3>
          <p class="text-gray-500 text-sm leading-relaxed"><?= $f['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Popular Destinations -->
<?php
$hotels = db()->query('SELECT * FROM hotels WHERE is_active=1 ORDER BY star_rating DESC LIMIT 6')->fetchAll();
?>
<?php if ($hotels): ?>
<section class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex items-end justify-between mb-12">
      <div>
        <span class="text-sky-600 font-semibold text-sm uppercase tracking-wider">Top Picks</span>
        <h2 class="text-4xl font-extrabold text-gray-900 mt-2">Popular Hotels</h2>
      </div>
      <a href="hotels.php" class="btn-secondary btn-sm">View All <i class="fas fa-arrow-right ml-2"></i></a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php foreach ($hotels as $hotel): ?>
        <div class="hotel-card">
          <div class="relative h-56 bg-gradient-to-br from-sky-200 to-teal-200 overflow-hidden">
            <?php if ($hotel['image_url']): ?>
              <img src="<?= e($hotel['image_url']) ?>" alt="<?= e($hotel['name']) ?>" class="w-full h-full object-cover" onerror="this.style.display='none'">
            <?php endif; ?>
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-3 left-3">
              <span class="bg-white/90 text-sky-700 text-xs font-bold px-2 py-1 rounded-full"><?= $hotel['city'] ?>, <?= $hotel['country'] ?></span>
            </div>
            <div class="absolute top-3 right-3 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">
              <?= formatPrice($hotel['price_per_night']) ?>/night
            </div>
          </div>
          <div class="p-5">
            <div class="flex items-start justify-between mb-2">
              <h3 class="font-bold text-gray-900 text-lg leading-tight"><?= e($hotel['name']) ?></h3>
            </div>
            <div class="flex items-center gap-1 mb-3"><?= starRating($hotel['star_rating']) ?></div>
            <?php if ($hotel['amenities']): ?>
              <div class="flex flex-wrap gap-1 mb-4">
                <?php foreach (array_slice(explode(',', $hotel['amenities']), 0, 3) as $am): ?>
                  <span class="bg-sky-50 text-sky-600 text-xs px-2 py-1 rounded-full"><?= e(trim($am)) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <a href="hotels.php?city=<?= urlencode($hotel['city']) ?>" class="btn-primary btn-sm w-full justify-center">
              View Hotel
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- How It Works -->
<section class="py-20" style="background:linear-gradient(135deg,#f0f9ff,#f0fdfa)">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center mb-14">
      <span class="text-sky-600 font-semibold text-sm uppercase tracking-wider">Simple Process</span>
      <h2 class="text-4xl font-extrabold text-gray-900 mt-2">Book in 3 Easy Steps</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 relative">
      <div class="hidden md:block absolute top-16 left-1/3 right-1/3 h-0.5 bg-gradient-to-r from-sky-200 to-teal-200"></div>
      <?php
      $steps = [
        ['num'=>'1','icon'=>'fa-search','title'=>'Search','desc'=>'Enter your destination, dates, and preferences to find the best flights and hotels.','color'=>'from-sky-500 to-sky-600'],
        ['num'=>'2','icon'=>'fa-mouse-pointer','title'=>'Select & Book','desc'=>'Choose your perfect option, fill in traveler details and proceed to payment.','color'=>'from-teal-500 to-teal-600'],
        ['num'=>'3','icon'=>'fa-check-circle','title'=>'Fly & Enjoy','desc'=>'Upload payment proof, get approval, and receive your confirmed itinerary.','color'=>'from-orange-500 to-orange-600'],
      ];
      foreach ($steps as $s): ?>
        <div class="text-center relative">
          <div class="w-20 h-20 rounded-2xl bg-gradient-to-br <?= $s['color'] ?> flex items-center justify-center mx-auto mb-6 shadow-lg float-anim">
            <i class="fas <?= $s['icon'] ?> text-white text-3xl"></i>
          </div>
          <div class="absolute -top-3 -right-3 md:left-auto left-1/2 w-8 h-8 bg-sky-100 text-sky-700 rounded-full flex items-center justify-center font-extrabold text-sm"><?= $s['num'] ?></div>
          <h3 class="text-xl font-bold text-gray-900 mb-3"><?= $s['title'] ?></h3>
          <p class="text-gray-500 leading-relaxed"><?= $s['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA Banner -->
<section class="py-20 hero-section">
  <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
    <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Ready to Explore the World?</h2>
    <p class="text-sky-100 text-xl mb-8">Join thousands of happy travelers and book your next adventure today.</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="flights.php" class="bg-white text-sky-700 font-bold px-8 py-4 rounded-2xl hover:shadow-xl transition-all">
        <i class="fas fa-plane mr-2"></i>Search Flights
      </a>
      <a href="hotels.php" class="bg-transparent border-2 border-white text-white font-bold px-8 py-4 rounded-2xl hover:bg-white/10 transition-all">
        <i class="fas fa-hotel mr-2"></i>Browse Hotels
      </a>
    </div>
  </div>
</section>

<script>
$(function() {
  // Passenger dropdown toggle
  $('#pax-trigger').on('click', function(e) {
    e.stopPropagation();
    $('#pax-dropdown').toggleClass('hidden');
  });
  $('#pax-done').on('click', function() {
    $('#pax-dropdown').addClass('hidden');
  });
  $(document).on('click', function(e) {
    if (!$(e.target).closest('#pax-trigger, #pax-dropdown').length) {
      $('#pax-dropdown').addClass('hidden');
    }
  });
});
</script>

<?php include 'includes/footer.php'; ?>
