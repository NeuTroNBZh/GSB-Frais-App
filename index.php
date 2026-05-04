<?php

// à supprimer après tests

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Front Controller for GSB-Frais-App
 * Main entry point for all requests
 * 
 * @author GSB
 * @version 1.0
 */

// Include configuration and dependencies
require_once 'include/config.php';
require_once 'include/Database.php';
require_once 'include/User.php';
require_once 'include/Frais.php';
require_once 'include/functions.php';

// Start session
demarrerSession();

// Get action from request
$action = isset($_GET['action']) ? sanitize($_GET['action']) : 'accueil';

// Route to appropriate controller
switch ($action) {
    // Authentication actions
    case 'connexion':
        require_once 'controleurs/connexion.php';
        break;
    
    case 'deconnexion':
        require_once 'controleurs/deconnexion.php';
        break;

    case 'accueil':
        require_once 'controleurs/accueil.php';
        break;
    
    // Visitor actions
    case 'mesFrais':
    case 'saisirFrais':
    case 'enregistrerFrais':
    case 'supprimerHorsForfait':
    case 'supprimerFiche':
    case 'modifierFrais':
    case 'enregistrerModification':
    case 'modifierFichePrecedente':
        require_once 'controleurs/visiteur.php';
        break;
    
    // Accountant actions
    case 'validerFrais':
    case 'listeFraisVisiteur':
    case 'detailFrais':
    case 'validerFiche':
    case 'parcAutomobileListe':
    case 'ajouterVehicule':
    case 'supprimerVehicule':
        require_once 'controleurs/comptable.php';
        break;
    
    // Admin actions
    case 'gestionUtilisateurs':
    case 'rapports':
        require_once 'controleurs/admin.php';
        break;
    
    default:
        require_once 'controleurs/accueil.php';
        break;
}
