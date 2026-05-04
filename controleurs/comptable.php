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
$messageType = 'success';

// Handle different actions
switch ($action) {
    case 'parcAutomobileListe':
        // Display vehicle fleet list for accountant
        $vehiculesParc = $fraisModel->getParcAutomobileListe();
        $visiteursSansVehicule = $fraisModel->getVisiteursSansVehicule();

        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/comptable/parcAutomobileListe.inc.php';
        require_once 'vues/footer.inc.php';
        break;

    case 'ajouterVehicule':
        // Handle POST: add a new vehicle to the fleet
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=parcAutomobileListe');
            exit();
        }

        if (!verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
            header('Location: index.php?action=parcAutomobileListe&erreur=csrf');
            exit();
        }

        $immatriculation = strtoupper(trim(sanitize($_POST['immatriculation'] ?? '')));
        $visiteurId = intval($_POST['visiteur_id'] ?? 0);
        $dateAttribution = sanitize($_POST['date_attribution'] ?? '');

        if ($immatriculation === '' || $visiteurId <= 0 || $dateAttribution === '') {
            header('Location: index.php?action=parcAutomobileListe&erreur=champs_manquants');
            exit();
        }

        // Validate date format
        $dateObj = DateTime::createFromFormat('Y-m-d', $dateAttribution);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $dateAttribution) {
            header('Location: index.php?action=parcAutomobileListe&erreur=date_invalide');
            exit();
        }

        $resultat = $fraisModel->ajouterVehicule($immatriculation, $visiteurId, $dateAttribution);

        if ($resultat) {
            header('Location: index.php?action=parcAutomobileListe&succes=vehicule_ajoute');
        } else {
            header('Location: index.php?action=parcAutomobileListe&erreur=ajout_impossible');
        }
        exit();
        break;

    case 'supprimerVehicule':
        // Handle POST: delete a vehicle from the fleet
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=parcAutomobileListe');
            exit();
        }

        if (!verifierJetonCsrf($_POST['csrf_token'] ?? '')) {
            header('Location: index.php?action=parcAutomobileListe&erreur=csrf');
            exit();
        }

        $immatriculation = strtoupper(trim(sanitize($_POST['immatriculation'] ?? '')));

        if ($immatriculation === '') {
            header('Location: index.php?action=parcAutomobileListe&erreur=champs_manquants');
            exit();
        }

        $resultat = $fraisModel->supprimerVehicule($immatriculation);

        if ($resultat) {
            header('Location: index.php?action=parcAutomobileListe&succes=vehicule_supprime');
        } else {
            header('Location: index.php?action=parcAutomobileListe&erreur=suppression_impossible');
        }
        exit();
        break;

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

            $fichesParPage = 10;
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $totalFiches = $fraisModel->countFichesFraisByVisiteur($visiteurId);
            $totalPages = max(1, (int) ceil($totalFiches / $fichesParPage));
            if ($page > $totalPages) {
                $page = $totalPages;
            }

            $offset = ($page - 1) * $fichesParPage;
            $ficheFrais = $fraisModel->getFichesFraisByVisiteur($visiteurId, null, $fichesParPage, $offset);
            
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

            if (!$fiche) {
                header('Location: index.php?action=validerFrais');
                exit();
            }

            $fraisForfait = $fraisModel->getFraisForfait($ficheId);
            $fraisHorsForfait = $fraisModel->getFraisHorsForfait($ficheId);
            $csrfToken = obtenirJetonCsrf();
            
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
            if (!verifierJetonCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
                $message = 'Jeton de securite invalide';
                $messageType = 'error';
                header('Location: index.php?action=validerFrais&message=' . urlencode($message) . '&message_type=' . urlencode($messageType));
                exit();
            }

            $ficheId = isset($_POST['fiche_id']) ? intval($_POST['fiche_id']) : 0;
            $nouveauStatut = isset($_POST['lstStatut']) ? sanitize($_POST['lstStatut']) : '';
            $statutsAutorises = Frais::getAllowedStatuses();
            
            if ($ficheId > 0 && in_array($nouveauStatut, $statutsAutorises, true)) {
                $fraisModel->updateStatut($ficheId, $nouveauStatut);
                $message = 'Statut mis à jour avec succès';
            } else {
                $message = 'Statut invalide';
                $messageType = 'error';
            }
        }
        
        header('Location: index.php?action=validerFrais&message=' . urlencode($message) . '&message_type=' . urlencode($messageType));
        exit();
        break;
    
    default:
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/accueil.inc.php';
        require_once 'vues/footer.inc.php';
        break;
}
