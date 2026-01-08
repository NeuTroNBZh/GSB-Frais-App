# GSB-Frais-App

Solution web de gestion et de suivi des frais engagés pour le laboratoire pharmaceutique Galaxy Swiss Bourdin (GSB). Projet d'étude.

## Description

Application web PHP MVC pour la gestion des notes de frais des visiteurs médicaux du laboratoire GSB. L'application permet de :
- Saisir et suivre les frais (forfait et hors forfait)
- Valider et gérer le cycle de remboursement des frais
- Gérer différents rôles d'utilisateurs (Visiteur, Comptable, Admin)

## Architecture

### Structure des dossiers
```
GSB-Frais-App/
├── index.php           # Front Controller - Point d'entrée principal
├── controleurs/        # Contrôleurs de l'application
│   ├── connexion.php
│   ├── deconnexion.php
│   ├── accueil.php
│   ├── visiteur.php
│   ├── comptable.php
│   └── admin.php
├── vues/              # Vues (.inc.php)
│   ├── header.inc.php
│   ├── footer.inc.php
│   ├── menu.inc.php
│   ├── connexion.inc.php
│   ├── accueil.inc.php
│   ├── visiteur/
│   ├── comptable/
│   └── admin/
├── include/           # Classes et fonctions (PDO, modèles)
│   ├── config.php
│   ├── Database.php
│   ├── User.php
│   ├── Frais.php
│   └── functions.php
├── styles/            # Feuilles de style (GSB blue theme)
│   └── style.css
└── database/          # Scripts SQL
    └── schema.sql
```

## Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur / MariaDB 10.2 ou supérieur
- Serveur web (Apache, Nginx, ou PHP built-in server)
- Extension PHP PDO MySQL activée

## Installation

### 1. Cloner le dépôt
```bash
git clone https://github.com/NeuTroNBZh/GSB-Frais-App.git
cd GSB-Frais-App
```

### 2. Créer la base de données
```bash
mysql -u root -p < database/schema.sql
```

### 3. Configurer la connexion à la base de données
Modifier le fichier `include/config.php` selon votre configuration :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gsb_frais');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Lancer l'application
Avec le serveur PHP intégré :
```bash
php -S localhost:8000
```

Accéder à l'application : `http://localhost:8000`

## Comptes de démonstration

Mot de passe par défaut : `gsb2024`

| Utilisateur | Login | Rôle |
|------------|-------|------|
| Jean Dupont | visiteur1 | Visiteur |
| Sophie Martin | comptable1 | Comptable |
| Pierre Bernard | admin1 | Admin |

## Fonctionnalités

### Rôle Visiteur
- Saisir les frais mensuels (forfait et hors forfait)
- Consulter ses fiches de frais
- Suivre le statut de remboursement

### Rôle Comptable
- Consulter les fiches de frais de tous les visiteurs
- Valider les fiches de frais
- Gérer le cycle de statut : En cours → Cloturé → Validé → Remboursé

### Rôle Admin
- Gérer les utilisateurs (à développer)
- Consulter les rapports (à développer)

## Workflow des frais

1. **En cours** : Le visiteur saisit ses frais
2. **Cloturé** : Fin du mois, la fiche est clôturée
3. **Validé** : Le comptable valide la fiche
4. **Remboursé** : Le remboursement est effectué

## Conventions de code

### Nommage
- **camelCase** pour les variables et fonctions PHP
- Préfixes pour les formulaires :
  - `txt` : Champs de texte (input type="text")
  - `lst` : Listes déroulantes (select)
  - `frm` : Formulaires (form)
- Terme **"hors forfait"** utilisé systématiquement

### Formulaires
- Champs obligatoires marqués avec un point rouge (●)
- Classe CSS `.required` pour le marquage visuel

### Design
- Thème GSB bleu (#003366, #0055aa)
- Résolution cible : 1024x768
- Menu latéral (sidebar)
- Pas de frames

### Sécurité
- Utilisation de PDO avec requêtes préparées
- Protection contre les injections SQL
- Hashage des mots de passe avec password_hash()
- Sanitisation des entrées utilisateur

### Documentation
- PHPDoc pour toutes les classes et fonctions
- Commentaires explicatifs dans le code

## Technologies utilisées

- **Backend** : PHP 7.4+
- **Base de données** : MySQL/MariaDB avec PDO
- **Frontend** : HTML5, CSS3
- **Architecture** : MVC (Model-View-Controller)
- **Pattern** : Front Controller, Singleton (Database)

## Développement

### Ajouter un nouveau type de frais forfait
1. Insérer dans la table `frais_forfait`
2. Le formulaire de saisie l'affichera automatiquement

### Ajouter une nouvelle action
1. Ajouter le case dans `index.php`
2. Créer le contrôleur dans `/controleurs`
3. Créer la vue dans `/vues`
4. Ajouter le lien dans le menu si nécessaire

## License

MIT License - voir le fichier LICENSE pour plus de détails.
