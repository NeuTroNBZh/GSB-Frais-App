<div class="card">
    <div class="card-header">
        <h2><?php echo $isEditing ? 'Modifier mes frais' : 'Saisir mes frais'; ?></h2>
    </div>
    
    <?php if ($message !== ''): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($erreur !== ''): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php endif; ?>
    
    <form action="index.php?action=enregistrerFrais" method="POST" id="frmSaisieFrais">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="fiche_id" value="<?php echo htmlspecialchars($ficheId); ?>">
        <input type="hidden" name="is_editing" value="<?php echo $isEditing ? '1' : '0'; ?>">
        <input type="hidden" name="selected_month" value="<?php echo htmlspecialchars($moisSelectionne); ?>">

        <?php if (!$isEditing): ?>
            <div class="form-group">
                <label for="txtMoisFiche" class="required">Mois de la fiche</label>
                <input type="month" id="txtMoisFiche" name="mois" value="<?php echo htmlspecialchars($moisSelectionne); ?>" onchange="window.location.href='index.php?action=saisirFrais&mois=' + encodeURIComponent(this.value)">
            </div>
        <?php else: ?>
            <p><strong>Mois de la fiche :</strong> <?php echo htmlspecialchars($moisSelectionne); ?></p>
        <?php endif; ?>

        <?php if (!empty($vehiculeAttribue)): ?>
            <div class="alert alert-info">
                <strong>Véhicule attribué :</strong> <?php echo htmlspecialchars($vehiculeAttribue['immatriculation']); ?>
                <br>
                Les frais kilométriques (KM) ne sont pas disponibles pour ce visiteur.
            </div>
        <?php endif; ?>
        
        <h3>Frais au forfait</h3>
        <p>Saisissez les quantités pour chaque type de frais forfaitaires du mois selectionne.</p>
        
        <?php foreach ($typesForfait as $type): ?>
            <?php
            $quantite = 0;
            foreach ($fraisForfait as $ligne) {
                if ($ligne['frais_forfait_id'] == $type['id']) {
                    $quantite = $ligne['quantite'];
                    break;
                }
            }
            ?>
            <div class="form-group">
                <label for="txtForfait<?php echo $type['id']; ?>">
                    <?php echo htmlspecialchars($type['libelle']); ?>
                </label>
                <input type="number" 
                       id="txtForfait<?php echo htmlspecialchars($type['id']); ?>" 
                       name="txtForfait<?php echo htmlspecialchars($type['id']); ?>" 
                       value="<?php echo htmlspecialchars($quantite); ?>" 
                       min="0" 
                       step="1">
            </div>
        <?php endforeach; ?>
        
        <hr>
        
        <h3>Frais hors forfait</h3>
        <p>Ajoutez un nouveau frais hors forfait.</p>
        
        <div class="form-group">
            <label for="txtDateHorsForfait" class="required">Date</label>
            <input type="date" id="txtDateHorsForfait" name="txtDateHorsForfait" max="<?php echo htmlspecialchars(date('Y-m-d')); ?>">
        </div>
        
        <div class="form-group">
            <label for="txtLibelleHorsForfait" class="required">Libellé</label>
            <input type="text" id="txtLibelleHorsForfait" name="txtLibelleHorsForfait" placeholder="Description du frais">
        </div>
        
        <div class="form-group">
            <label for="txtMontantHorsForfait" class="required">Montant (€)</label>
            <input type="number" id="txtMontantHorsForfait" name="txtMontantHorsForfait" step="0.01" min="0" placeholder="0.00">
        </div>
        
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
    
    <?php if (!empty($fraisHorsForfait)): ?>
        <hr>
        <h3>Frais hors forfait saisis</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th>Montant</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fraisHorsForfait as $frais): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($frais['date']); ?></td>
                        <td><?php echo htmlspecialchars($frais['libelle']); ?></td>
                        <td><?php echo number_format($frais['montant'], 2, ',', ' '); ?> €</td>
                        <td>
                            <form action="index.php?action=supprimerHorsForfait" method="POST" class="inline-form js-confirm-action" data-confirm-message="Supprimer ce frais hors forfait ?">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                <input type="hidden" name="ligne_id" value="<?php echo (int) $frais['id']; ?>">
                                <input type="hidden" name="selected_month" value="<?php echo htmlspecialchars($moisSelectionne); ?>">
                                <button type="submit" class="btn btn-warning">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
