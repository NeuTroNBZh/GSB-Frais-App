<?php
/**
 * GSB Frais App — Installateur
 * Crée la base de données, les tables et les comptes de test.
 * SUPPRIMER ce fichier après installation.
 */

$errors   = [];
$success  = [];
$done     = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Récupération des paramètres ────────────────────────────────────────
    $host    = trim($_POST['db_host']    ?? 'localhost');
    $dbName  = trim($_POST['db_name']    ?? 'gsb_frais');
    $dbUser  = trim($_POST['db_user']    ?? 'root');
    $dbPass  = $_POST['db_pass']         ?? '';
    $mdpTest = trim($_POST['mdp_test']   ?? '');

    if ($host === '')   { $errors[] = "L'hôte MySQL est obligatoire."; }
    if ($dbName === '') { $errors[] = "Le nom de la base est obligatoire."; }
    if ($dbUser === '') { $errors[] = "L'utilisateur MySQL est obligatoire."; }
    if ($mdpTest === '') { $errors[] = "Le mot de passe des comptes de test est obligatoire."; }

    if (empty($errors)) {
        // ── Connexion sans base (pour CREATE DATABASE) ─────────────────────
        try {
            $pdo = new PDO(
                "mysql:host={$host};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $errors[] = "Connexion MySQL impossible : " . htmlspecialchars($e->getMessage());
        }
    }

    if (empty($errors)) {
        try {
            // ── Créer la base ──────────────────────────────────────────────
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`
                        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");
            $success[] = "Base de données <strong>{$dbName}</strong> prête.";

            // ── Tables ─────────────────────────────────────────────────────
            $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs (
                id            INT PRIMARY KEY AUTO_INCREMENT,
                login         VARCHAR(50) UNIQUE NOT NULL,
                mot_de_passe  VARCHAR(255) NOT NULL,
                nom           VARCHAR(100) NOT NULL,
                prenom        VARCHAR(100) NOT NULL,
                role          ENUM('visiteur','comptable','admin') NOT NULL DEFAULT 'visiteur',
                date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_login (login),
                INDEX idx_role  (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = "Table <code>utilisateurs</code> créée.";

            $pdo->exec("CREATE TABLE IF NOT EXISTS frais_forfait (
                id      INT PRIMARY KEY AUTO_INCREMENT,
                code    VARCHAR(10) NOT NULL UNIQUE,
                libelle VARCHAR(100) NOT NULL,
                montant DECIMAL(10,2) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = "Table <code>frais_forfait</code> créée.";

            $pdo->exec("CREATE TABLE IF NOT EXISTS fiche_frais (
                id               INT PRIMARY KEY AUTO_INCREMENT,
                visiteur_id      INT NOT NULL,
                mois             VARCHAR(7) NOT NULL,
                nb_justificatifs INT DEFAULT 0,
                montant_valide   DECIMAL(10,2) DEFAULT 0,
                statut           ENUM('En cours','Cloturé','Validé','Remboursé') NOT NULL DEFAULT 'En cours',
                date_modif       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (visiteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
                INDEX idx_visiteur (visiteur_id),
                INDEX idx_mois     (mois),
                INDEX idx_statut   (statut),
                UNIQUE KEY unique_visiteur_mois (visiteur_id, mois)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = "Table <code>fiche_frais</code> créée.";

            $pdo->exec("CREATE TABLE IF NOT EXISTS ligne_frais_forfait (
                id               INT PRIMARY KEY AUTO_INCREMENT,
                fiche_frais_id   INT NOT NULL,
                frais_forfait_id INT NOT NULL,
                quantite         INT DEFAULT 0,
                FOREIGN KEY (fiche_frais_id)   REFERENCES fiche_frais(id)    ON DELETE CASCADE,
                FOREIGN KEY (frais_forfait_id) REFERENCES frais_forfait(id)  ON DELETE CASCADE,
                INDEX idx_fiche (fiche_frais_id),
                UNIQUE KEY unique_fiche_forfait (fiche_frais_id, frais_forfait_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = "Table <code>ligne_frais_forfait</code> créée.";

            $pdo->exec("CREATE TABLE IF NOT EXISTS ligne_frais_hors_forfait (
                id             INT PRIMARY KEY AUTO_INCREMENT,
                fiche_frais_id INT NOT NULL,
                date           DATE NOT NULL,
                libelle        VARCHAR(255) NOT NULL,
                montant        DECIMAL(10,2) NOT NULL,
                FOREIGN KEY (fiche_frais_id) REFERENCES fiche_frais(id) ON DELETE CASCADE,
                INDEX idx_fiche (fiche_frais_id),
                INDEX idx_date  (date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $success[] = "Table <code>ligne_frais_hors_forfait</code> créée.";

            // ── Forfaits (INSERT IGNORE = idempotent) ──────────────────────
            $pdo->exec("INSERT IGNORE INTO frais_forfait (code, libelle, montant) VALUES
                ('ETP', 'Forfait Etape',       110.00),
                ('KM',  'Frais Kilométrique',    0.62),
                ('NUI', 'Nuitée Hôtel',          80.00),
                ('REP', 'Repas Restaurant',       25.00)");
            $success[] = "Forfaits insérés (ETP, KM, NUI, REP).";

            // ── Comptes de test ────────────────────────────────────────────
            $hash = password_hash($mdpTest, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO utilisateurs (login, mot_de_passe, nom, prenom, role)
                 VALUES (:login, :mdp, :nom, :prenom, :role)
                 ON DUPLICATE KEY UPDATE mot_de_passe = :mdp2"
            );

            $comptes = [
                ['visiteur1',  'Dupont',  'Jean',   'visiteur'],
                ['comptable1', 'Martin',  'Sophie', 'comptable'],
                ['admin1',     'Bernard', 'Pierre', 'admin'],
            ];

            foreach ($comptes as [$login, $nom, $prenom, $role]) {
                $stmt->execute([
                    ':login'  => $login,
                    ':mdp'    => $hash,
                    ':nom'    => $nom,
                    ':prenom' => $prenom,
                    ':role'   => $role,
                    ':mdp2'   => $hash,
                ]);
            }
            $success[] = "Comptes de test créés/mis à jour avec le mot de passe fourni.";

            // ── Mettre à jour config.php ───────────────────────────────────
            $configPath = __DIR__ . '/include/config.php';
            if (is_writable($configPath)) {
                $configContent = file_get_contents($configPath);
                $configContent = preg_replace(
                    "/define\('DB_HOST',\s*'[^']*'\)/",
                    "define('DB_HOST', '" . addslashes($host) . "')",
                    $configContent
                );
                $configContent = preg_replace(
                    "/define\('DB_NAME',\s*'[^']*'\)/",
                    "define('DB_NAME', '" . addslashes($dbName) . "')",
                    $configContent
                );
                $configContent = preg_replace(
                    "/define\('DB_USER',\s*'[^']*'\)/",
                    "define('DB_USER', '" . addslashes($dbUser) . "')",
                    $configContent
                );
                $configContent = preg_replace(
                    "/define\('DB_PASS',\s*'[^']*'\)/",
                    "define('DB_PASS', '" . addslashes($dbPass) . "')",
                    $configContent
                );
                file_put_contents($configPath, $configContent);
                $success[] = "<code>include/config.php</code> mis à jour automatiquement.";
            } else {
                $success[] = "⚠️ <code>include/config.php</code> non accessible en écriture. Mettez-le à jour manuellement.";
            }

            $done = true;

        } catch (PDOException $e) {
            $errors[] = "Erreur SQL : " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation GSB Frais App</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; display: flex; justify-content: center; padding: 40px 20px; }
        .box { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.12); padding: 36px; max-width: 560px; width: 100%; }
        h1  { color: #003366; margin-bottom: 6px; font-size: 22px; }
        p.sub { color: #666; margin-bottom: 24px; font-size: 13px; }
        label { display: block; margin-bottom: 4px; font-weight: 600; color: #444; font-size: 13px; }
        input { width: 100%; padding: 9px 11px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; margin-bottom: 16px; }
        input:focus { outline: none; border-color: #0055aa; }
        button { background: #0055aa; color: #fff; border: none; padding: 11px 28px; border-radius: 4px; font-size: 15px; cursor: pointer; width: 100%; }
        button:hover { background: #003366; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; padding: 12px 16px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; padding: 12px 16px; margin-bottom: 20px; }
        .alert-success li { margin: 4px 0; }
        hr { border: 0; border-top: 1px solid #eee; margin: 24px 0; }
        .credentials { background: #e6f0ff; border-radius: 6px; padding: 14px 18px; }
        .credentials table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .credentials th, .credentials td { text-align: left; padding: 5px 8px; }
        .credentials th { color: #003366; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffc107; border-radius: 4px; padding: 10px 14px; font-size: 13px; margin-top: 20px; }
        a.btn-app { display: block; text-align: center; margin-top: 18px; background: #28a745; color: #fff; padding: 11px; border-radius: 4px; text-decoration: none; font-size: 15px; }
        a.btn-app:hover { background: #1e7e34; }
    </style>
</head>
<body>
<div class="box">
    <h1>🔧 Installation GSB Frais App</h1>
    <p class="sub">Ce script crée la base de données, les tables et les comptes de test.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <?php foreach ($errors as $e): ?>
                <div>❌ <?php echo $e; ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert-success">
            <ul style="margin:0;padding-left:18px">
                <?php foreach ($success as $s): ?>
                    <li>✅ <?php echo $s; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($done): ?>
        <div class="credentials">
            <strong>Comptes de test</strong>
            <table>
                <tr><th>Login</th><th>Mot de passe</th><th>Rôle</th></tr>
                <tr><td>visiteur1</td>  <td><?php echo htmlspecialchars($_POST['mdp_test']); ?></td><td>visiteur</td></tr>
                <tr><td>comptable1</td> <td><?php echo htmlspecialchars($_POST['mdp_test']); ?></td><td>comptable</td></tr>
                <tr><td>admin1</td>     <td><?php echo htmlspecialchars($_POST['mdp_test']); ?></td><td>admin</td></tr>
            </table>
        </div>
        <div class="warning">
            ⚠️ Supprimez le fichier <strong>install.php</strong> de votre serveur dès maintenant.
        </div>
        <a class="btn-app" href="index.php">→ Accéder à l'application</a>
    <?php else: ?>
        <form method="POST" action="">
            <label>Hôte MySQL</label>
            <input type="text" name="db_host" value="<?php echo htmlspecialchars($_POST['db_host'] ?? '192.168.1.103'); ?>" required>

            <label>Nom de la base de données</label>
            <input type="text" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name'] ?? 'gsb_frais'); ?>" required>

            <label>Utilisateur MySQL</label>
            <input type="text" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user'] ?? 'gabriel'); ?>" required>

            <label>Mot de passe MySQL</label>
            <input type="password" name="db_pass" value="<?php echo htmlspecialchars($_POST['db_pass'] ?? 'TheLumen67'); ?>">

            <hr>

            <label>Mot de passe des comptes de test (visiteur1 / comptable1 / admin1)</label>
            <input type="text" name="mdp_test" value="<?php echo htmlspecialchars($_POST['mdp_test'] ?? 'gsb2026'); ?>" required>

            <button type="submit">Installer</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
