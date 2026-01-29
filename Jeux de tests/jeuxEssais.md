# Jeu d’essais – Application de gestion de fiches de remboursement

# Sommaire

- [Jeu d’essais – Application de gestion de fiches de remboursement](#jeu-dessais--application-de-gestion-de-fiches-de-remboursement)
- [Sommaire](#sommaire)
  - [Objectifs du test](#objectifs-du-test)
- [Partie 1 : Protocole de Connexion](#partie-1--protocole-de-connexion)
  - [1. Objectif](#1-objectif)
  - [2. Pré-requis](#2-pré-requis)
  - [3. Scénarios de test](#3-scénarios-de-test)
    - [3.1 Connexion réussie](#31-connexion-réussie)
    - [3.2 Connexion échouée](#32-connexion-échouée)
    - [3.3 Redirection selon le rôle](#33-redirection-selon-le-rôle)
  - [4. Cas de test détaillés](#4-cas-de-test-détaillés)




## Objectifs du test
- S'assurer du bon fonctionnement et de la sécurité de l'interface de connexion
- S'assurer que les fonctions de gestion des utilisateurs 


# Partie 1 : Protocole de Connexion

## 1. Objectif
Vérifier que la fonctionnalité de connexion fonctionne correctement pour les deux rôles de l’application :
- Visiteur médical
- Comptable

## 2. Pré-requis
- Comptes de test créés dans la base de données
- Application accessible via l’URL de test
- Navigateur fonctionnel

## 3. Scénarios de test
### 3.1 Connexion réussie
- Connexion avec un compte visiteur médical valide
- Connexion avec un compte comptable valide

### 3.2 Connexion échouée
- Identifiant incorrect
- Mot de passe incorrect
- Champs vides

### 3.3 Redirection selon le rôle
- Visiteur médical → page de saisie/consultation des frais
- Comptable → page de suivi comptable

## 4. Cas de test détaillés
Les cas de test sont listés dans le fichier Excel : **jeu_tests.xlsx**, onglet **Connexion**.