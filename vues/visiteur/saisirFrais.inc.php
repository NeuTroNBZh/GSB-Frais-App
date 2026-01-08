<div class="card">
    <div class="card-header">
        <h2>Saisir mes frais</h2>
    </div>
    
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>
    
    <form action="index.php?action=enregistrerFrais" method="POST" id="frmSaisieFrais">
        <input type="hidden" name="fiche_id" value="<?php echo $ficheId; ?>">
        
        <h3>Frais au forfait</h3>
        <p>Saisissez les quantités pour chaque type de frais forfaitaires du mois en cours.</p>
        
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
                       id="txtForfait<?php echo $type['id']; ?>" 
                       name="txtForfait<?php echo $type['id']; ?>" 
                       value="<?php echo $quantite; ?>" 
                       min="0" 
                       step="1">
            </div>
        <?php endforeach; ?>
        
        <hr>
        
        <h3>Frais hors forfait</h3>
        <p>Ajoutez un nouveau frais hors forfait.</p>
        
        <div class="form-group">
            <label for="txtDateHorsForfait" class="required">Date</label>
            <input type="date" id="txtDateHorsForfait" name="txtDateHorsForfait" max="<?php echo date('Y-m-d'); ?>">
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
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
