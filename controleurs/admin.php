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
$userModel = new User();
$fraisModel = new Frais();
$message = isset($_GET['message']) ? sanitize($_GET['message']) : '';
$erreur = isset($_GET['erreur']) ? sanitize($_GET['erreur']) : '';
$rolesDisponibles = [User::ROLE_VISITOR, User::ROLE_ACCOUNTANT, User::ROLE_ADMIN];

// Handle different actions
switch ($action) {
    case 'gestionUtilisateurs':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifierJetonCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
                header('Location: index.php?action=gestionUtilisateurs&erreur=' . urlencode('Jeton de securite invalide'));
                exit();
            }

            $adminAction = isset($_POST['admin_action']) ? sanitize($_POST['admin_action']) : '';

            if ($adminAction === 'create') {
                $login = isset($_POST['txtLogin']) ? sanitize($_POST['txtLogin']) : '';
                $password = isset($_POST['txtPassword']) ? trim($_POST['txtPassword']) : '';
                $nom = isset($_POST['txtNom']) ? sanitize($_POST['txtNom']) : '';
                $prenom = isset($_POST['txtPrenom']) ? sanitize($_POST['txtPrenom']) : '';
                $role = isset($_POST['lstRole']) ? sanitize($_POST['lstRole']) : '';

                if ($login === '' || $password === '' || $nom === '' || $prenom === '' || !in_array($role, $rolesDisponibles, true)) {
                    $erreur = 'Tous les champs sont obligatoires pour creer un utilisateur';
                } elseif ($userModel->loginExists($login)) {
                    $erreur = 'Ce login existe deja';
                } else {
                    $userModel->createUser($login, $password, $nom, $prenom, $role);
                    header('Location: index.php?action=gestionUtilisateurs&message=' . urlencode('Utilisateur cree avec succes'));
                    exit();
                }
            }

            if ($adminAction === 'update') {
                $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
                $login = isset($_POST['txtLogin']) ? sanitize($_POST['txtLogin']) : '';
                $password = isset($_POST['txtPassword']) ? trim($_POST['txtPassword']) : '';
                $nom = isset($_POST['txtNom']) ? sanitize($_POST['txtNom']) : '';
                $prenom = isset($_POST['txtPrenom']) ? sanitize($_POST['txtPrenom']) : '';
                $role = isset($_POST['lstRole']) ? sanitize($_POST['lstRole']) : '';

                if ($userId <= 0 || $login === '' || $nom === '' || $prenom === '' || !in_array($role, $rolesDisponibles, true)) {
                    $erreur = 'Donnees invalides pour modifier l utilisateur';
                } elseif (!$userModel->getUserById($userId)) {
                    $erreur = 'Utilisateur introuvable';
                } elseif ($userModel->loginExists($login, $userId)) {
                    $erreur = 'Ce login existe deja';
                } else {
                    $userModel->updateUser($userId, $login, $nom, $prenom, $role, $password !== '' ? $password : null);
                    header('Location: index.php?action=gestionUtilisateurs&message=' . urlencode('Utilisateur modifie avec succes'));
                    exit();
                }
            }

            if ($adminAction === 'delete') {
                $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

                if ($userId <= 0) {
                    $erreur = 'Utilisateur invalide';
                } elseif ($userId === (int) $user['id']) {
                    $erreur = 'Vous ne pouvez pas supprimer votre propre compte';
                } elseif (!$userModel->getUserById($userId)) {
                    $erreur = 'Utilisateur introuvable';
                } else {
                    $userModel->deleteUser($userId);
                    header('Location: index.php?action=gestionUtilisateurs&message=' . urlencode('Utilisateur supprime avec succes'));
                    exit();
                }
            }
        }

        $editUserId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $utilisateurEnEdition = $editUserId > 0 ? $userModel->getUserById($editUserId) : null;
        $csrfToken = obtenirJetonCsrf();

        $utilisateursParPage = 10;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $totalUtilisateurs = $userModel->countAllUsers();
        $totalPages = max(1, (int) ceil($totalUtilisateurs / $utilisateursParPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $utilisateursParPage;
        $utilisateurs = $userModel->getAllUsers($utilisateursParPage, $offset);

        require_once 'vues/header.inc.php';
        require_once 'vues/menu.inc.php';
        require_once 'vues/admin/gestionUtilisateurs.inc.php';
        require_once 'vues/footer.inc.php';
        break;
    
    case 'rapports':
        // Display reports
        $filtresRapports = [
            'mois_debut' => isset($_GET['mois_debut']) ? sanitize($_GET['mois_debut']) : '',
            'mois_fin' => isset($_GET['mois_fin']) ? sanitize($_GET['mois_fin']) : '',
            'statut' => isset($_GET['statut']) ? sanitize($_GET['statut']) : '',
            'visiteur_id' => isset($_GET['visiteur_id']) ? intval($_GET['visiteur_id']) : 0,
        ];

        $statutsRapport = Frais::getAllowedStatuses();
        if (!in_array($filtresRapports['statut'], $statutsRapport, true)) {
            $filtresRapports['statut'] = '';
        }

        $formatExport = isset($_GET['export']) ? sanitize($_GET['export']) : '';
        if ($formatExport === 'csv') {
            $lignesExport = $fraisModel->getRapportExportFiches($filtresRapports);
            $nomFichier = 'rapports_frais_' . date('Ymd_His') . '.csv';

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $nomFichier . '"');

            $stream = fopen('php://output', 'w');
            if ($stream !== false) {
                // UTF-8 BOM for Excel compatibility
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['Mois', 'Visiteur ID', 'Nom', 'Prenom', 'Statut', 'Montant valide', 'Justificatifs', 'Date modif'], ';');

                foreach ($lignesExport as $ligne) {
                    fputcsv($stream, [
                        $ligne['mois'],
                        $ligne['visiteur_id'],
                        $ligne['nom'],
                        $ligne['prenom'],
                        $ligne['statut'],
                        number_format((float) $ligne['montant_valide'], 2, '.', ''),
                        (int) $ligne['nb_justificatifs'],
                        $ligne['date_modif'],
                    ], ';');
                }

                fclose($stream);
            }

            exit();
        }

        $visiteursRapport = $userModel->getAllVisitors();
        $rapportSynthese = $fraisModel->getRapportSynthese($filtresRapports);
        $rapportParStatut = $fraisModel->getRapportParStatut($filtresRapports);
        $rapportMensuel = $fraisModel->getRapportMensuel(12, $filtresRapports);
        $rapportTopVisiteurs = $fraisModel->getRapportTopVisiteurs(10, $filtresRapports);

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
