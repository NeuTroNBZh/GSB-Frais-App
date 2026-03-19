<?php
/**
 * Session management utilities
 * Handles user session and authentication state
 * 
 * @author GSB
 * @version 1.0
 */

/**
 * Start session if not already started
 * 
 * @return void
 */
function demarrerSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is authenticated
 * 
 * @return bool True if authenticated, false otherwise
 */
function estConnecte() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

/**
 * Get current user from session
 * 
 * @return array|null User data or null
 */
function getUtilisateurConnecte() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Set user in session
 * 
 * @param array $user User data
 * @return void
 */
function connecterUtilisateur($user) {
    $_SESSION['user'] = $user;
    $_SESSION['last_activity'] = time();
}

/**
 * Get or create the CSRF token stored in session
 *
 * @return string CSRF token
 */
function obtenirJetonCsrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate the submitted CSRF token
 *
 * @param string|null $token Submitted token
 * @return bool True when valid, false otherwise
 */
function verifierJetonCsrf($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Destroy session and logout user
 * 
 * @return void
 */
function deconnecterUtilisateur() {
    session_unset();
    session_destroy();
}

/**
 * Check if user has specific role
 * 
 * @param string $role Role to check
 * @return bool True if user has role, false otherwise
 */
function aLeRole($role) {
    $user = getUtilisateurConnecte();
    return $user !== null && isset($user['role']) && $user['role'] === $role;
}

/**
 * Redirect to login if not authenticated
 * 
 * @return void
 */
function requireAuthentication() {
    if (!estConnecte()) {
        header('Location: index.php?action=connexion');
        exit();
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        deconnecterUtilisateur();
        header('Location: index.php?action=connexion&erreur=' . urlencode('Session expiree apres inactivite'));
        exit();
    }

    $_SESSION['last_activity'] = time();
}

/**
 * Require specific role or redirect
 * 
 * @param string $role Required role
 * @return void
 */
function requireRole($role) {
    requireAuthentication();
    if (!aLeRole($role)) {
        header('Location: index.php?action=accueil');
        exit();
    }
}

/**
 * Sanitize input data
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
