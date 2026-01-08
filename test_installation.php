<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSB Frais App - Test d'installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background-color: #003366;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .test-section {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ff9900;
            font-weight: bold;
        }
        .test-item {
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #ccc;
            padding-left: 15px;
        }
        .test-item.pass {
            border-left-color: #28a745;
            background-color: #d4edda;
        }
        .test-item.fail {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }
        .test-item.warn {
            border-left-color: #ff9900;
            background-color: #fff3cd;
        }
        h2 {
            color: #003366;
        }
        .summary {
            padding: 20px;
            background-color: #e6f0ff;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GSB Frais App - Vérification de l'installation</h1>
        <p>Ce script vérifie que votre environnement est correctement configuré</p>
    </div>

    <?php
    $errors = 0;
    $warnings = 0;
    $success = 0;

    // Test 1: PHP Version
    echo '<div class="test-section">';
    echo '<h2>1. Version de PHP</h2>';
    $phpVersion = PHP_VERSION;
    $minVersion = '7.4.0';
    if (version_compare($phpVersion, $minVersion, '>=')) {
        echo "<div class='test-item pass'><span class='success'>✓</span> PHP $phpVersion (minimum requis: $minVersion)</div>";
        $success++;
    } else {
        echo "<div class='test-item fail'><span class='error'>✗</span> PHP $phpVersion - Version trop ancienne (minimum requis: $minVersion)</div>";
        $errors++;
    }
    echo '</div>';

    // Test 2: Extensions PHP
    echo '<div class="test-section">';
    echo '<h2>2. Extensions PHP</h2>';
    
    $requiredExtensions = [
        'pdo' => 'PDO - Accès base de données',
        'pdo_mysql' => 'PDO MySQL - Driver MySQL',
        'mbstring' => 'mbstring - Gestion chaînes multioctets',
        'session' => 'session - Gestion des sessions'
    ];

    foreach ($requiredExtensions as $ext => $desc) {
        if (extension_loaded($ext)) {
            echo "<div class='test-item pass'><span class='success'>✓</span> $desc</div>";
            $success++;
        } else {
            echo "<div class='test-item fail'><span class='error'>✗</span> $desc - Non installée</div>";
            $errors++;
        }
    }
    echo '</div>';

    // Test 3: Configuration PHP
    echo '<div class="test-section">';
    echo '<h2>3. Configuration PHP</h2>';
    
    $displayErrors = ini_get('display_errors');
    if ($displayErrors) {
        echo "<div class='test-item warn'><span class='warning'>⚠</span> display_errors activé - À désactiver en production</div>";
        $warnings++;
    } else {
        echo "<div class='test-item pass'><span class='success'>✓</span> display_errors désactivé</div>";
        $success++;
    }

    $sessionPath = session_save_path();
    if (is_writable($sessionPath)) {
        echo "<div class='test-item pass'><span class='success'>✓</span> Dossier sessions accessible: $sessionPath</div>";
        $success++;
    } else {
        echo "<div class='test-item fail'><span class='error'>✗</span> Dossier sessions non accessible: $sessionPath</div>";
        $errors++;
    }
    echo '</div>';

    // Test 4: Fichiers application
    echo '<div class="test-section">';
    echo '<h2>4. Fichiers de l\'application</h2>';
    
    $requiredFiles = [
        'index.php' => 'Front Controller',
        'include/config.php' => 'Configuration',
        'include/Database.php' => 'Classe Database',
        'include/User.php' => 'Classe User',
        'include/Frais.php' => 'Classe Frais',
        'include/functions.php' => 'Fonctions utilitaires',
        'styles/style.css' => 'Feuille de style',
        'database/schema.sql' => 'Schéma de base de données'
    ];

    foreach ($requiredFiles as $file => $desc) {
        if (file_exists($file)) {
            echo "<div class='test-item pass'><span class='success'>✓</span> $desc ($file)</div>";
            $success++;
        } else {
            echo "<div class='test-item fail'><span class='error'>✗</span> $desc ($file) - Fichier manquant</div>";
            $errors++;
        }
    }
    echo '</div>';

    // Test 5: Connexion base de données
    echo '<div class="test-section">';
    echo '<h2>5. Connexion à la base de données</h2>';
    
    if (file_exists('include/config.php') && file_exists('include/Database.php')) {
        require_once 'include/config.php';
        require_once 'include/Database.php';
        
        try {
            $db = Database::getInstance();
            echo "<div class='test-item pass'><span class='success'>✓</span> Connexion à la base de données réussie</div>";
            $success++;
            
            // Test des tables
            $pdo = $db->getConnection();
            
            $requiredTables = ['utilisateurs', 'frais_forfait', 'fiche_frais', 'ligne_frais_forfait', 'ligne_frais_hors_forfait'];
            
            foreach ($requiredTables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "<div class='test-item pass'><span class='success'>✓</span> Table '$table' existe</div>";
                    $success++;
                } else {
                    echo "<div class='test-item fail'><span class='error'>✗</span> Table '$table' manquante</div>";
                    $errors++;
                }
            }
            
            // Test des utilisateurs
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                echo "<div class='test-item pass'><span class='success'>✓</span> {$result['count']} utilisateur(s) dans la base</div>";
                $success++;
            } else {
                echo "<div class='test-item warn'><span class='warning'>⚠</span> Aucun utilisateur dans la base</div>";
                $warnings++;
            }
            
        } catch (Exception $e) {
            echo "<div class='test-item fail'><span class='error'>✗</span> Erreur de connexion: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<div class='test-item fail'>Vérifiez les paramètres dans include/config.php</div>";
            $errors++;
        }
    } else {
        echo "<div class='test-item fail'><span class='error'>✗</span> Fichiers de configuration manquants</div>";
        $errors++;
    }
    echo '</div>';

    // Test 6: Permissions
    echo '<div class="test-section">';
    echo '<h2>6. Permissions des fichiers</h2>';
    
    $writableDirs = [];
    // Vérifier si des dossiers doivent être accessibles en écriture
    // (Pour l'instant, aucun n'est requis pour cette application)
    
    if (is_readable('styles/style.css')) {
        echo "<div class='test-item pass'><span class='success'>✓</span> Fichiers CSS accessibles</div>";
        $success++;
    } else {
        echo "<div class='test-item fail'><span class='error'>✗</span> Fichiers CSS non accessibles</div>";
        $errors++;
    }
    
    if (is_readable('index.php')) {
        echo "<div class='test-item pass'><span class='success'>✓</span> index.php accessible</div>";
        $success++;
    } else {
        echo "<div class='test-item fail'><span class='error'>✗</span> index.php non accessible</div>";
        $errors++;
    }
    echo '</div>';

    // Résumé
    $total = $success + $errors + $warnings;
    echo '<div class="summary">';
    echo '<h2>Résumé</h2>';
    echo "<p><strong>Total de tests:</strong> $total</p>";
    echo "<p class='success'>Réussis: $success</p>";
    if ($warnings > 0) {
        echo "<p class='warning'>Avertissements: $warnings</p>";
    }
    if ($errors > 0) {
        echo "<p class='error'>Erreurs: $errors</p>";
        echo "<p><strong>Action requise:</strong> Corrigez les erreurs avant d'utiliser l'application.</p>";
        echo "<p>Consultez le fichier INSTALL.md pour les instructions d'installation.</p>";
    } else {
        echo "<p class='success' style='font-size: 18px;'>✓ Installation complète et fonctionnelle !</p>";
        echo "<p>Vous pouvez maintenant <a href='index.php'>accéder à l'application</a>.</p>";
        echo "<p><em>Important: Supprimez ce fichier (test_installation.php) pour des raisons de sécurité.</em></p>";
    }
    echo '</div>';
    ?>

</body>
</html>
