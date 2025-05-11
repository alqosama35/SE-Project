<?php

namespace App\Models;

class Database {
    private static ?Database $instance = null;
    private \mysqli $connection;
    private const CONNECTION_TIMEOUT = 5;
    private const MAX_RETRIES = 3;
    
    private function __construct() {
        $host = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        $database = getenv('DB_DATABASE') ?: 'museum_db';
        
        $this->connection = new \mysqli();
        $this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::CONNECTION_TIMEOUT);
        
        $retries = 0;
        while ($retries < self::MAX_RETRIES) {
            try {
                $this->connection->real_connect($host, $username, $password, $database);
                if ($this->connection->connect_error) {
                    throw new \Exception("Connection failed: " . $this->connection->connect_error);
                }
                break;
            } catch (\Exception $e) {
                $retries++;
                if ($retries === self::MAX_RETRIES) {
                    throw new \Exception("Failed to connect after " . self::MAX_RETRIES . " attempts: " . $e->getMessage());
                }
                sleep(1); // Wait before retrying
            }
        }
        
        $this->connection->set_charset("utf8mb4");
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query(string $sql, array $params = []): \mysqli_result|bool {
        try {
            if (empty($params)) {
                $result = $this->connection->query($sql);
                if ($result === false) {
                    throw new \Exception("Query failed: " . $this->connection->error);
                }
                return $result;
            }
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new \Exception("Query preparation failed: " . $this->connection->error);
            }
            
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
            }
            
            if (!$stmt->bind_param($types, ...$params)) {
                throw new \Exception("Parameter binding failed: " . $stmt->error);
            }
            
            if (!$stmt->execute()) {
                throw new \Exception("Query execution failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
        } catch (\Exception $e) {
            error_log("Database query error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function escape(string $value): string {
        try {
            return $this->connection->real_escape_string($value);
        } catch (\Exception $e) {
            error_log("Database escape error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getLastInsertId(): int {
        try {
            $id = $this->connection->insert_id;
            if ($id === 0) {
                throw new \Exception("No last insert ID available");
            }
            return $id;
        } catch (\Exception $e) {
            error_log("Database getLastInsertId error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function beginTransaction(): bool {
        try {
            if (!$this->connection->begin_transaction()) {
                throw new \Exception("Failed to begin transaction: " . $this->connection->error);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Database beginTransaction error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function commit(): bool {
        try {
            if (!$this->connection->commit()) {
                throw new \Exception("Failed to commit transaction: " . $this->connection->error);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Database commit error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function rollback(): bool {
        try {
            if (!$this->connection->rollback()) {
                throw new \Exception("Failed to rollback transaction: " . $this->connection->error);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Database rollback error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function __destruct() {
        try {
            if ($this->connection) {
                $this->connection->close();
            }
        } catch (\Exception $e) {
            error_log("Database connection close error: " . $e->getMessage());
        }
    }
    
    private function __clone() {}
    private function __wakeup() {}
} 