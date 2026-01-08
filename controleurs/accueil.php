<?php
/**
 * Home/Welcome controller
 * Main landing page after login
 * 
 * @author GSB
 * @version 1.0
 */

requireAuthentication();

$user = getUtilisateurConnecte();

require_once 'vues/header.inc.php';
require_once 'vues/menu.inc.php';
require_once 'vues/accueil.inc.php';
require_once 'vues/footer.inc.php';
