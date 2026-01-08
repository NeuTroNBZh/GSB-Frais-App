<?php
/**
 * Connection controller
 * Handles user authentication
 * 
 * @author GSB
 * @version 1.0
 */

$userModel = new User();
$erreur = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

// Display login view
require_once 'vues/header.inc.php';
require_once 'vues/connexion.inc.php';
require_once 'vues/footer.inc.php';
