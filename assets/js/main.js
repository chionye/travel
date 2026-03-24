$(function () {

  // Mobile menu toggle
  $('#mobileMenuBtn').on('click', function () {
    $('#mobileMenu').toggleClass('hidden');
  });

  // Navbar shadow on scroll
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 10) {
      $('#navbar').addClass('shadow-md');
    } else {
      $('#navbar').removeClass('shadow-md');
    }
  });

  // Search tabs on home page
  $('.search-tab').on('click', function () {
    var tab = $(this).data('tab');
    $('.search-tab').removeClass('active');
    $(this).addClass('active');
    $('.search-panel').addClass('hidden');
    $('#panel-' + tab).removeClass('hidden');
  });

  // Initialize flatpickr date pickers
  if ($.fn) {
    var today = new Date().toISOString().split('T')[0];
    flatpickr('.datepicker', {
      minDate: today,
      dateFormat: 'Y-m-d',
      disableMobile: false
    });
    flatpickr('.datepicker-checkin', {
      minDate: today,
      dateFormat: 'Y-m-d',
      onChange: function (selectedDates, dateStr) {
        var co = document.querySelector('.datepicker-checkout');
        if (co && co._flatpickr) {
          co._flatpickr.set('minDate', dateStr);
        }
      }
    });
    flatpickr('.datepicker-checkout', {
      minDate: today,
      dateFormat: 'Y-m-d'
    });
  }

  // Payment method selection
  $('.payment-option').on('click', function () {
    $('.payment-option').removeClass('selected');
    $(this).addClass('selected');
    var method = $(this).data('method');
    $('.payment-details').addClass('hidden');
    $('#details-' + method).removeClass('hidden');
    $('input[name="payment_method"]').val(method);
    if (method === 'crypto') {
      $('.crypto-sub').first().trigger('click');
    }
  });

  // Crypto sub-selection
  $('.crypto-sub').on('click', function () {
    $('.crypto-sub').removeClass('ring-2 ring-sky-500 bg-sky-50');
    $(this).addClass('ring-2 ring-sky-500 bg-sky-50');
    var crypto = $(this).data('crypto');
    $('input[name="crypto_type"]').val(crypto);
    $('.crypto-wallet-display').addClass('hidden');
    $('#wallet-' + crypto).removeClass('hidden');
  });

  // Copy wallet address
  $(document).on('click', '.copy-btn', function () {
    var text = $(this).data('copy');
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(function () {
        showToast('Address copied!', 'success');
      });
    } else {
      var el = $('<textarea>').val(text).appendTo('body').select();
      document.execCommand('copy');
      el.remove();
      showToast('Copied!', 'success');
    }
  });

  // File upload preview
  $('#proof_upload').on('change', function () {
    var file = this.files[0];
    if (!file) return;
    var maxSize = 5 * 1024 * 1024;
    if (file.size > maxSize) {
      showToast('File too large. Max 5MB.', 'error');
      $(this).val('');
      return;
    }
    var allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp','application/pdf'];
    if (!allowed.includes(file.type)) {
      showToast('Invalid file type.', 'error');
      $(this).val('');
      return;
    }
    if (file.type.startsWith('image/')) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#proof-preview').attr('src', e.target.result).removeClass('hidden');
        $('#proof-pdf-icon').addClass('hidden');
      };
      reader.readAsDataURL(file);
    } else {
      $('#proof-preview').addClass('hidden');
      $('#proof-pdf-icon').removeClass('hidden');
    }
    $('#proof-file-name').text(file.name);
    $('#proof-preview-box').removeClass('hidden');
  });

  // Passenger count
  function updatePassengerDisplay() {
    var adults   = parseInt($('#adults-val').text()) || 1;
    var children = parseInt($('#children-val').text()) || 0;
    var total    = adults + children;
    var label    = adults + ' Adult' + (adults > 1 ? 's' : '');
    if (children > 0) label += ', ' + children + ' Child' + (children > 1 ? 'ren' : '');
    $('#passenger-display').text(label);
    $('input[name="adults"]').val(adults);
    $('input[name="children"]').val(children);
  }

  $(document).on('click', '.pax-btn', function () {
    var type   = $(this).data('type');
    var action = $(this).data('action');
    var el     = $('#' + type + '-val');
    var val    = parseInt(el.text()) || 0;
    var min    = type === 'adults' ? 1 : 0;
    var max    = 9;
    if (action === 'inc' && val < max) el.text(val + 1);
    if (action === 'dec' && val > min) el.text(val - 1);
    updatePassengerDisplay();
  });

  // Booking type toggle
  $('input[name="booking_type"]').on('change', function () {
    var val = $(this).val();
    $('#section-flight').toggleClass('hidden', val === 'hotel');
    $('#section-hotel').toggleClass('hidden', val === 'flight');
    recalcTotal();
  });

  // Price calculation
  window.recalcTotal = function () {
    var flightPrice = parseFloat($('#flight-price').val()) || 0;
    var hotelPrice  = parseFloat($('#hotel-price').val()) || 0;
    var nights      = parseInt($('#nights-count').val()) || 1;
    var adults      = parseInt($('input[name="adults"]').val()) || 1;
    var type        = $('input[name="booking_type"]:checked').val();
    var total       = 0;
    if (type === 'flight' || type === 'both') total += flightPrice * adults;
    if (type === 'hotel'  || type === 'both') total += hotelPrice * nights;
    $('#total-display').text('$' + total.toFixed(2));
    $('#total-hidden').val(total.toFixed(2));
  };

  // Toast
  window.showToast = function (message, type) {
    type = type || 'info';
    var colors = { success: '#10b981', error: '#ef4444', info: '#0ea5e9', warning: '#f59e0b' };
    var icons  = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-triangle' };
    var toast  = $('<div class="fixed bottom-4 right-4 z-[9999] flex items-center gap-3 px-5 py-4 rounded-xl text-white shadow-xl text-sm font-medium" style="background:' + colors[type] + ';min-width:240px;">' +
      '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + message + '</span></div>');
    $('body').append(toast);
    setTimeout(function () { toast.fadeOut(400, function () { $(this).remove(); }); }, 3000);
  };

  // Confirm dialogs
  $(document).on('click', '.confirm-action', function (e) {
    var msg = $(this).data('confirm') || 'Are you sure?';
    if (!confirm(msg)) e.preventDefault();
  });

  // Auto dismiss flash messages
  setTimeout(function () {
    $('.flash-msg').fadeOut(500, function () { $(this).remove(); });
  }, 5000);

  // Filter form live search (flights)
  $('#filter-form input, #filter-form select').on('change', function () {
    $(this).closest('form').submit();
  });

  // Admin: toggle active status
  $(document).on('change', '.toggle-active', function () {
    var id     = $(this).data('id');
    var type   = $(this).data('type');
    var active = $(this).is(':checked') ? 1 : 0;
    $.post(window.SITE_URL + '/admin/ajax.php', { action: 'toggle_active', id: id, type: type, active: active, csrf: window.CSRF })
      .done(function (r) {
        var res = typeof r === 'string' ? JSON.parse(r) : r;
        showToast(res.message || 'Updated', res.success ? 'success' : 'error');
      });
  });

});
