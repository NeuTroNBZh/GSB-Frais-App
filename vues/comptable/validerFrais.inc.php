<div class="card">
    <div class="card-header">
        <h2>Valider les frais</h2>
    </div>
    
    <p>Sélectionnez un visiteur pour consulter et valider ses fiches de frais.</p>
    
    <form action="index.php" method="GET" id="frmSelectionVisiteur">
        <input type="hidden" name="action" value="listeFraisVisiteur">
        
        <div class="form-group">
            <label for="lstVisiteur" class="required">Visiteur</label>
            <select id="lstVisiteur" name="visiteur" required>
                <option value="">-- Sélectionnez un visiteur --</option>
                <?php foreach ($visiteurs as $visiteur): ?>
                    <option value="<?php echo $visiteur['id']; ?>">
                        <?php echo htmlspecialchars($visiteur['nom'] . ' ' . $visiteur['prenom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Consulter</button>
    </form>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
