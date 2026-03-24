<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:Arial,sans-serif;padding:30px;background:#fee2e2;color:#991b1b;border-radius:12px;margin:40px auto;max-width:600px;">
                <h2 style="margin:0 0 10px">&#x26A0; Database Connection Failed</h2>
                <p>Could not connect to the database. Please check your <code>includes/config.php</code> settings or run the <a href="/install/">installer</a> again.</p>
                <details><summary>Error Details</summary><code style="font-size:12px">' . htmlspecialchars($e->getMessage()) . '</code></details></div>');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

function db() {
    return Database::getInstance()->getConnection();
}
?>
