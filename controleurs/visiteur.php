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
$erreur = '';
$moisCourant = date('Y-m');

$fraisModel->cloturerFichesAnciennes($user['id'], $moisCourant);

// Handle different actions
switch ($action) {
    case 'mesFrais':
        // Display visitor's expense sheets
        $ficheFrais = $fraisModel->getFichesFraisByVisiteur($user['id']);
        $csrfToken = obtenirJetonCsrf();
        $message = isset($_GET['message']) ? sanitize($_GET['message']) : '';
        $erreur = isset($_GET['erreur']) ? sanitize($_GET['erreur']) : '';
        
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/visiteur/mesFrais.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    case 'saisirFrais':
        // Display expense entry form
        $message = isset($_GET['message']) ? sanitize($_GET['message']) : '';
        $erreur = isset($_GET['erreur']) ? sanitize($_GET['erreur']) : '';
        
        if (isset($_GET['fiche_id'])) {
            // Editing an existing fiche
            $ficheId = intval($_GET['fiche_id']);
            $fiche = $fraisModel->getFicheFraisById($ficheId);
            if (!$fiche || (int) $fiche['visiteur_id'] !== (int) $user['id']) {
                $erreur = 'Fiche de frais invalide ou accès non autorisé';
                header('Location: index.php?action=mesFrais&erreur=' . urlencode($erreur));
                exit();
            }
            if ($fiche['statut'] !== Frais::STATUS_EN_COURS) {
                $erreur = 'Seules les fiches en cours peuvent être modifiées';
                header('Location: index.php?action=mesFrais&erreur=' . urlencode($erreur));
                exit();
            }
        } else {
            // Get or create current month expense sheet
            $mois = $moisCourant;
            $ficheFrais = $fraisModel->getFicheFraisByVisiteurAndMois($user['id'], $mois);
            
            if (!$ficheFrais) {
                $ficheId = $fraisModel->createFicheFrais($user['id'], $mois);
            } else {
                if ($ficheFrais['statut'] !== Frais::STATUS_EN_COURS) {
                    $erreur = 'La fiche du mois en cours n\'est pas modifiable';
                    header('Location: index.php?action=mesFrais&erreur=' . urlencode($erreur));
                    exit();
                }
                $ficheId = $ficheFrais['id'];
            }
        }
        
        $fraisForfait = $fraisModel->getFraisForfait($ficheId);
        $fraisHorsForfait = $fraisModel->getFraisHorsForfait($ficheId);
        $typesForfait = $fraisModel->getFraisForfaitTypes();
        $csrfToken = obtenirJetonCsrf();
        $isEditing = isset($_GET['fiche_id']);
        
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/visiteur/saisirFrais.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    case 'enregistrerFrais':
        // Handle expense form submission
        $redirectAction = 'saisirFrais';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierJetonCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
                $message = 'Jeton de securite invalide';
                header('Location: index.php?action=saisirFrais&message=' . urlencode($message));
                exit();
            }

            $ficheId = isset($_POST['fiche_id']) ? intval($_POST['fiche_id']) : 0;
            $fiche = $fraisModel->getFicheFraisById($ficheId);

            if (!$fiche || (int) $fiche['visiteur_id'] !== (int) $user['id']) {
                $message = 'Fiche de frais invalide';
                header('Location: index.php?action=saisirFrais&message=' . urlencode($message));
                exit();
            }

            if ($fiche['statut'] !== Frais::STATUS_EN_COURS) {
                $message = 'Seules les fiches en cours peuvent etre modifiees';
                header('Location: index.php?action=saisirFrais&message=' . urlencode($message));
                exit();
            }
            
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

            $fraisModel->recalculerMontantValide($ficheId);
            
            $message = 'Frais enregistrés avec succès';
            $redirectAction = (isset($_POST['is_editing']) && $_POST['is_editing'] === '1') ? 'mesFrais' : 'saisirFrais';
        }
        
        // Redirect back to expense entry
        header('Location: index.php?action=' . $redirectAction . '&message=' . urlencode($message));
        exit();
        break;

    case 'supprimerFiche':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierJetonCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
                $erreur = 'Jeton de securite invalide';
                header('Location: index.php?action=mesFrais&erreur=' . urlencode($erreur));
                exit();
            }

            $ficheId = isset($_POST['fiche_id']) ? intval($_POST['fiche_id']) : 0;
            $fiche = $fraisModel->getFicheFraisById($ficheId);

            if (!$fiche || (int) $fiche['visiteur_id'] !== (int) $user['id']) {
                $erreur = 'Fiche introuvable ou accès non autorisé';
            } elseif ($fiche['statut'] === Frais::STATUS_CLOTURE) {
                $erreur = 'Une fiche clôturée ne peut pas être supprimée';
            } else {
                $fraisModel->deleteFicheFrais($ficheId);
                $message = 'Fiche supprimée avec succès';
            }
        }

        $queryKey = !empty($erreur) ? 'erreur' : 'message';
        $queryValue = !empty($erreur) ? $erreur : $message;
        header('Location: index.php?action=mesFrais&' . $queryKey . '=' . urlencode($queryValue));
        exit();
        break;

    case 'supprimerHorsForfait':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierJetonCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
                $erreur = 'Jeton de securite invalide';
                header('Location: index.php?action=saisirFrais&erreur=' . urlencode($erreur));
                exit();
            }

            $ligneId = isset($_POST['ligne_id']) ? intval($_POST['ligne_id']) : 0;
            $ligne = $fraisModel->getLigneFraisHorsForfaitById($ligneId);

            if (!$ligne) {
                $erreur = 'Ligne hors forfait introuvable';
            } elseif ((int) $ligne['visiteur_id'] !== (int) $user['id']) {
                $erreur = 'Action non autorisee';
            } elseif ($ligne['statut'] !== Frais::STATUS_EN_COURS) {
                $erreur = 'Seules les fiches en cours peuvent etre modifiees';
            } else {
                $fraisModel->deleteFraisHorsForfait($ligneId);
                $fraisModel->recalculerMontantValide((int) $ligne['fiche_frais_id']);
                $message = 'Ligne hors forfait supprimee avec succes';
            }
        }

        $queryKey = $erreur !== '' ? 'erreur' : 'message';
        $queryValue = $erreur !== '' ? $erreur : $message;
        header('Location: index.php?action=saisirFrais&' . $queryKey . '=' . urlencode($queryValue));
        exit();
        break;

        case 'modifierFichePrecedente':
    $mois = isset($_GET['mois']) ? sanitize($_GET['mois']) : null;
    $message = isset($_GET['message']) ? sanitize($_GET['message']) : '';
    $erreur = isset($_GET['erreur']) ? sanitize($_GET['erreur']) : '';

    // Récupère toutes les fiches du visiteur
    $fichesDispo = $fraisModel->getFichesFraisByVisiteur($user['id']);

    // Si un mois est sélectionné, charge la fiche correspondante
    if ($mois) {
        $ficheFrais = $fraisModel->getFicheFraisByVisiteurAndMois($user['id'], $mois);

        // Sécurité : la fiche doit exister, appartenir au visiteur, et ne pas être clôturée
        if (!$ficheFrais || $ficheFrais['statut'] === Frais::STATUS_CLOTURE) {
            $erreur = 'Cette fiche ne peut pas être modifiée';
            $mois = null;
        } else {
            $ficheId = $ficheFrais['id'];
            $fraisForfait = $fraisModel->getFraisForfait($ficheId);
            $fraisHorsForfait = $fraisModel->getFraisHorsForfait($ficheId);
            $typesForfait = $fraisModel->getFraisForfaitTypes();
            $csrfToken = obtenirJetonCsrf();
        }
    }

    require_once 'vues/header.inc.php';
    require_once 'vues/menu.inc.php';
    require_once 'vues/visiteur/modifierFichePrecedente.inc.php';
    require_once 'vues/footer.inc.php';
    break;

    case 'accueil':
    default:
        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/accueil.inc.php';
        require_once 'vues/footer.inc.php';
        break;
}
