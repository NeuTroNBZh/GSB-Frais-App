<?php
/**
 * Frais (Expenses) model class
 * Handles expense management and workflow
 * 
 * @author GSB
 * @version 1.0
 */

class Frais {
    /**
     * @var PDO Database connection
     */
    private $db;
    
    /**
     * Expense status constants
     */
    const STATUS_EN_COURS = 'En cours';
    const STATUS_CLOTURE = 'Cloturé';
    const STATUS_VALIDE = 'Validé';
    const STATUS_REMBOURSE = 'Remboursé';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get expense sheets for a visitor
     * 
     * @param int $visiteurId Visitor ID
     * @param string|null $statut Optional status filter
     * @return array Array of expense sheets
     */
    public function getFichesFraisByVisiteur($visiteurId, $statut = null) {
        $sql = "SELECT ff.id, ff.mois, ff.montant_valide, ff.nb_justificatifs, ff.statut, ff.date_modif
                FROM fiche_frais ff
                WHERE ff.visiteur_id = :visiteur_id";
        
        if ($statut !== null) {
            $sql .= " AND ff.statut = :statut";
        }
        
        $sql .= " ORDER BY ff.mois DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':visiteur_id', $visiteurId, PDO::PARAM_INT);
        
        if ($statut !== null) {
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get expense sheet by ID
     * 
     * @param int $ficheId Expense sheet ID
     * @return array|bool Expense sheet data or false
     */
    public function getFicheFraisById($ficheId) {
        $sql = "SELECT ff.*, u.nom, u.prenom
                FROM fiche_frais ff
                JOIN utilisateurs u ON ff.visiteur_id = u.id
                WHERE ff.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ficheId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Get forfait expenses for a sheet
     * 
     * @param int $ficheId Expense sheet ID
     * @return array Array of forfait expenses
     */
    public function getFraisForfait($ficheId) {
        $sql = "SELECT lff.*, ff.libelle
                FROM ligne_frais_forfait lff
                JOIN frais_forfait ff ON lff.frais_forfait_id = ff.id
                WHERE lff.fiche_frais_id = :fiche_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get hors forfait expenses for a sheet
     * 
     * @param int $ficheId Expense sheet ID
     * @return array Array of hors forfait expenses
     */
    public function getFraisHorsForfait($ficheId) {
        $sql = "SELECT *
                FROM ligne_frais_hors_forfait
                WHERE fiche_frais_id = :fiche_id
                ORDER BY date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Create new expense sheet
     * 
     * @param int $visiteurId Visitor ID
     * @param string $mois Month (YYYY-MM format)
     * @return int|bool New sheet ID or false
     */
    public function createFicheFrais($visiteurId, $mois) {
        $sql = "INSERT INTO fiche_frais (visiteur_id, mois, nb_justificatifs, montant_valide, statut, date_modif)
                VALUES (:visiteur_id, :mois, 0, 0, :statut, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':visiteur_id', $visiteurId, PDO::PARAM_INT);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->bindValue(':statut', self::STATUS_EN_COURS, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update expense sheet status
     * 
     * @param int $ficheId Expense sheet ID
     * @param string $statut New status
     * @return bool Success status
     */
    public function updateStatut($ficheId, $statut) {
        $sql = "UPDATE fiche_frais 
                SET statut = :statut, date_modif = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
        $stmt->bindParam(':id', $ficheId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Add hors forfait expense
     * 
     * @param int $ficheId Expense sheet ID
     * @param string $date Expense date
     * @param string $libelle Description
     * @param float $montant Amount
     * @return bool Success status
     */
    public function addFraisHorsForfait($ficheId, $date, $libelle, $montant) {
        $sql = "INSERT INTO ligne_frais_hors_forfait (fiche_frais_id, date, libelle, montant)
                VALUES (:fiche_id, :date, :libelle, :montant)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':libelle', $libelle, PDO::PARAM_STR);
        $stmt->bindParam(':montant', $montant, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    /**
     * Update forfait expense quantity
     * 
     * @param int $ficheId Expense sheet ID
     * @param int $fraisForfaitId Forfait type ID
     * @param int $quantite Quantity
     * @return bool Success status
     */
    public function updateFraisForfait($ficheId, $fraisForfaitId, $quantite) {
        // Check if line exists
        $sql = "SELECT id FROM ligne_frais_forfait 
                WHERE fiche_frais_id = :fiche_id AND frais_forfait_id = :forfait_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
        $stmt->bindParam(':forfait_id', $fraisForfaitId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            // Update
            $sql = "UPDATE ligne_frais_forfait 
                    SET quantite = :quantite 
                    WHERE fiche_frais_id = :fiche_id AND frais_forfait_id = :forfait_id";
        } else {
            // Insert
            $sql = "INSERT INTO ligne_frais_forfait (fiche_frais_id, frais_forfait_id, quantite)
                    VALUES (:fiche_id, :forfait_id, :quantite)";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
        $stmt->bindParam(':forfait_id', $fraisForfaitId, PDO::PARAM_INT);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Get all forfait types
     * 
     * @return array Array of forfait types
     */
    public function getFraisForfaitTypes() {
        $sql = "SELECT * FROM frais_forfait ORDER BY id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
