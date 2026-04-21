<?php
/**
 * Disconnection controller
 * Handles user logout
 * 
 * @author GSB
 * @version 1.0
 */

deconnecterUtilisateur();
header('Location: index.php?action=connexion');
exit();
