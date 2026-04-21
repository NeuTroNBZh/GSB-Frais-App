<?php
/**
 * Connection controller
 * Handles user authentication
 * 
 * @author GSB
 * @version 1.1
 */

$userModel = new User();
$erreur = isset($_GET['erreur']) ? sanitize($_GET['erreur']) : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifierJetonCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null)) {
        $erreur = 'Jeton de securite invalide';
    } else {
        $login = isset($_POST['txtLogin']) ? sanitize($_POST['txtLogin']) : '';
        $password = isset($_POST['txtPassword']) ? $_POST['txtPassword'] : '';
        
        if (!empty($login) && !empty($password)) {
            $user = $userModel->authenticate($login, $password);
            
            if ($user) {
                connecterUtilisateur($user);
                header('Location: index.php?action=accueil');
                exit();
            } else {
                $erreur = 'Identifiants incorrects';
            }
        } else {
            $erreur = 'Veuillez remplir tous les champs';
        }
    }
}

// Display login view
$csrfToken = obtenirJetonCsrf();
require_once 'vues/header.inc.php';
require_once 'vues/connexion.inc.php';
require_once 'vues/footer.inc.php';
