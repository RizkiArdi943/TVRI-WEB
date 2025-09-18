<?php
class Database {
    private $pdo;
    private $host = 'localhost';
    private $dbname = 'tvri_ticketing';
    private $username = 'root';
    private $password = 'password';

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    // Generic methods for CRUD operations
    public function insert($table, $data) {
        try {
            // Set timestamps
            $timestamp = date('Y-m-d H:i:s');
            $data['created_at'] = $timestamp;
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = $timestamp;
            }

            // Build SQL
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));

            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert error: " . $e->getMessage());
            return false;
        }
    }

    public function update($table, $id, $data) {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');

            $columns = array_keys($data);
            $setClause = implode(' = ?, ', $columns) . ' = ?';
            $sql = "UPDATE $table SET $setClause WHERE id = ?";

            $values = array_values($data);
            $values[] = $id;

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($table, $id) {
        try {
            $sql = "DELETE FROM $table WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            return false;
        }
    }

    public function find($table, $id) {
        try {
            $sql = "SELECT * FROM $table WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Find error: " . $e->getMessage());
            return null;
        }
    }

    public function findAll($table, $conditions = []) {
        try {
            $sql = "SELECT * FROM $table";
            $params = [];

            if (!empty($conditions)) {
                $whereClause = [];
                foreach ($conditions as $field => $value) {
                    $whereClause[] = "$field = ?";
                    $params[] = $value;
                }
                $sql .= " WHERE " . implode(' AND ', $whereClause);
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("FindAll error: " . $e->getMessage());
            return [];
        }
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return [];
        }
    }

    public function prepare($sql) {
        return new PreparedStatement($this->pdo, $sql);
    }

    // Get database connection for advanced operations
    public function getConnection() {
        return $this->pdo;
    }
}

// PreparedStatement class for PDO compatibility
class PreparedStatement {
    private $pdo;
    private $stmt;
    private $sql;

    public function __construct($pdo, $sql) {
        $this->pdo = $pdo;
        $this->sql = $sql;
        $this->stmt = $pdo->prepare($sql);
    }

    public function execute($params = []) {
        try {
            return $this->stmt->execute($params);
        } catch (PDOException $e) {
            error_log("PreparedStatement execute error: " . $e->getMessage());
            return false;
        }
    }

    public function fetch($mode = PDO::FETCH_ASSOC) {
        try {
            return $this->stmt->fetch($mode);
        } catch (PDOException $e) {
            error_log("PreparedStatement fetch error: " . $e->getMessage());
            return false;
        }
    }

    public function fetchAll($mode = PDO::FETCH_ASSOC) {
        try {
            return $this->stmt->fetchAll($mode);
        } catch (PDOException $e) {
            error_log("PreparedStatement fetchAll error: " . $e->getMessage());
            return [];
        }
    }

    public function fetchColumn($column = 0) {
        try {
            return $this->stmt->fetchColumn($column);
        } catch (PDOException $e) {
            error_log("PreparedStatement fetchColumn error: " . $e->getMessage());
            return false;
        }
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }
}

// Initialize database
$database = new Database();
$db = $database; 