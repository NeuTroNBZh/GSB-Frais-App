<?php
/**
 * PDO Database connection class
 * Singleton pattern for database access
 * 
 * @author GSB
 * @version 1.0
 */

class Database {
    /**
    * @var Database|null Singleton instance
     */
    private static $instance = null;
    
    /**
     * @var PDO PDO connection object
     */
    private $pdo;
    
    /**
     * Private constructor to prevent direct instantiation
     * Establishes PDO connection with error handling
     */
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please contact administrator.");
        }
    }
    
    /**
     * Get singleton instance of Database
     * 
     * @return Database Database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO PDO connection object
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Prevent cloning of instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of instance
     */
    private function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
