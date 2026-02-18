<?php
/**
 * Database Configuration
 * SAM 2026 - Role-Based Access Control System
 */

// Database configuration
define('DB_HOST', '172.16.2.141');
define('DB_USER', 'pendaftar');
define('DB_PASS', 'Pend@ftar@2025?');
define('DB_NAME', 'esportsdb');
define('DB_CHARSET', 'utf8mb4');

// Sybase ODBC defaults (can be overridden by env vars)
define('SYBASE_ESPORTS_DSN', 'SYBASE_ESPORTS_STAF');
define('SYBASE_ESPORTS_USER', 'expdir');
define('SYBASE_ESPORTS_PASS', 'X@directory1');ak

// Database connection class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Helper function to get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}

// Helper function to get Sybase (ODBC) connection
function getSybaseConnection($dsn = null, $user = null, $pass = null) {
    $dsn = $dsn ?: (getenv('SYBASE_ESPORTS_DSN') ?: SYBASE_ESPORTS_DSN);
    $user = $user ?: (getenv('SYBASE_ESPORTS_USER') ?: SYBASE_ESPORTS_USER);
    $pass = ($pass !== null) ? $pass : (getenv('SYBASE_ESPORTS_PASS') ?: SYBASE_ESPORTS_PASS);

    if (!function_exists('odbc_connect')) {
        throw new Exception('ODBC extension tidak tersedia pada server PHP.');
    }

    $conn = @odbc_connect($dsn, $user, $pass);
    if (!$conn) {
        $code = function_exists('odbc_error') ? @odbc_error() : '';
        $msg = function_exists('odbc_errormsg') ? @odbc_errormsg() : '';
        throw new Exception('Sambungan Sybase gagal' . ($code ? ' [' . $code . ']' : '') . ($msg ? ': ' . $msg : '.'));
    }

    return $conn;
}

function getSybaseOdbcErrorMessage($conn = null) {
    if (function_exists('odbc_errormsg')) {
        try {
            $msg = $conn ? @odbc_errormsg($conn) : @odbc_errormsg();
            if ($msg) return (string)$msg;
        } catch (Throwable $e) {
            // ignore and fallback
        }
    }
    return 'Ralat ODBC tidak tersedia (pastikan extension ODBC aktif).';
}

function sybaseOdbcPrepare($conn, $sql) {
    if (!function_exists('odbc_prepare')) {
        throw new Exception('Fungsi ODBC tidak tersedia: odbc_prepare');
    }
    return @call_user_func('odbc_prepare', $conn, $sql);
}

function sybaseOdbcExecute($stmt, array $params = []) {
    if (!function_exists('odbc_execute')) {
        throw new Exception('Fungsi ODBC tidak tersedia: odbc_execute');
    }
    return @call_user_func('odbc_execute', $stmt, $params);
}

function sybaseOdbcExec($conn, $sql) {
    if (!function_exists('odbc_exec')) {
        throw new Exception('Fungsi ODBC tidak tersedia: odbc_exec');
    }
    return @call_user_func('odbc_exec', $conn, $sql);
}

function sybaseOdbcFetchArray($stmt) {
    if (!function_exists('odbc_fetch_array')) {
        throw new Exception('Fungsi ODBC tidak tersedia: odbc_fetch_array');
    }
    return @call_user_func('odbc_fetch_array', $stmt);
}

function sybaseOdbcClose($conn) {
    if (function_exists('odbc_close')) {
        @call_user_func('odbc_close', $conn);
    }
}
