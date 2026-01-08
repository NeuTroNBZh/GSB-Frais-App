<?php
/**
 * Accountant controller
 * Handles accountant-specific actions for expense validation
 * 
 * @author GSB
 * @version 1.0
 */

requireRole(User::ROLE_ACCOUNTANT);

$user = getUtilisateurConnecte();
$fraisModel = new Frais();
$userModel = new User();
$message = '';

// Handle different actions
switch ($action) {
    case 'validerFrais':
        // Display list of visitors
        $visiteurs = $userModel->getAllVisitors();
        
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/comptable/validerFrais.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    case 'listeFraisVisiteur':
        // Display expense sheets for selected visitor
        $visiteurId = isset($_GET['visiteur']) ? intval($_GET['visiteur']) : 0;
        
        if ($visiteurId > 0) {
            $visiteur = $userModel->getUserById($visiteurId);
            $ficheFrais = $fraisModel->getFichesFraisByVisiteur($visiteurId);
            
            require_once 'vues/header.inc.php';
            require_once 'vues/menu.inc.php';
            require_once 'vues/comptable/listeFraisVisiteur.inc.php';
            require_once 'vues/footer.inc.php';
        } else {
            header('Location: index.php?action=validerFrais');
            exit();
        }
        break;
    
    case 'detailFrais':
        // Display expense sheet details
        $ficheId = isset($_GET['fiche']) ? intval($_GET['fiche']) : 0;
        
        if ($ficheId > 0) {
            $fiche = $fraisModel->getFicheFraisById($ficheId);
            $fraisForfait = $fraisModel->getFraisForfait($ficheId);
            $fraisHorsForfait = $fraisModel->getFraisHorsForfait($ficheId);
            
            require_once 'vues/header.inc.php';
            require_once 'vues/menu.inc.php';
            require_once 'vues/comptable/detailFrais.inc.php';
            require_once 'vues/footer.inc.php';
        } else {
            header('Location: index.php?action=validerFrais');
            exit();
        }
        break;
    
    case 'validerFiche':
        // Validate expense sheet and update status
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ficheId = isset($_POST['fiche_id']) ? intval($_POST['fiche_id']) : 0;
            $nouveauStatut = isset($_POST['lstStatut']) ? sanitize($_POST['lstStatut']) : '';
            
            if ($ficheId > 0 && !empty($nouveauStatut)) {
                $fraisModel->updateStatut($ficheId, $nouveauStatut);
                $message = 'Statut mis à jour avec succès';
            }
        }
        
        header('Location: index.php?action=validerFrais&message=' . urlencode($message));
        exit();
        break;
    
    default:
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/accueil.inc.php';
        require_once 'vues/footer.inc.php';
        break;
}
