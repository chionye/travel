<?php
$siteName  = getSetting('site_name', 'SkyWave Travel');
$siteEmail = getSetting('contact_email');
$sitePhone = getSetting('contact_phone');
$fb = getSetting('social_facebook');
$ig = getSetting('social_instagram');
$tw = getSetting('social_twitter');
?>
<footer class="bg-slate-900 text-slate-300 pt-16 pb-8 mt-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

      <!-- Brand -->
      <div class="lg:col-span-1">
        <div class="flex items-center gap-2 mb-4">
          <div class="w-10 h-10 bg-gradient-to-br from-sky-500 to-teal-500 rounded-xl flex items-center justify-center">
            <i class="fas fa-paper-plane text-white"></i>
          </div>
          <span class="font-extrabold text-xl text-white"><?= e($siteName) ?></span>
        </div>
        <p class="text-sm text-slate-400 leading-relaxed mb-4">Your trusted partner for seamless travel experiences. Book flights, hotels, and more with confidence.</p>
        <div class="flex gap-3">
          <?php if ($fb): ?><a href="<?= e($fb) ?>" target="_blank" class="w-9 h-9 bg-slate-800 hover:bg-sky-600 rounded-lg flex items-center justify-center transition"><i class="fab fa-facebook-f text-sm"></i></a><?php endif; ?>
          <?php if ($ig): ?><a href="<?= e($ig) ?>" target="_blank" class="w-9 h-9 bg-slate-800 hover:bg-pink-600 rounded-lg flex items-center justify-center transition"><i class="fab fa-instagram text-sm"></i></a><?php endif; ?>
          <?php if ($tw): ?><a href="<?= e($tw) ?>" target="_blank" class="w-9 h-9 bg-slate-800 hover:bg-sky-500 rounded-lg flex items-center justify-center transition"><i class="fab fa-twitter text-sm"></i></a><?php endif; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="font-bold text-white mb-4">Quick Links</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="<?= SITE_URL ?>" class="hover:text-sky-400 transition">Home</a></li>
          <li><a href="<?= SITE_URL ?>/flights.php" class="hover:text-sky-400 transition">Search Flights</a></li>
          <li><a href="<?= SITE_URL ?>/hotels.php" class="hover:text-sky-400 transition">Search Hotels</a></li>
          <li><a href="<?= SITE_URL ?>/dashboard.php" class="hover:text-sky-400 transition">My Bookings</a></li>
        </ul>
      </div>

      <!-- Support -->
      <div>
        <h4 class="font-bold text-white mb-4">Support</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="hover:text-sky-400 transition">FAQ</a></li>
          <li><a href="#" class="hover:text-sky-400 transition">Cancellation Policy</a></li>
          <li><a href="#" class="hover:text-sky-400 transition">Privacy Policy</a></li>
          <li><a href="#" class="hover:text-sky-400 transition">Terms &amp; Conditions</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div>
        <h4 class="font-bold text-white mb-4">Contact Us</h4>
        <ul class="space-y-3 text-sm">
          <?php if ($siteEmail): ?>
            <li class="flex items-start gap-2"><i class="fas fa-envelope text-sky-400 mt-0.5 w-4"></i><a href="mailto:<?= e($siteEmail) ?>" class="hover:text-sky-400 transition break-all"><?= e($siteEmail) ?></a></li>
          <?php endif; ?>
          <?php if ($sitePhone): ?>
            <li class="flex items-center gap-2"><i class="fas fa-phone text-sky-400 w-4"></i><a href="tel:<?= e($sitePhone) ?>" class="hover:text-sky-400 transition"><?= e($sitePhone) ?></a></li>
          <?php endif; ?>
          <li class="flex items-center gap-2"><i class="fas fa-clock text-sky-400 w-4"></i><span>24/7 Customer Support</span></li>
        </ul>
      </div>
    </div>

    <!-- Bottom -->
    <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-sm text-slate-500">&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.</p>
      <div class="flex items-center gap-4 text-slate-500 text-sm">
        <span class="flex items-center gap-1"><i class="fas fa-shield-alt text-sky-500"></i> Secure Payments</span>
        <span class="flex items-center gap-1"><i class="fas fa-lock text-sky-500"></i> SSL Encrypted</span>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
