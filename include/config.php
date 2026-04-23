<?php
/**
 * Configuration file for GSB-Frais-App
 * Contains database connection parameters
 * 
 * @author GSB
 * @version 1.0
 */

// Database configuration prod

define('DB_HOST', '192.168.1.103'); 
define('DB_NAME', 'gsb_frais');
define('DB_USER', 'gabriel');
define('DB_PASS', 'TheLumen67');
define('DB_CHARSET', 'utf8mb4');


// Test configuration for podman
/*
define('DB_HOST', 'db'); 
define('DB_NAME', 'gsb_frais');
define('DB_USER', 'gsb_user');
define('DB_PASS', 'gsb_pass');
define('DB_CHARSET', 'utf8mb4');
*/

/*
define('DB_HOST', 'localhost'); 
define('DB_NAME', 'gsb_frais');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
*/
// Application configuration
define('APP_NAME', 'GSB Frais App');
define('BASE_URL', '/');

// Session configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
