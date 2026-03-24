<?php
require_once __DIR__ . '/db.php';

// ── Settings ──────────────────────────────────────────────
function getSetting($key, $default = '') {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    try {
        $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
    return $cache[$key];
}

function getAllSettings() {
    try {
        $stmt = db()->query('SELECT setting_key, setting_value FROM settings');
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

function updateSetting($key, $value) {
    $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?');
    return $stmt->execute([$key, $value, $value]);
}

// ── Booking Reference ──────────────────────────────────────
function generateBookingRef() {
    do {
        $ref = 'SKY' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 7));
        $stmt = db()->prepare('SELECT id FROM bookings WHERE booking_ref = ?');
        $stmt->execute([$ref]);
    } while ($stmt->fetch());
    return $ref;
}

// ── Currency ───────────────────────────────────────────────
function formatPrice($amount) {
    $symbol = getSetting('currency_symbol', '$');
    return $symbol . number_format((float)$amount, 2);
}

// ── Date / Time ────────────────────────────────────────────
function formatDate($date) {
    return $date ? date('D, M j, Y', strtotime($date)) : '';
}

function formatTime($time) {
    return $time ? date('g:i A', strtotime($time)) : '';
}

function nightsBetween($checkin, $checkout) {
    $d1 = new DateTime($checkin);
    $d2 = new DateTime($checkout);
    return max(1, $d2->diff($d1)->days);
}

// ── Stars ──────────────────────────────────────────────────
function starRating($n) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<i class="fas fa-star ' . ($i <= $n ? 'text-yellow-400' : 'text-gray-300') . '"></i>';
    }
    return $html;
}

// ── Status Badges ──────────────────────────────────────────
function paymentBadge($status) {
    $map = [
        'unpaid'   => ['bg-gray-100 text-gray-700',   'Unpaid'],
        'pending'  => ['bg-yellow-100 text-yellow-700','Pending Review'],
        'approved' => ['bg-green-100 text-green-700',  'Approved'],
        'declined' => ['bg-red-100 text-red-700',      'Declined'],
    ];
    [$cls, $label] = $map[$status] ?? ['bg-gray-100 text-gray-700', ucfirst($status)];
    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $cls . '">' . $label . '</span>';
}

function bookingBadge($status) {
    $map = [
        'pending'   => ['bg-yellow-100 text-yellow-700', 'Pending'],
        'confirmed' => ['bg-green-100 text-green-700',   'Confirmed'],
        'cancelled' => ['bg-red-100 text-red-700',       'Cancelled'],
    ];
    [$cls, $label] = $map[$status] ?? ['bg-gray-100 text-gray-700', ucfirst($status)];
    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $cls . '">' . $label . '</span>';
}

// ── Class Badge ────────────────────────────────────────────
function classBadge($class) {
    $map = [
        'economy'  => 'bg-sky-100 text-sky-700',
        'business' => 'bg-purple-100 text-purple-700',
        'first'    => 'bg-amber-100 text-amber-700',
    ];
    $cls = $map[$class] ?? 'bg-gray-100 text-gray-700';
    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $cls . '">' . ucfirst($class) . '</span>';
}

// ── Flash Messages ─────────────────────────────────────────
function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function renderFlash() {
    $f = getFlash();
    if (!$f) return '';
    $colors = [
        'success' => 'bg-green-50 border-green-400 text-green-800',
        'error'   => 'bg-red-50 border-red-400 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
        'info'    => 'bg-sky-50 border-sky-400 text-sky-800',
    ];
    $icons = [
        'success' => 'fa-check-circle',
        'error'   => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info'    => 'fa-info-circle',
    ];
    $cls  = $colors[$f['type']] ?? $colors['info'];
    $icon = $icons[$f['type']] ?? $icons['info'];
    return '<div class="border-l-4 p-4 rounded-lg mb-6 ' . $cls . '">
        <div class="flex items-center gap-2">
            <i class="fas ' . $icon . '"></i>
            <span>' . htmlspecialchars($f['message']) . '</span>
        </div>
    </div>';
}

// ── CSRF ───────────────────────────────────────────────────
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        flash('error', 'Invalid request. Please try again.');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// ── File Upload ────────────────────────────────────────────
function uploadFile($file, $subdir = 'payment_proofs') {
    $allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp','application/pdf'];
    if (!in_array($file['type'], $allowed)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP, PDF allowed.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'File too large. Maximum 5MB allowed.'];
    }
    $dir = UPLOAD_PATH . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('proof_') . '.' . strtolower($ext);
    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return ['path' => $subdir . '/' . $name, 'url' => UPLOAD_URL . $subdir . '/' . $name];
    }
    return ['error' => 'Upload failed. Check folder permissions.'];
}

// ── Sanitize ───────────────────────────────────────────────
function clean($val) {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function e($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

// ── Pagination ─────────────────────────────────────────────
function paginate($total, $perPage, $current, $url) {
    $pages = ceil($total / $perPage);
    if ($pages <= 1) return '';
    $html = '<div class="flex items-center gap-1 mt-6">';
    for ($i = 1; $i <= $pages; $i++) {
        $active = $i === $current ? 'bg-sky-600 text-white' : 'bg-white text-gray-700 hover:bg-sky-50';
        $html .= '<a href="' . $url . '&page=' . $i . '" class="px-3 py-2 rounded border text-sm ' . $active . '">' . $i . '</a>';
    }
    $html .= '</div>';
    return $html;
}
?>
