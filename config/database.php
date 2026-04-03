<?php
// ============================================================
// GROCEESARY – Unified Database Wrapper (PDO)
// ============================================================
class Database {
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    private function __construct() {
        try {
            // ---- Environment Detection for DB -----------------------
            if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
                $dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../database/groceesary.sqlite';
                
                // Create directory if not exists
                $dbDir = dirname($dbPath);
                if (!is_dir($dbDir)) @mkdir($dbDir, 0777, true);

                $this->conn = new PDO("sqlite:$dbPath");
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                // MySQL mode
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $this->conn = new PDO($dsn, DB_USER, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
        } catch (PDOException $e) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                die('<h3>DB Connection Error: ' . $e->getMessage() . '</h3>');
            }
            die('Service temporarily unavailable.');
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /** Run a raw query and return array of rows */
    public function query(string $sql): array {
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError($e->getMessage(), $sql);
            return [];
        }
    }

    /** Return single row */
    public function queryOne(string $sql): ?array {
        $rows = $this->query($sql);
        return $rows[0] ?? null;
    }

    /** Prepared statement – returns array of rows */
    public function prepare(string $sql, string $types, ...$params): array {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError($e->getMessage(), $sql);
            return [];
        }
    }

    /** Prepared statement – return single row */
    public function prepareOne(string $sql, string $types, ...$params): ?array {
        $rows = $this->prepare($sql, $types, ...$params);
        return $rows[0] ?? null;
    }

    /** Prepared statement for INSERT / UPDATE / DELETE – returns affected rows */
    public function execute(string $sql, string $types, ...$params): int {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e->getMessage(), $sql);
            return 0;
        }
    }

    /** Get last inserted ID */
    public function lastInsertId(): int {
        return (int) $this->conn->lastInsertId();
    }

    /** Escape helper for direct SQL (use with caution, prefer prepared stmts) */
    public function escape(string $value): string {
        $trimmed = trim($this->conn->quote($value), "'");
        return $trimmed;
    }

    public function beginTransaction(): void  { $this->conn->beginTransaction(); }
    public function commit(): void            { $this->conn->commit(); }
    public function rollback(): void          { $this->conn->rollback(); }

    private function logError(string $error, string $sql): void {
        $msg = date('Y-m-d H:i:s') . " | SQL Error: $error | Query: $sql\n";
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
        @file_put_contents($logDir . 'db_errors.log', $msg, FILE_APPEND);
        
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<pre style='color:red;'>DB Error: $error\nSQL: $sql</pre>";
        }
    }

    private function __clone() {}
}
?>
