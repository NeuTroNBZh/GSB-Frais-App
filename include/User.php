<?php
/**
 * User model class
 * Handles user authentication and role management
 * 
 * @author GSB
 * @version 1.0
 */

class User {
    /**
     * @var PDO Database connection
     */
    private $db;
    
    /**
     * User roles constants
     */
    const ROLE_VISITOR = 'visiteur';
    const ROLE_ACCOUNTANT = 'comptable';
    const ROLE_ADMIN = 'admin';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Authenticate user with login and password
     * 
     * @param string $login User login
     * @param string $password User password
     * @return array|bool User data array if success, false otherwise
     */
    public function authenticate($login, $password) {
        $sql = "SELECT id, login, nom, prenom, role, mot_de_passe 
                FROM utilisateurs 
                WHERE login = :login";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            unset($user['mot_de_passe']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|bool User data array if found, false otherwise
     */
    public function getUserById($userId) {
        $sql = "SELECT id, login, nom, prenom, role 
                FROM utilisateurs 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Check if user has specific role
     * 
     * @param string $role Role to check
     * @param array $user User data array
     * @return bool True if user has role, false otherwise
     */
    public function hasRole($role, $user) {
        return isset($user['role']) && $user['role'] === $role;
    }
    
    /**
     * Get all visitors for accountant/admin
     * 
     * @return array Array of visitors
     */
    public function getAllVisitors() {
        $sql = "SELECT id, login, nom, prenom 
                FROM utilisateurs 
                WHERE role = :role 
                ORDER BY nom, prenom";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role', self::ROLE_VISITOR, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
