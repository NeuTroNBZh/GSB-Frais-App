# GSB-Frais-App

> Projet d'étude — application web de gestion des frais engagés pour le laboratoire pharmaceutique fictif Galaxy Swiss Bourdin (GSB).

[![PHP](https://img.shields.io/badge/PHP-8-777BB4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1.svg)](https://www.mysql.com/)

## À propos

Ce projet a été réalisé dans le cadre d'un cursus BTS SIO. Il reprend un cas d'étude classique de la formation (GSB) : les visiteurs médicaux saisissent leurs frais de déplacement mensuels, les comptables les valident, et l'administration gère les utilisateurs et consulte des rapports.

**Ce n'est pas un projet destiné à évoluer** — il est publié tel quel, pour être consultable comme exemple de mon travail.

## Fonctionnalités

- **Authentification** par rôle : visiteur médical, comptable, admin
- **Visiteur** : saisie des frais au forfait (étape, kilométrique, nuitée, repas) et hors forfait, suivi des fiches de frais par mois
- **Comptable** : liste et validation des fiches de frais, détail par visiteur, gestion du parc automobile
- **Admin** : gestion des utilisateurs, rapports
- Protection CSRF sur les formulaires, mots de passe hashés (`password_hash`), échappement des sorties (`htmlspecialchars`)

## Stack technique

| Techno | Usage |
|--------|-------|
| PHP 8 (sans framework) | Architecture MVC maison, front controller (`index.php`) |
| MySQL / MariaDB | Stockage (schéma dans `database/schema.sql`) |
| PDO | Accès base de données |
| CSS natif | Mise en forme |

## Structure du projet

```
GSB-Frais-App/
├── index.php              # Front controller, routage par ?action=
├── install.php            # Script d'installation de la base
├── controleurs/           # Un contrôleur par domaine (connexion, visiteur, comptable, admin)
├── include/                # Modèles (User, Frais), accès DB, fonctions utilitaires
├── vues/                   # Vues, organisées par rôle
├── styles/                 # CSS
└── database/schema.sql     # Schéma + jeu de données de démonstration
```

## Installation locale

Prérequis : PHP 8+, MySQL/MariaDB, un serveur web (ou `php -S`).

1. Cloner le dépôt et servir le dossier avec PHP :
   ```bash
   git clone https://github.com/NeuTroNBZh/GSB-Frais-App.git
   cd GSB-Frais-App
   php -S localhost:8000
   ```
2. Créer la base et les tables :
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   (ou lancer `install.php` dans le navigateur, qui fait la même chose via une interface)
3. Adapter si besoin les identifiants de connexion dans `include/config.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
4. `database/schema.sql` insère des comptes de démonstration (visiteurs, comptable, admin) — voir le fichier pour les identifiants.

## Licence

Projet scolaire, publié à titre de portfolio. Pas de licence open source associée.
