<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// ── User Auth ──────────────────────────────────────────────
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function currentUser() {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user) return $user;
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user;
}

function loginUser($email, $password) {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([trim($email)]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['first_name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

function registerUser($data) {
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) return ['error' => 'Email already registered.'];

    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO users (first_name, last_name, email, password, phone) VALUES (?,?,?,?,?)');
    $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $hash, $data['phone'] ?? '']);
    return ['id' => db()->lastInsertId()];
}

function logoutUser() {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
    session_destroy();
}

// ── Admin Auth ─────────────────────────────────────────────
function isAdminLoggedIn() {
    return !empty($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function loginAdmin($username, $password) {
    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([trim($username)]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_user'] = $admin['username'];
        return true;
    }
    return false;
}

function logoutAdmin() {
    unset($_SESSION['admin_id'], $_SESSION['admin_user']);
    session_destroy();
}
?>
