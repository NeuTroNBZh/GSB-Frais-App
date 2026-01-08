<?php
/**
 * Visitor controller
 * Handles visitor-specific actions for expense management
 * 
 * @author GSB
 * @version 1.0
 */

requireRole(User::ROLE_VISITOR);

$user = getUtilisateurConnecte();
$fraisModel = new Frais();
$message = '';

// Handle different actions
switch ($action) {
    case 'mesFrais':
        // Display visitor's expense sheets
        $ficheFrais = $fraisModel->getFichesFraisByVisiteur($user['id']);
        
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/visiteur/mesFrais.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    case 'saisirFrais':
        // Display expense entry form
        $mois = date('Y-m');
        
        // Get or create current month expense sheet
        $ficheFrais = $fraisModel->getFichesFraisByVisiteur($user['id'], Frais::STATUS_EN_COURS);
        
        if (empty($ficheFrais)) {
            $ficheId = $fraisModel->createFicheFrais($user['id'], $mois);
        } else {
            $ficheId = $ficheFrais[0]['id'];
        }
        
        $fraisForfait = $fraisModel->getFraisForfait($ficheId);
        $fraisHorsForfait = $fraisModel->getFraisHorsForfait($ficheId);
        $typesForfait = $fraisModel->getFraisForfaitTypes();
        
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/visiteur/saisirFrais.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    case 'enregistrerFrais':
        // Handle expense form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ficheId = isset($_POST['fiche_id']) ? intval($_POST['fiche_id']) : 0;
            
            // Update forfait expenses
            $typesForfait = $fraisModel->getFraisForfaitTypes();
            foreach ($typesForfait as $type) {
                $fieldName = 'txtForfait' . $type['id'];
                if (isset($_POST[$fieldName])) {
                    $quantite = intval($_POST[$fieldName]);
                    $fraisModel->updateFraisForfait($ficheId, $type['id'], $quantite);
                }
            }
            
            // Add hors forfait expense
            if (!empty($_POST['txtDateHorsForfait']) && !empty($_POST['txtLibelleHorsForfait']) && !empty($_POST['txtMontantHorsForfait'])) {
                $date = sanitize($_POST['txtDateHorsForfait']);
                $libelle = sanitize($_POST['txtLibelleHorsForfait']);
                $montant = floatval($_POST['txtMontantHorsForfait']);
                
                $fraisModel->addFraisHorsForfait($ficheId, $date, $libelle, $montant);
            }
            
            $message = 'Frais enregistrés avec succès';
        }
        
        // Redirect back to expense entry
        header('Location: index.php?action=saisirFrais&message=' . urlencode($message));
        exit();
        break;
    
    case 'accueil':
    default:
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/accueil.inc.php';
        require_once 'vues/footer.inc.php';
        break;
}
