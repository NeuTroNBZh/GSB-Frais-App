<div class="card">
    <div class="card-header">
        <h2>Détail de la fiche de frais</h2>
    </div>
    
    <p><a href="index.php?action=listeFraisVisiteur&visiteur=<?php echo $fiche['visiteur_id']; ?>" class="btn btn-secondary">← Retour</a></p>
    
    <h3>Informations générales</h3>
    <table>
        <tr>
            <th>Visiteur</th>
            <td><?php echo htmlspecialchars($fiche['nom'] . ' ' . $fiche['prenom']); ?></td>
        </tr>
        <tr>
            <th>Mois</th>
            <td><?php echo htmlspecialchars($fiche['mois']); ?></td>
        </tr>
        <tr>
            <th>Statut</th>
            <td>
                <?php
                $badgeClass = '';
                switch ($fiche['statut']) {
                    case Frais::STATUS_EN_COURS:
                        $badgeClass = 'badge-en-cours';
                        break;
                    case Frais::STATUS_CLOTURE:
                        $badgeClass = 'badge-cloture';
                        break;
                    case Frais::STATUS_VALIDE:
                        $badgeClass = 'badge-valide';
                        break;
                    case Frais::STATUS_REMBOURSE:
                        $badgeClass = 'badge-rembourse';
                        break;
                }
                ?>
                <span class="badge <?php echo $badgeClass; ?>">
                    <?php echo htmlspecialchars($fiche['statut']); ?>
                </span>
            </td>
        </tr>
        <tr>
            <th>Montant validé</th>
            <td><?php echo number_format($fiche['montant_valide'], 2, ',', ' '); ?> €</td>
        </tr>
        <tr>
            <th>Nb justificatifs</th>
            <td><?php echo htmlspecialchars($fiche['nb_justificatifs']); ?></td>
        </tr>
    </table>
    
    <hr>
    
    <h3>Frais au forfait</h3>
    <?php if (empty($fraisForfait)): ?>
        <p>Aucun frais forfaitaire.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Quantité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fraisForfait as $frais): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($frais['libelle']); ?></td>
                        <td><?php echo htmlspecialchars($frais['quantite']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <hr>
    
    <h3>Frais hors forfait</h3>
    <?php if (empty($fraisHorsForfait)): ?>
        <p>Aucun frais hors forfait.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fraisHorsForfait as $frais): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($frais['date']); ?></td>
                        <td><?php echo htmlspecialchars($frais['libelle']); ?></td>
                        <td><?php echo number_format($frais['montant'], 2, ',', ' '); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <hr>
    
    <h3>Modifier le statut</h3>
    <form action="index.php?action=validerFiche" method="POST" id="frmValidation">
        <input type="hidden" name="fiche_id" value="<?php echo $fiche['id']; ?>">
        
        <div class="form-group">
            <label for="lstStatut" class="required">Nouveau statut</label>
            <select id="lstStatut" name="lstStatut" required>
                <option value="">-- Sélectionnez un statut --</option>
                <option value="<?php echo Frais::STATUS_EN_COURS; ?>" <?php echo $fiche['statut'] === Frais::STATUS_EN_COURS ? 'selected' : ''; ?>>
                    <?php echo Frais::STATUS_EN_COURS; ?>
                </option>
                <option value="<?php echo Frais::STATUS_CLOTURE; ?>" <?php echo $fiche['statut'] === Frais::STATUS_CLOTURE ? 'selected' : ''; ?>>
                    <?php echo Frais::STATUS_CLOTURE; ?>
                </option>
                <option value="<?php echo Frais::STATUS_VALIDE; ?>" <?php echo $fiche['statut'] === Frais::STATUS_VALIDE ? 'selected' : ''; ?>>
                    <?php echo Frais::STATUS_VALIDE; ?>
                </option>
                <option value="<?php echo Frais::STATUS_REMBOURSE; ?>" <?php echo $fiche['statut'] === Frais::STATUS_REMBOURSE ? 'selected' : ''; ?>>
                    <?php echo Frais::STATUS_REMBOURSE; ?>
                </option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-success">Mettre à jour le statut</button>
    </form>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
