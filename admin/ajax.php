<?php
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
$type   = $_POST['type'] ?? '';
$active = (int)($_POST['active'] ?? 0);

if ($action === 'toggle_active' && $id) {
    if ($type === 'flight') {
        db()->prepare('UPDATE flights SET is_active=? WHERE id=?')->execute([$active, $id]);
        echo json_encode(['success'=>true,'message'=>'Flight status updated.']);
    } elseif ($type === 'hotel') {
        db()->prepare('UPDATE hotels SET is_active=? WHERE id=?')->execute([$active, $id]);
        echo json_encode(['success'=>true,'message'=>'Hotel status updated.']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Invalid type.']);
    }
} else {
    echo json_encode(['success'=>false,'message'=>'Invalid action.']);
}
