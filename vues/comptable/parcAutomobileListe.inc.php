<div class="parc-auto-card">
    <div class="parc-auto-header">
        <h2>Gestion du parc automobile</h2>
        <button type="button" class="parc-action parc-plus" id="btnOuvrirModal" title="Ajouter un véhicule">+</button>
    </div>

    <?php if (isset($_GET['succes']) && $_GET['succes'] === 'vehicule_ajoute'): ?>
        <div class="alert alert-success">Véhicule ajouté avec succès.</div>
    <?php elseif (isset($_GET['succes']) && $_GET['succes'] === 'vehicule_supprime'): ?>
        <div class="alert alert-success">Véhicule supprimé avec succès.</div>
    <?php endif; ?>
    <?php if (isset($_GET['erreur'])): ?>
        <div class="alert alert-danger">
            <?php
            $erreurs = [
                'csrf'                 => 'Erreur de sécurité, veuillez réessayer.',
                'champs_manquants'     => 'Tous les champs sont obligatoires.',
                'date_invalide'        => 'La date saisie est invalide.',
                'ajout_impossible'     => 'Impossible d\'ajouter le véhicule (immatriculation déjà existante ou visiteur déjà attribué).',
                'suppression_impossible' => 'Impossible de supprimer ce véhicule.',
            ];
            $cle = htmlspecialchars($_GET['erreur']);
            echo $erreurs[$cle] ?? 'Une erreur est survenue.';
            ?>
        </div>
    <?php endif; ?>

    <table class="parc-auto-table">
        <thead>
            <tr>
                <th>Immatriculation</th>
                <th>Nom Prénom du visiteur</th>
                <th>Date</th>
                <th class="parc-col-action"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $lignesAffichees = max(3, count($vehiculesParc));
            for ($i = 0; $i < $lignesAffichees; $i++):
                $ligne = isset($vehiculesParc[$i]) ? $vehiculesParc[$i] : null;
            ?>
                <tr>
                    <td><?php echo $ligne ? htmlspecialchars($ligne['immatriculation']) : ''; ?></td>
                    <td>
                        <?php
                        if ($ligne) {
                            echo htmlspecialchars($ligne['nom'] . ' ' . $ligne['prenom']);
                        }
                        ?>
                    </td>
                    <td><?php echo $ligne ? htmlspecialchars($ligne['date_attribution']) : ''; ?></td>
                    <td class="parc-col-action">
                        <?php if ($ligne): ?>
                            <form method="post" action="index.php?action=supprimerVehicule"
                                  onsubmit="return confirm('Supprimer le véhicule <?php echo addslashes(htmlspecialchars($ligne['immatriculation'])); ?> ?');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(obtenirJetonCsrf()); ?>">
                                <input type="hidden" name="immatriculation" value="<?php echo htmlspecialchars($ligne['immatriculation']); ?>">
                                <button type="submit" class="parc-action parc-minus" title="Supprimer ce véhicule">-</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

<!-- Modal ajout véhicule -->
<div id="modalAjoutVehicule" class="parc-modal-overlay" style="display:none;">
    <div class="parc-modal">
        <div class="parc-modal-header">
            <h3>Ajouter un véhicule</h3>
            <button type="button" class="parc-modal-close" id="btnFermerModal">&times;</button>
        </div>
        <form method="post" action="index.php?action=ajouterVehicule" class="parc-modal-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(obtenirJetonCsrf()); ?>">

            <div class="parc-form-group">
                <label for="immatriculation">Immatriculation</label>
                <input type="text" id="immatriculation" name="immatriculation"
                       placeholder="Ex : AB-123-CD" maxlength="20" required
                       class="parc-form-input">
            </div>

            <div class="parc-form-group">
                <label for="visiteur_id">Visiteur</label>
                <select id="visiteur_id" name="visiteur_id" required class="parc-form-input">
                    <option value="">-- Sélectionner un visiteur --</option>
                    <?php foreach ($visiteursSansVehicule as $v): ?>
                        <option value="<?php echo (int) $v['id']; ?>">
                            <?php echo htmlspecialchars($v['nom'] . ' ' . $v['prenom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="parc-form-group">
                <label for="date_attribution">Date d'attribution</label>
                <input type="date" id="date_attribution" name="date_attribution"
                       value="<?php echo date('Y-m-d'); ?>" required
                       class="parc-form-input">
            </div>

            <div class="parc-modal-footer">
                <button type="button" class="btn-secondary" id="btnAnnulerModal">Annuler</button>
                <button type="submit" class="btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var overlay = document.getElementById('modalAjoutVehicule');
    document.getElementById('btnOuvrirModal').addEventListener('click', function () {
        overlay.style.display = 'flex';
    });
    document.getElementById('btnFermerModal').addEventListener('click', function () {
        overlay.style.display = 'none';
    });
    document.getElementById('btnAnnulerModal').addEventListener('click', function () {
        overlay.style.display = 'none';
    });
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.style.display = 'none';
    });
})();
</script>

<?php require_once 'vues/menu_close.inc.php'; ?>
