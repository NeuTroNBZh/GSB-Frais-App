<?php
/**
 * Admin controller
 * Handles admin-specific actions
 * 
 * @author GSB
 * @version 1.0
 */

requireRole(User::ROLE_ADMIN);

$user = getUtilisateurConnecte();

// Handle different actions
switch ($action) {
    case 'gestionUtilisateurs':
        // Display user management
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/admin/gestionUtilisateurs.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    case 'rapports':
        // Display reports
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/admin/rapports.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    default:
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/accueil.inc.php';
        require_once 'vues/footer.inc.php';
        break;
}
