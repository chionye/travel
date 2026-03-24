<?php
require_once __DIR__ . '/functions.php';

function sendEmail($to, $subject, $htmlBody, $toName = '') {
    $fromEmail = getSetting('smtp_from') ?: getSetting('contact_email', 'noreply@example.com');
    $fromName  = getSetting('site_name', 'SkyWave Travel');

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: {$fromEmail}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $toHeader = $toName ? "{$toName} <{$to}>" : $to;
    return mail($toHeader, $subject, $htmlBody, $headers);
}

function emailTemplate($title, $body, $bookingRef = '') {
    $siteName = getSetting('site_name', 'SkyWave Travel');
    $siteUrl  = getSetting('site_url', SITE_URL);
    $year     = date('Y');
    $refBadge = $bookingRef ? '<p style="background:#e0f2fe;border-radius:8px;padding:12px 16px;font-size:18px;font-weight:bold;color:#0369a1;text-align:center;margin:20px 0;">Booking Reference: ' . $bookingRef . '</p>' : '';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f0f9ff;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9ff;padding:30px 0;">
<tr><td align="center">
  <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
    <!-- Header -->
    <tr>
      <td style="background:linear-gradient(135deg,#0369a1,#0891b2);padding:30px;text-align:center;">
        <h1 style="color:#ffffff;margin:0;font-size:26px;font-weight:800;letter-spacing:-0.5px;">&#9992; {$siteName}</h1>
        <p style="color:#bae6fd;margin:6px 0 0;font-size:14px;">Your Journey, Our Passion</p>
      </td>
    </tr>
    <!-- Body -->
    <tr>
      <td style="padding:32px 36px;">
        <h2 style="color:#0f172a;margin:0 0 16px;font-size:22px;">{$title}</h2>
        {$refBadge}
        {$body}
      </td>
    </tr>
    <!-- Footer -->
    <tr>
      <td style="background:#f8fafc;padding:20px 36px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="color:#64748b;font-size:12px;margin:0;">
          &copy; {$year} {$siteName}. All rights reserved.<br>
          <a href="{$siteUrl}" style="color:#0369a1;text-decoration:none;">{$siteUrl}</a>
        </p>
      </td>
    </tr>
  </table>
</td></tr>
</table>
</body>
</html>
HTML;
}

function sendBookingConfirmationEmail($booking, $flight = null, $hotel = null) {
    $siteName  = getSetting('site_name', 'SkyWave Travel');
    $symbol    = getSetting('currency_symbol', '$');
    $ref       = $booking['booking_ref'];
    $siteUrl   = getSetting('site_url', SITE_URL);

    $details = '<table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;margin:16px 0;">';
    if ($flight) {
        $details .= '<tr style="background:#f8fafc;"><td colspan="2" style="font-weight:bold;color:#0369a1;padding:10px;">&#9992; Flight Details</td></tr>';
        $details .= '<tr><td style="color:#64748b;width:40%;padding:8px;">Flight</td><td style="font-weight:600;">' . e($flight['airline']) . ' ' . e($flight['flight_number']) . '</td></tr>';
        $details .= '<tr style="background:#f8fafc;"><td style="color:#64748b;padding:8px;">Route</td><td style="font-weight:600;">' . e($flight['origin_code']) . ' &rarr; ' . e($flight['destination_code']) . '</td></tr>';
        $details .= '<tr><td style="color:#64748b;padding:8px;">Date</td><td style="font-weight:600;">' . date('D, M j, Y', strtotime($flight['departure_date'])) . '</td></tr>';
        $details .= '<tr style="background:#f8fafc;"><td style="color:#64748b;padding:8px;">Departure</td><td style="font-weight:600;">' . date('g:i A', strtotime($flight['departure_time'])) . '</td></tr>';
        $details .= '<tr><td style="color:#64748b;padding:8px;">Class</td><td style="font-weight:600;">' . ucfirst($flight['class']) . '</td></tr>';
    }
    if ($hotel) {
        $details .= '<tr style="background:#f8fafc;"><td colspan="2" style="font-weight:bold;color:#0369a1;padding:10px;">&#127963; Hotel Details</td></tr>';
        $details .= '<tr><td style="color:#64748b;width:40%;padding:8px;">Hotel</td><td style="font-weight:600;">' . e($hotel['name']) . '</td></tr>';
        $details .= '<tr style="background:#f8fafc;"><td style="color:#64748b;padding:8px;">Location</td><td style="font-weight:600;">' . e($hotel['city']) . ', ' . e($hotel['country']) . '</td></tr>';
        $details .= '<tr><td style="color:#64748b;padding:8px;">Check-in</td><td style="font-weight:600;">' . date('D, M j, Y', strtotime($booking['check_in'])) . '</td></tr>';
        $details .= '<tr style="background:#f8fafc;"><td style="color:#64748b;padding:8px;">Check-out</td><td style="font-weight:600;">' . date('D, M j, Y', strtotime($booking['check_out'])) . '</td></tr>';
        $details .= '<tr><td style="color:#64748b;padding:8px;">Nights</td><td style="font-weight:600;">' . $booking['nights'] . '</td></tr>';
    }
    $details .= '<tr style="border-top:2px solid #0369a1;"><td style="color:#0369a1;font-weight:bold;padding:12px;">Total Amount</td><td style="color:#0369a1;font-weight:bold;font-size:18px;">' . $symbol . number_format($booking['total_amount'], 2) . '</td></tr>';
    $details .= '</table>';

    $body = '<p style="color:#475569;line-height:1.6;">Dear <strong>' . e($booking['contact_name']) . '</strong>,</p>
<p style="color:#475569;line-height:1.6;">Great news! Your payment has been verified and your booking is now <strong style="color:#16a34a;">confirmed</strong>. We are excited to be part of your journey!</p>
' . $details . '
<p style="color:#475569;line-height:1.6;">Please save your booking reference for future correspondence. You can view your full itinerary by logging into your account at <a href="' . $siteUrl . '" style="color:#0369a1;">' . $siteUrl . '</a>.</p>
<p style="color:#475569;line-height:1.6;">Have a wonderful trip!<br><strong>' . e($siteName) . ' Team</strong></p>';

    $html = emailTemplate('Booking Confirmed! &#127881;', $body, $ref);
    return sendEmail($booking['contact_email'], 'Booking Confirmed - ' . $ref . ' | ' . $siteName, $html, $booking['contact_name']);
}

function sendPaymentReceivedEmail($booking) {
    $siteName = getSetting('site_name', 'SkyWave Travel');
    $ref      = $booking['booking_ref'];
    $body = '<p style="color:#475569;line-height:1.6;">Dear <strong>' . e($booking['contact_name']) . '</strong>,</p>
<p style="color:#475569;line-height:1.6;">We have received your payment proof for booking <strong>' . e($ref) . '</strong>. Our team is currently reviewing it and will confirm your booking within <strong>24 hours</strong>.</p>
<p style="color:#475569;line-height:1.6;">You will receive another email once your payment is approved and your booking is confirmed.</p>
<p style="color:#475569;line-height:1.6;">Thank you for choosing ' . e($siteName) . '!</p>';
    $html = emailTemplate('Payment Proof Received', $body, $ref);
    return sendEmail($booking['contact_email'], 'Payment Proof Received - ' . $ref, $html, $booking['contact_name']);
}

function sendPaymentDeclinedEmail($booking, $reason = '') {
    $siteName = getSetting('site_name', 'SkyWave Travel');
    $siteUrl  = getSetting('site_url', SITE_URL);
    $ref      = $booking['booking_ref'];
    $reasonText = $reason ? '<p style="background:#fee2e2;border-left:4px solid #dc2626;padding:12px 16px;border-radius:4px;color:#991b1b;"><strong>Reason:</strong> ' . e($reason) . '</p>' : '';
    $body = '<p style="color:#475569;line-height:1.6;">Dear <strong>' . e($booking['contact_name']) . '</strong>,</p>
<p style="color:#475569;line-height:1.6;">Unfortunately, your payment for booking <strong>' . e($ref) . '</strong> could not be verified.</p>
' . $reasonText . '
<p style="color:#475569;line-height:1.6;">Please <a href="' . $siteUrl . '/payment.php?ref=' . urlencode($ref) . '" style="color:#0369a1;">resubmit your payment proof</a> or contact our support team for assistance.</p>
<p style="color:#475569;line-height:1.6;">We apologize for any inconvenience.<br><strong>' . e($siteName) . ' Team</strong></p>';
    $html = emailTemplate('Payment Verification Failed', $body, $ref);
    return sendEmail($booking['contact_email'], 'Payment Issue - ' . $ref, $html, $booking['contact_name']);
}
?>
