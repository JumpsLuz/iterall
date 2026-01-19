<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Load .env if present; ignore if not (Railway uses env vars)
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();

        // Prefer Railway/MySQL variables, then generic DB_* fallbacks
        $host = $_ENV['MYSQLHOST'] ?? $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['MYSQLPORT'] ?? $_ENV['DB_PORT'] ?? 3306;
        $db   = $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME'] ?? 'iterall_db';
        $user = $_ENV['MYSQLUSER'] ?? $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASS'] ?? '';

        // Allow DATABASE_URL override (mysql://user:pass@host:port/db)
        if (!empty($_ENV['DATABASE_URL'])) {
            $url = parse_url($_ENV['DATABASE_URL']);
            if ($url && !empty($url['scheme'])) {
                $host = $url['host'] ?? $host;
                $port = $url['port'] ?? $port;
                $user = $url['user'] ?? $user;
                $pass = $url['pass'] ?? $pass;
                $db   = ltrim($url['path'] ?? '', '/');
            }
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log('Error de conexión: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }
}
