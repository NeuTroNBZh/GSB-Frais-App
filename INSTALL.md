# Installation Guide - GSB-Frais-App

## Table des matières
1. [Prérequis](#prérequis)
2. [Installation étape par étape](#installation-étape-par-étape)
3. [Configuration](#configuration)
4. [Vérification](#vérification)
5. [Dépannage](#dépannage)

## Prérequis

### Logiciels requis
- **PHP** : Version 7.4 ou supérieure
  - Extensions requises :
    - PDO
    - pdo_mysql
    - mbstring
- **MySQL** : Version 5.7+ ou **MariaDB** : Version 10.2+
- **Serveur web** : Apache, Nginx, ou serveur PHP intégré

### Vérification de votre environnement

#### Vérifier la version de PHP
```bash
php -v
```

#### Vérifier les extensions PHP
```bash
php -m | grep -E 'PDO|pdo_mysql|mbstring'
```

Vous devriez voir :
```
PDO
pdo_mysql
mbstring
```

#### Vérifier MySQL/MariaDB
```bash
mysql --version
```

## Installation étape par étape

### 1. Cloner le dépôt

```bash
git clone https://github.com/NeuTroNBZh/GSB-Frais-App.git
cd GSB-Frais-App
```

### 2. Configuration de la base de données

#### Option A : Via ligne de commande MySQL
```bash
mysql -u root -p < database/schema.sql
```

Entrez le mot de passe root de MySQL lorsque demandé.

#### Option B : Via phpMyAdmin
1. Ouvrir phpMyAdmin
2. Créer une nouvelle base de données nommée `gsb_frais`
3. Sélectionner la base de données
4. Aller dans l'onglet "Importer"
5. Choisir le fichier `database/schema.sql`
6. Cliquer sur "Exécuter"

#### Option C : Manuellement via MySQL CLI
```bash
mysql -u root -p
```

Puis exécuter :
```sql
CREATE DATABASE gsb_frais CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gsb_frais;
SOURCE /chemin/vers/GSB-Frais-App/database/schema.sql;
EXIT;
```

### 3. Configuration de l'application

Modifier le fichier `include/config.php` avec vos paramètres de connexion :

```php
define('DB_HOST', 'localhost');      // Hôte de la base de données
define('DB_NAME', 'gsb_frais');      // Nom de la base de données
define('DB_USER', 'root');           // Utilisateur MySQL
define('DB_PASS', '');               // Mot de passe MySQL
```

**Note de sécurité** : En production, utilisez un utilisateur MySQL spécifique avec des privilèges limités, pas le compte root.

#### Créer un utilisateur MySQL dédié (recommandé)
```sql
CREATE USER 'gsb_user'@'localhost' IDENTIFIED BY 'mot_de_passe_securise';
GRANT SELECT, INSERT, UPDATE, DELETE ON gsb_frais.* TO 'gsb_user'@'localhost';
FLUSH PRIVILEGES;
```

Puis dans `config.php` :
```php
define('DB_USER', 'gsb_user');
define('DB_PASS', 'mot_de_passe_securise');
```

### 4. Lancement de l'application

#### Option A : Serveur PHP intégré (développement)
```bash
cd /chemin/vers/GSB-Frais-App
php -S localhost:8000
```

Accéder à : `http://localhost:8000`

#### Option B : Apache

1. Copier le dossier dans le répertoire web :
   ```bash
   sudo cp -r GSB-Frais-App /var/www/html/
   ```

2. Configurer les permissions :
   ```bash
   sudo chown -R www-data:www-data /var/www/html/GSB-Frais-App
   sudo chmod -R 755 /var/www/html/GSB-Frais-App
   ```

3. Accéder à : `http://localhost/GSB-Frais-App`

#### Option C : Nginx

Ajouter cette configuration dans votre fichier de site Nginx :

```nginx
server {
    listen 80;
    server_name gsb-frais.local;
    root /var/www/GSB-Frais-App;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Vérification

### 1. Test de connexion à la base de données

Créer un fichier test `test_db.php` dans le dossier racine :

```php
<?php
require_once 'include/config.php';
require_once 'include/Database.php';

try {
    $db = Database::getInstance();
    echo "✓ Connexion à la base de données réussie !<br>";
    
    $pdo = $db->getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
    $result = $stmt->fetch();
    echo "✓ {$result['count']} utilisateurs trouvés dans la base<br>";
    
    echo "<br>Installation réussie ! Vous pouvez supprimer ce fichier.";
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage();
}
?>
```

Accéder à `http://localhost:8000/test_db.php`

**Important** : Supprimer ce fichier après le test !

### 2. Connexion à l'application

Utilisez ces comptes de test (mot de passe : `gsb2024`) :

| Login | Rôle | Fonction |
|-------|------|----------|
| `visiteur1` | Visiteur | Saisir et consulter ses frais |
| `comptable1` | Comptable | Valider les frais |
| `admin1` | Admin | Administration |

## Dépannage

### Erreur : "Database connection failed"

**Cause** : Impossible de se connecter à MySQL

**Solutions** :
1. Vérifier que MySQL est démarré :
   ```bash
   sudo systemctl status mysql
   # ou
   sudo systemctl status mariadb
   ```

2. Vérifier les identifiants dans `include/config.php`

3. Vérifier que l'utilisateur a les droits nécessaires :
   ```sql
   SHOW GRANTS FOR 'root'@'localhost';
   ```

### Erreur : "PDO extension not found"

**Cause** : Extension PDO non installée

**Solution** :
```bash
# Ubuntu/Debian
sudo apt-get install php-pdo php-mysql

# CentOS/RHEL
sudo yum install php-pdo php-mysql

# Redémarrer le serveur web
sudo systemctl restart apache2
# ou
sudo systemctl restart nginx
```

### Erreur : "Access denied for user"

**Cause** : Identifiants MySQL incorrects

**Solution** :
1. Réinitialiser le mot de passe MySQL root
2. Vérifier `DB_USER` et `DB_PASS` dans `config.php`

### Page blanche sans erreur

**Cause** : Erreur PHP non affichée

**Solution** : Activer l'affichage des erreurs temporairement

Ajouter en haut de `index.php` :
```php
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Note** : Désactiver en production !

### Styles CSS non chargés

**Cause** : Chemin incorrect ou permissions

**Solution** :
1. Vérifier que le dossier `styles/` existe
2. Vérifier les permissions :
   ```bash
   chmod 644 styles/style.css
   ```

### Sessions ne fonctionnent pas

**Cause** : Permissions du dossier de sessions

**Solution** :
```bash
# Vérifier le dossier de sessions
php -r "echo session_save_path();"

# Ajuster les permissions si nécessaire
sudo chmod 1733 /var/lib/php/sessions
```

## Configuration avancée

### Modifier le timeout de session

Dans `include/config.php` :
```php
define('SESSION_TIMEOUT', 7200); // 2 heures
```

### Activer HTTPS (recommandé en production)

1. Obtenir un certificat SSL
2. Configurer Apache/Nginx pour HTTPS
3. Forcer HTTPS en ajoutant dans `index.php` :
```php
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

### Mode production

1. Désactiver l'affichage des erreurs
2. Utiliser un utilisateur MySQL dédié
3. Activer HTTPS
4. Configurer des logs appropriés
5. Mettre à jour les mots de passe par défaut

## Support

Pour toute question ou problème :
- Consulter le fichier README.md
- Vérifier les logs PHP et MySQL
- Consulter la documentation PHP : https://www.php.net/

## Prochaines étapes

Après l'installation :
1. Se connecter avec un compte de test
2. Explorer les différentes fonctionnalités
3. Modifier les mots de passe par défaut
4. Adapter l'application selon vos besoins
