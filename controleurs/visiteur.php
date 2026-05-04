
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
        $moisSelectionne = isset($_GET['mois']) ? sanitize($_GET['mois']) : $moisCourant;

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $moisSelectionne)) {
            $moisSelectionne = $moisCourant;
            $erreur = 'Mois invalide, le mois courant a ete charge';
        }
        
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

            $moisSelectionne = $fiche['mois'];
        } else {
            // Get or create selected month expense sheet
            $ficheFrais = $fraisModel->getFicheFraisByVisiteurAndMois($user['id'], $moisSelectionne);
            
            if (!$ficheFrais) {
                $ficheId = $fraisModel->createFicheFrais($user['id'], $moisSelectionne);
            } else {
                $ficheId = $ficheFrais['id'];
            }
        }
        
        $fraisForfait = $fraisModel->getFraisForfait($ficheId);
        $fraisHorsForfait = $fraisModel->getFraisHorsForfait($ficheId);
        $vehiculeAttribue = $fraisModel->getVehiculeByVisiteurId((int) $user['id']);
        $typesForfait = $fraisModel->getFraisForfaitTypes();
        if ($vehiculeAttribue) {
            $typesForfait = array_values(array_filter($typesForfait, function ($type) {
                return isset($type['code']) && $type['code'] !== 'KM';
            }));
        }
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
        $redirectParams = [];
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
            
            // Update forfait expenses (KM is excluded for visitors with an assigned vehicle)
            $vehiculeAttribue = $fraisModel->getVehiculeByVisiteurId((int) $user['id']);
            $typesForfait = $fraisModel->getFraisForfaitTypes();
            if ($vehiculeAttribue) {
                $typesForfait = array_values(array_filter($typesForfait, function ($type) {
                    return isset($type['code']) && $type['code'] !== 'KM';
                }));
            }
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

                        $selectedMonth = isset($_POST['selected_month']) ? sanitize($_POST['selected_month']) : '';
                        if ($redirectAction === 'saisirFrais' && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
                            $redirectParams['mois'] = $selectedMonth;
                        }
        }
        
                    // Redirect back to expense entry
                    $query = ['action' => $redirectAction, 'message' => $message];
                    if (!empty($redirectParams['mois'])) {
                        $query['mois'] = $redirectParams['mois'];
                    }

                    header('Location: index.php?' . http_build_query($query));
        exit();
        break;

    case 'supprimerHorsForfait':
        $selectedMonth = isset($_POST['selected_month']) ? sanitize($_POST['selected_month']) : '';
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
        $query = ['action' => 'saisirFrais', $queryKey => $queryValue];
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $query['mois'] = $selectedMonth;
        }

        header('Location: index.php?' . http_build_query($query));
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

            if (!$fiche) {
                $erreur = 'Fiche de frais introuvable';
            } elseif ((int) $fiche['visiteur_id'] !== (int) $user['id']) {
                $erreur = 'Action non autorisee';
            } elseif ($fiche['statut'] !== Frais::STATUS_EN_COURS) {
                $erreur = 'Seules les fiches en cours peuvent etre supprimees';
            } elseif ($fraisModel->deleteFicheFrais($ficheId)) {
                $message = 'Fiche de frais supprimee avec succes';
            } else {
                $erreur = 'Impossible de supprimer la fiche de frais';
            }
        }

        $queryKey = $erreur !== '' ? 'erreur' : 'message';
        $queryValue = $erreur !== '' ? $erreur : $message;
        header('Location: index.php?action=mesFrais&' . $queryKey . '=' . urlencode($queryValue));
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