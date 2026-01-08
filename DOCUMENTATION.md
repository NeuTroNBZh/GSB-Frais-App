# GSB-Frais-App - Documentation Technique

## Table des matières
1. [Architecture](#architecture)
2. [Conventions de code](#conventions-de-code)
3. [Structure de la base de données](#structure-de-la-base-de-données)
4. [Sécurité](#sécurité)
5. [Workflow des frais](#workflow-des-frais)
6. [Guide du développeur](#guide-du-développeur)

## Architecture

### Pattern MVC (Model-View-Controller)

L'application suit le pattern MVC avec un Front Controller :

```
Requête HTTP
    ↓
index.php (Front Controller)
    ↓
Routage vers Contrôleur
    ↓
Contrôleur traite la logique métier
    ↓
Modèle (accès données via PDO)
    ↓
Vue (.inc.php) génère le HTML
    ↓
Réponse HTTP
```

#### Composants

**Front Controller** (`index.php`)
- Point d'entrée unique
- Gestion du routage via paramètre `action`
- Chargement des dépendances
- Démarrage de session

**Modèles** (`/include`)
- `Database.php` : Singleton pour connexion PDO
- `User.php` : Gestion des utilisateurs et authentification
- `Frais.php` : Gestion des fiches de frais
- `functions.php` : Fonctions utilitaires

**Contrôleurs** (`/controleurs`)
- `connexion.php` : Authentification
- `visiteur.php` : Actions visiteur
- `comptable.php` : Actions comptable
- `admin.php` : Actions administrateur

**Vues** (`/vues`)
- Fichiers `.inc.php` (inclusions PHP)
- Organisation par rôle dans sous-dossiers

## Conventions de code

### Nommage

#### Variables et fonctions : camelCase
```php
$userName = "Jean";
$fichesFrais = [];

function getUtilisateurConnecte() {
    // ...
}
```

#### Classes : PascalCase
```php
class Database { }
class User { }
class Frais { }
```

#### Constantes : UPPER_SNAKE_CASE
```php
define('DB_HOST', 'localhost');
const STATUS_EN_COURS = 'En cours';
```

### Préfixes de formulaires

Les éléments de formulaires utilisent des préfixes standardisés :

#### `txt` - Champs de texte
```html
<input type="text" id="txtLogin" name="txtLogin">
<input type="password" id="txtPassword" name="txtPassword">
<input type="date" id="txtDateHorsForfait" name="txtDateHorsForfait">
<input type="number" id="txtMontantHorsForfait" name="txtMontantHorsForfait">
```

#### `lst` - Listes déroulantes (select)
```html
<select id="lstVisiteur" name="lstVisiteur">
    <option value="1">Visiteur 1</option>
</select>
```

#### `frm` - Formulaires
```html
<form id="frmConnexion" method="POST">
    <!-- ... -->
</form>
```

### Champs obligatoires

Marqués avec la classe CSS `.required` qui affiche un point rouge :

```html
<label for="txtLogin" class="required">Identifiant</label>
```

CSS correspondant :
```css
.required::after {
    content: ' ●';
    color: var(--gsb-red-required);
    font-size: 8px;
    vertical-align: super;
}
```

### Terminologie : "hors forfait"

Toujours utiliser **"hors forfait"** (et non "exceptionnels" ou autre) :
- Nom de table : `ligne_frais_hors_forfait`
- Variables : `$fraisHorsForfait`
- Fonctions : `getFraisHorsForfait()`
- Vues : Texte "Frais hors forfait"

### Documentation PHPDoc

Toutes les classes, méthodes et fonctions doivent avoir une documentation :

```php
/**
 * Authenticate user with login and password
 * 
 * @param string $login User login
 * @param string $password User password
 * @return array|bool User data array if success, false otherwise
 */
public function authenticate($login, $password) {
    // Implementation
}
```

## Structure de la base de données

### Schéma relationnel

```
utilisateurs
├── id (PK)
├── login
├── mot_de_passe
├── nom
├── prenom
└── role (ENUM: visiteur, comptable, admin)

frais_forfait
├── id (PK)
├── code
├── libelle
└── montant

fiche_frais
├── id (PK)
├── visiteur_id (FK → utilisateurs)
├── mois (YYYY-MM)
├── nb_justificatifs
├── montant_valide
├── statut (ENUM: En cours, Cloturé, Validé, Remboursé)
└── date_modif

ligne_frais_forfait
├── id (PK)
├── fiche_frais_id (FK → fiche_frais)
├── frais_forfait_id (FK → frais_forfait)
└── quantite

ligne_frais_hors_forfait
├── id (PK)
├── fiche_frais_id (FK → fiche_frais)
├── date
├── libelle
└── montant
```

### Relations

- Un utilisateur peut avoir plusieurs fiches de frais
- Une fiche de frais appartient à un utilisateur
- Une fiche de frais contient plusieurs lignes de frais forfait
- Une fiche de frais contient plusieurs lignes de frais hors forfait

## Sécurité

### Protection SQL Injection

**✓ Utiliser PDO avec requêtes préparées**
```php
$sql = "SELECT * FROM utilisateurs WHERE login = :login";
$stmt = $this->db->prepare($sql);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->execute();
```

**✗ JAMAIS de concaténation directe**
```php
// NE JAMAIS FAIRE CECI !
$sql = "SELECT * FROM utilisateurs WHERE login = '$login'";
```

### Protection XSS

**Sanitisation des entrées**
```php
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$login = sanitize($_POST['txtLogin']);
```

**Échappement en sortie**
```php
<?php echo htmlspecialchars($user['nom']); ?>
```

### Authentification et sessions

**Hashage des mots de passe**
```php
// Création
$hash = password_hash($password, PASSWORD_DEFAULT);

// Vérification
if (password_verify($password, $hash)) {
    // OK
}
```

**Gestion de session sécurisée**
```php
// Démarrage
session_start();

// Stockage utilisateur
$_SESSION['user'] = $userData;

// Vérification
function estConnecte() {
    return isset($_SESSION['user']);
}

// Nettoyage
session_unset();
session_destroy();
```

### Contrôle d'accès basé sur les rôles

```php
// Vérification authentification
requireAuthentication();

// Vérification rôle spécifique
requireRole(User::ROLE_VISITOR);
requireRole(User::ROLE_ACCOUNTANT);
requireRole(User::ROLE_ADMIN);
```

## Workflow des frais

### Cycle de vie d'une fiche de frais

```
1. En cours
   ↓ (Visiteur saisit ses frais)
   │
2. Cloturé
   ↓ (Fin du mois)
   │
3. Validé
   ↓ (Comptable valide)
   │
4. Remboursé
   (Traitement terminé)
```

### États et transitions

| État actuel | Action | État suivant | Acteur |
|------------|--------|--------------|--------|
| - | Création | En cours | Système |
| En cours | Saisie frais | En cours | Visiteur |
| En cours | Clôture mois | Cloturé | Système/Comptable |
| Cloturé | Validation | Validé | Comptable |
| Validé | Remboursement | Remboursé | Comptable |

### Permissions par rôle

**Visiteur**
- ✓ Créer fiche de frais
- ✓ Modifier fiche "En cours"
- ✓ Consulter ses fiches
- ✗ Modifier statut

**Comptable**
- ✓ Consulter toutes les fiches
- ✓ Modifier statut
- ✗ Créer fiche pour un visiteur

**Admin**
- ✓ Toutes les permissions
- ✓ Gérer utilisateurs

## Guide du développeur

### Ajouter une nouvelle page

1. **Créer le contrôleur**
```php
// controleurs/maNouvellePage.php
<?php
requireAuthentication();

$user = getUtilisateurConnecte();

// Logique métier...

require_once 'vues/header.inc.php';
require_once 'vues/menu.inc.php';
require_once 'vues/maNouvellePage.inc.php';
require_once 'vues/footer.inc.php';
```

2. **Créer la vue**
```php
// vues/maNouvellePage.inc.php
<div class="card">
    <div class="card-header">
        <h2>Ma Nouvelle Page</h2>
    </div>
    <!-- Contenu -->
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
```

3. **Ajouter la route dans index.php**
```php
case 'maNouvellePage':
    require_once 'controleurs/maNouvellePage.php';
    break;
```

4. **Ajouter au menu** (si nécessaire)
```php
// vues/menu.inc.php
<li><a href="index.php?action=maNouvellePage">Ma Page</a></li>
```

### Ajouter un nouveau modèle

```php
// include/MonModele.php
<?php
/**
 * Mon modèle de données
 * 
 * @author GSB
 * @version 1.0
 */

class MonModele {
    /**
     * @var PDO Database connection
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Méthode exemple
     * 
     * @param int $id ID de l'entité
     * @return array|bool Données ou false
     */
    public function getById($id) {
        $sql = "SELECT * FROM ma_table WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
}
```

### Ajouter un type de frais forfait

```sql
INSERT INTO frais_forfait (code, libelle, montant) 
VALUES ('TRN', 'Transport en commun', 5.00);
```

Le formulaire l'affichera automatiquement.

### Créer un nouveau style de badge

```css
.badge-mon-statut {
    background-color: #couleur;
    color: var(--gsb-white);
}
```

Utilisation :
```php
<span class="badge badge-mon-statut">Mon Statut</span>
```

### Bonnes pratiques

1. **Toujours utiliser PDO préparé**
2. **Sanitiser les entrées utilisateur**
3. **Échapper les sorties HTML**
4. **Documenter avec PHPDoc**
5. **Respecter les conventions de nommage**
6. **Tester avec différents rôles**
7. **Vérifier les permissions**
8. **Logger les erreurs importantes**

### Débogage

**Activer les erreurs PHP** (développement uniquement)
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Logger les erreurs**
```php
error_log("Message de debug: " . print_r($variable, true));
```

**Vérifier les requêtes SQL**
```php
$stmt->debugDumpParams();
```

## Ressources

- [PHP Manual](https://www.php.net/manual/fr/)
- [PDO Documentation](https://www.php.net/manual/fr/book.pdo.php)
- [HTML/CSS Reference](https://developer.mozilla.org/fr/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

## Changelog

### Version 1.0 (Initial)
- Architecture MVC complète
- Gestion des 3 rôles (Visiteur, Comptable, Admin)
- Workflow complet des frais
- Thème GSB bleu responsive
- Sécurité PDO + password hashing
- Documentation complète
