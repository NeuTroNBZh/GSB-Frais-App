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
    public function getFichesFraisByVisiteur($visiteurId, $statut = null, $limit = null, $offset = 0) {
        $sql = "SELECT ff.id, ff.mois, ff.montant_valide, ff.nb_justificatifs, ff.statut, ff.date_modif
                FROM fiche_frais ff
                WHERE ff.visiteur_id = :visiteur_id";
        
        if ($statut !== null) {
            $sql .= " AND ff.statut = :statut";
        }
        
        $sql .= " ORDER BY ff.mois DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':visiteur_id', $visiteurId, PDO::PARAM_INT);
        
        if ($statut !== null) {
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
        }

        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count expense sheets for a visitor
     *
     * @param int $visiteurId Visitor ID
     * @param string|null $statut Optional status filter
     * @return int Total sheets
     */
    public function countFichesFraisByVisiteur($visiteurId, $statut = null) {
        $sql = "SELECT COUNT(*)
                FROM fiche_frais ff
                WHERE ff.visiteur_id = :visiteur_id";

        if ($statut !== null) {
            $sql .= " AND ff.statut = :statut";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':visiteur_id', $visiteurId, PDO::PARAM_INT);

        if ($statut !== null) {
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Get one expense sheet for a visitor and month
     *
     * @param int $visiteurId Visitor ID
     * @param string $mois Month in YYYY-MM format
     * @return array|bool Expense sheet data or false
     */
    public function getFicheFraisByVisiteurAndMois($visiteurId, $mois) {
        $sql = "SELECT id, visiteur_id, mois, nb_justificatifs, montant_valide, statut, date_modif
                FROM fiche_frais
                WHERE visiteur_id = :visiteur_id AND mois = :mois";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':visiteur_id', $visiteurId, PDO::PARAM_INT);
        $stmt->bindParam(':mois', $mois, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch();
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
     * Get one hors forfait expense line with its parent sheet context
     *
     * @param int $ligneId Line ID
     * @return array|bool Expense line data or false
     */
    public function getLigneFraisHorsForfaitById($ligneId) {
        $sql = "SELECT lfhf.*, ff.visiteur_id, ff.statut
                FROM ligne_frais_hors_forfait lfhf
                INNER JOIN fiche_frais ff ON lfhf.fiche_frais_id = ff.id
                WHERE lfhf.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ligneId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
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
     * Automatically close previous months still marked as open
     *
     * @param int $visiteurId Visitor ID
     * @param string $moisCourant Current month in YYYY-MM format
     * @return bool Success status
     */
    public function cloturerFichesAnciennes($visiteurId, $moisCourant) {
        $sql = "UPDATE fiche_frais
                SET statut = :statut_cloture, date_modif = NOW()
                WHERE visiteur_id = :visiteur_id
                  AND statut = :statut_en_cours
                  AND mois < :mois_courant";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':statut_cloture', self::STATUS_CLOTURE, PDO::PARAM_STR);
        $stmt->bindValue(':statut_en_cours', self::STATUS_EN_COURS, PDO::PARAM_STR);
        $stmt->bindParam(':visiteur_id', $visiteurId, PDO::PARAM_INT);
        $stmt->bindParam(':mois_courant', $moisCourant, PDO::PARAM_STR);

        return $stmt->execute();
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
     * Delete an entire expense sheet and its lines
     *
     * @param int $ficheId Expense sheet ID
     * @return bool Success status
     */
    public function deleteFicheFrais($ficheId) {
        $sql = "DELETE FROM fiche_frais WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ficheId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Delete one hors forfait expense line
     *
     * @param int $ligneId Line ID
     * @return bool Success status
     */
    public function deleteFraisHorsForfait($ligneId) {
        $sql = "DELETE FROM ligne_frais_hors_forfait WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $ligneId, PDO::PARAM_INT);

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

    /**
     * Get the vehicle assigned to a visitor
     *
     * @param int $visiteurId Visitor ID
     * @return array|bool Vehicle data or false
     */
    public function getVehiculeByVisiteurId($visiteurId) {
        $sql = "SELECT immatriculation, date_attribution
                FROM vehicule
                WHERE id_visiteur = :visiteur_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':visiteur_id', $visiteurId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get vehicle fleet list with assigned visitor names
     *
     * @return array List of vehicles with visitor
     */
    public function getParcAutomobileListe() {
        $sql = "SELECT v.immatriculation, v.date_attribution, u.id AS visiteur_id, u.nom, u.prenom
                FROM vehicule v
                INNER JOIN utilisateurs u ON u.id = v.id_visiteur
                WHERE u.role = :role_visiteur
                ORDER BY v.immatriculation ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role_visiteur', User::ROLE_VISITOR, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get visitors who have no vehicle assigned
     *
     * @return array List of visitors without a vehicle
     */
    public function getVisiteursSansVehicule() {
        $sql = "SELECT u.id, u.nom, u.prenom
                FROM utilisateurs u
                WHERE u.role = :role_visiteur
                  AND u.id NOT IN (SELECT id_visiteur FROM vehicule)
                ORDER BY u.nom ASC, u.prenom ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role_visiteur', User::ROLE_VISITOR, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Add a new vehicle to the fleet
     *
     * @param string $immatriculation Vehicle registration plate
     * @param int $visiteurId Visitor ID
     * @param string $dateAttribution Date of attribution (YYYY-MM-DD)
     * @return bool Success status
     */
    public function ajouterVehicule($immatriculation, $visiteurId, $dateAttribution) {
        $sql = "INSERT INTO vehicule (immatriculation, id_visiteur, date_attribution)
                VALUES (:immatriculation, :id_visiteur, :date_attribution)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':immatriculation', $immatriculation, PDO::PARAM_STR);
        $stmt->bindValue(':id_visiteur', $visiteurId, PDO::PARAM_INT);
        $stmt->bindValue(':date_attribution', $dateAttribution, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Delete a vehicle from the fleet by its registration plate
     *
     * @param string $immatriculation Vehicle registration plate
     * @return bool Success status
     */
    public function supprimerVehicule($immatriculation) {
        $sql = "DELETE FROM vehicule WHERE immatriculation = :immatriculation";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':immatriculation', $immatriculation, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Recalculate and persist the validated amount for a sheet
     *
     * @param int $ficheId Expense sheet ID
     * @return bool Success status
     */
    public function recalculerMontantValide($ficheId) {
        $sqlForfait = "SELECT COALESCE(SUM(lff.quantite * ff.montant), 0)
                       FROM ligne_frais_forfait lff
                       INNER JOIN frais_forfait ff ON lff.frais_forfait_id = ff.id
                       WHERE lff.fiche_frais_id = :fiche_id";

        $stmtForfait = $this->db->prepare($sqlForfait);
        $stmtForfait->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
        $stmtForfait->execute();
        $totalForfait = (float) $stmtForfait->fetchColumn();

        $sqlHorsForfait = "SELECT COALESCE(SUM(montant), 0)
                           FROM ligne_frais_hors_forfait
                           WHERE fiche_frais_id = :fiche_id";

        $stmtHorsForfait = $this->db->prepare($sqlHorsForfait);
        $stmtHorsForfait->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);
        $stmtHorsForfait->execute();
        $totalHorsForfait = (float) $stmtHorsForfait->fetchColumn();

        $montantValide = $totalForfait + $totalHorsForfait;

        $sqlUpdate = "UPDATE fiche_frais
                      SET montant_valide = :montant_valide, date_modif = NOW()
                      WHERE id = :fiche_id";

        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':montant_valide', $montantValide);
        $stmtUpdate->bindParam(':fiche_id', $ficheId, PDO::PARAM_INT);

        return $stmtUpdate->execute();
    }

    /**
     * Get the list of allowed statuses
     *
     * @return array Allowed statuses
     */
    public static function getAllowedStatuses() {
        return [
            self::STATUS_EN_COURS,
            self::STATUS_CLOTURE,
            self::STATUS_VALIDE,
            self::STATUS_REMBOURSE,
        ];
    }

    /**
     * Get global report summary metrics
     *
     * @return array Summary values
     */
    public function getRapportSynthese($filtres = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtres['mois_debut'])) {
            $where[] = "ff.mois >= :mois_debut";
            $params[':mois_debut'] = $filtres['mois_debut'];
        }

        if (!empty($filtres['mois_fin'])) {
            $where[] = "ff.mois <= :mois_fin";
            $params[':mois_fin'] = $filtres['mois_fin'];
        }

        if (!empty($filtres['statut'])) {
            $where[] = "ff.statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }

        if (!empty($filtres['visiteur_id'])) {
            $where[] = "ff.visiteur_id = :visiteur_id";
            $params[':visiteur_id'] = (int) $filtres['visiteur_id'];
        }

        $sql = "SELECT
                    COUNT(*) AS nb_fiches,
                    COALESCE(SUM(montant_valide), 0) AS montant_total,
                    COALESCE(AVG(montant_valide), 0) AS montant_moyen,
                    COALESCE(SUM(nb_justificatifs), 0) AS nb_justificatifs
                FROM fiche_frais ff
                WHERE " . implode(' AND ', $where);

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':visiteur_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get report breakdown by status
     *
     * @return array Rows by status
     */
    public function getRapportParStatut($filtres = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtres['mois_debut'])) {
            $where[] = "ff.mois >= :mois_debut";
            $params[':mois_debut'] = $filtres['mois_debut'];
        }

        if (!empty($filtres['mois_fin'])) {
            $where[] = "ff.mois <= :mois_fin";
            $params[':mois_fin'] = $filtres['mois_fin'];
        }

        if (!empty($filtres['visiteur_id'])) {
            $where[] = "ff.visiteur_id = :visiteur_id";
            $params[':visiteur_id'] = (int) $filtres['visiteur_id'];
        }

        $sql = "SELECT
                    statut,
                    COUNT(*) AS nb_fiches,
                    COALESCE(SUM(montant_valide), 0) AS montant_total
                FROM fiche_frais ff
                WHERE " . implode(' AND ', $where) . "
                GROUP BY statut
                ORDER BY FIELD(statut, 'En cours', 'Cloturé', 'Validé', 'Remboursé')";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':visiteur_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get monthly report values
     *
     * @param int $limit Number of months to return
     * @return array Rows by month
     */
    public function getRapportMensuel($limit = 12, $filtres = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtres['mois_debut'])) {
            $where[] = "ff.mois >= :mois_debut";
            $params[':mois_debut'] = $filtres['mois_debut'];
        }

        if (!empty($filtres['mois_fin'])) {
            $where[] = "ff.mois <= :mois_fin";
            $params[':mois_fin'] = $filtres['mois_fin'];
        }

        if (!empty($filtres['statut'])) {
            $where[] = "ff.statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }

        if (!empty($filtres['visiteur_id'])) {
            $where[] = "ff.visiteur_id = :visiteur_id";
            $params[':visiteur_id'] = (int) $filtres['visiteur_id'];
        }

        $sql = "SELECT
                    mois,
                    COUNT(*) AS nb_fiches,
                    COALESCE(SUM(montant_valide), 0) AS montant_total,
                    COALESCE(AVG(montant_valide), 0) AS montant_moyen
                FROM fiche_frais ff
                WHERE " . implode(' AND ', $where) . "
                GROUP BY mois
                ORDER BY mois DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':visiteur_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get top visitors by validated amount
     *
     * @param int $limit Number of visitors to return
     * @return array Rows for top visitors
     */
    public function getRapportTopVisiteurs($limit = 10, $filtres = []) {
        $where = ["u.role = :role"];
        $params = [];

        if (!empty($filtres['mois_debut'])) {
            $where[] = "ff.mois >= :mois_debut";
            $params[':mois_debut'] = $filtres['mois_debut'];
        }

        if (!empty($filtres['mois_fin'])) {
            $where[] = "ff.mois <= :mois_fin";
            $params[':mois_fin'] = $filtres['mois_fin'];
        }

        if (!empty($filtres['statut'])) {
            $where[] = "ff.statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }

        if (!empty($filtres['visiteur_id'])) {
            $where[] = "u.id = :visiteur_id";
            $params[':visiteur_id'] = (int) $filtres['visiteur_id'];
        }

        $sql = "SELECT
                    u.id,
                    u.nom,
                    u.prenom,
                    COUNT(ff.id) AS nb_fiches,
                    COALESCE(SUM(ff.montant_valide), 0) AS montant_total
                FROM utilisateurs u
                LEFT JOIN fiche_frais ff ON ff.visiteur_id = u.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY u.id, u.nom, u.prenom
                ORDER BY montant_total DESC, nb_fiches DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $roleVisiteur = User::ROLE_VISITOR;
        $stmt->bindParam(':role', $roleVisiteur, PDO::PARAM_STR);
        foreach ($params as $key => $value) {
            if ($key === ':visiteur_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get detailed report lines for CSV export
     *
     * @param array $filtres Report filters
     * @return array Detailed rows
     */
    public function getRapportExportFiches($filtres = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtres['mois_debut'])) {
            $where[] = "ff.mois >= :mois_debut";
            $params[':mois_debut'] = $filtres['mois_debut'];
        }

        if (!empty($filtres['mois_fin'])) {
            $where[] = "ff.mois <= :mois_fin";
            $params[':mois_fin'] = $filtres['mois_fin'];
        }

        if (!empty($filtres['statut'])) {
            $where[] = "ff.statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }

        if (!empty($filtres['visiteur_id'])) {
            $where[] = "ff.visiteur_id = :visiteur_id";
            $params[':visiteur_id'] = (int) $filtres['visiteur_id'];
        }

        $sql = "SELECT
                    ff.id,
                    ff.mois,
                    ff.statut,
                    ff.montant_valide,
                    ff.nb_justificatifs,
                    ff.date_modif,
                    u.id AS visiteur_id,
                    u.nom,
                    u.prenom
                FROM fiche_frais ff
                INNER JOIN utilisateurs u ON u.id = ff.visiteur_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY ff.mois DESC, u.nom ASC, u.prenom ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':visiteur_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
