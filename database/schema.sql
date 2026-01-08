-- GSB Frais App Database Schema
-- Database for expense management application
-- @author GSB
-- @version 1.0

-- Create database
CREATE DATABASE IF NOT EXISTS gsb_frais CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gsb_frais;

-- Users table
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    role ENUM('visiteur', 'comptable', 'admin') NOT NULL DEFAULT 'visiteur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login (login),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Forfait expense types table
CREATE TABLE IF NOT EXISTS frais_forfait (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) UNIQUE NOT NULL,
    libelle VARCHAR(100) NOT NULL,
    montant DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expense sheets table
CREATE TABLE IF NOT EXISTS fiche_frais (
    id INT PRIMARY KEY AUTO_INCREMENT,
    visiteur_id INT NOT NULL,
    mois VARCHAR(7) NOT NULL, -- Format: YYYY-MM
    nb_justificatifs INT DEFAULT 0,
    montant_valide DECIMAL(10,2) DEFAULT 0,
    statut ENUM('En cours', 'Cloturé', 'Validé', 'Remboursé') NOT NULL DEFAULT 'En cours',
    date_modif TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (visiteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_visiteur (visiteur_id),
    INDEX idx_mois (mois),
    INDEX idx_statut (statut),
    UNIQUE KEY unique_visiteur_mois (visiteur_id, mois)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Forfait expense lines table
CREATE TABLE IF NOT EXISTS ligne_frais_forfait (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fiche_frais_id INT NOT NULL,
    frais_forfait_id INT NOT NULL,
    quantite INT DEFAULT 0,
    FOREIGN KEY (fiche_frais_id) REFERENCES fiche_frais(id) ON DELETE CASCADE,
    FOREIGN KEY (frais_forfait_id) REFERENCES frais_forfait(id) ON DELETE CASCADE,
    INDEX idx_fiche (fiche_frais_id),
    UNIQUE KEY unique_fiche_forfait (fiche_frais_id, frais_forfait_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Hors forfait expense lines table
CREATE TABLE IF NOT EXISTS ligne_frais_hors_forfait (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fiche_frais_id INT NOT NULL,
    date DATE NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (fiche_frais_id) REFERENCES fiche_frais(id) ON DELETE CASCADE,
    INDEX idx_fiche (fiche_frais_id),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default forfait types
INSERT INTO frais_forfait (code, libelle, montant) VALUES
('ETP', 'Forfait Etape', 110.00),
('KM', 'Frais Kilométrique', 0.62),
('NUI', 'Nuitée Hôtel', 80.00),
('REP', 'Repas Restaurant', 25.00);

-- Insert sample users (passwords are hashed with password_hash())
-- Default password for all users: 'gsb2024'
INSERT INTO utilisateurs (login, mot_de_passe, nom, prenom, role) VALUES
('visiteur1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont', 'Jean', 'visiteur'),
('comptable1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Martin', 'Sophie', 'comptable'),
('admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bernard', 'Pierre', 'admin');
