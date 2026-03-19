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

    /**
     * Get all users for admin management
     *
     * @return array Array of users
     */
    public function getAllUsers($limit = null, $offset = 0) {
        $sql = "SELECT id, login, nom, prenom, role, date_creation
                FROM utilisateurs
                ORDER BY nom, prenom";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Count total users
     *
     * @return int Total users
     */
    public function countAllUsers() {
        $sql = "SELECT COUNT(*) FROM utilisateurs";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Create a new user
     *
     * @param string $login Login
     * @param string $password Plain password
     * @param string $nom Last name
     * @param string $prenom First name
     * @param string $role Role
     * @return bool Success status
     */
    public function createUser($login, $password, $nom, $prenom, $role) {
        $sql = "INSERT INTO utilisateurs (login, mot_de_passe, nom, prenom, role)
                VALUES (:login, :mot_de_passe, :nom, :prenom, :role)";

        $stmt = $this->db->prepare($sql);
        $motDePasseHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->bindParam(':mot_de_passe', $motDePasseHash, PDO::PARAM_STR);
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Update an existing user
     *
     * @param int $userId User ID
     * @param string $login Login
     * @param string $nom Last name
     * @param string $prenom First name
     * @param string $role Role
     * @param string|null $password Optional plain password
     * @return bool Success status
     */
    public function updateUser($userId, $login, $nom, $prenom, $role, $password = null) {
        if ($password !== null && $password !== '') {
            $sql = "UPDATE utilisateurs
                    SET login = :login,
                        mot_de_passe = :mot_de_passe,
                        nom = :nom,
                        prenom = :prenom,
                        role = :role
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $motDePasseHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':mot_de_passe', $motDePasseHash, PDO::PARAM_STR);
        } else {
            $sql = "UPDATE utilisateurs
                    SET login = :login,
                        nom = :nom,
                        prenom = :prenom,
                        role = :role
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
        }

        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
        $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Delete an existing user
     *
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteUser($userId) {
        $sql = "DELETE FROM utilisateurs WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Check whether a login already exists
     *
     * @param string $login Login
     * @param int|null $excludeUserId User ID to exclude from the check
     * @return bool True if the login exists, false otherwise
     */
    public function loginExists($login, $excludeUserId = null) {
        $sql = "SELECT COUNT(*)
                FROM utilisateurs
                WHERE login = :login";

        if ($excludeUserId !== null) {
            $sql .= " AND id != :exclude_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);

        if ($excludeUserId !== null) {
            $stmt->bindParam(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }
}
