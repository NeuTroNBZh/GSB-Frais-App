<div class="card">
    <div class="card-header">
        <h2>Rapports</h2>
    </div>

    <?php
    $urlExportCsv = 'index.php?' . http_build_query([
        'action' => 'rapports',
        'export' => 'csv',
        'mois_debut' => $filtresRapports['mois_debut'],
        'mois_fin' => $filtresRapports['mois_fin'],
        'statut' => $filtresRapports['statut'],
        'visiteur_id' => $filtresRapports['visiteur_id'],
    ]);
    ?>

    <p>Tableau de bord de synthèse des fiches de frais.</p>

    <form action="index.php" method="GET">
        <input type="hidden" name="action" value="rapports">

        <div class="report-grid">
            <div class="form-group">
                <label for="txtMoisDebut">Mois début</label>
                <input type="month" id="txtMoisDebut" name="mois_debut" value="<?php echo htmlspecialchars($filtresRapports['mois_debut']); ?>">
            </div>

            <div class="form-group">
                <label for="txtMoisFin">Mois fin</label>
                <input type="month" id="txtMoisFin" name="mois_fin" value="<?php echo htmlspecialchars($filtresRapports['mois_fin']); ?>">
            </div>

            <div class="form-group">
                <label for="lstStatutRapport">Statut</label>
                <select id="lstStatutRapport" name="statut">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statutsRapport as $statut): ?>
                        <option value="<?php echo htmlspecialchars($statut); ?>" <?php echo $filtresRapports['statut'] === $statut ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($statut); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="lstVisiteurRapport">Visiteur</label>
                <select id="lstVisiteurRapport" name="visiteur_id">
                    <option value="0">Tous les visiteurs</option>
                    <?php foreach ($visiteursRapport as $visiteur): ?>
                        <option value="<?php echo (int) $visiteur['id']; ?>" <?php echo (int) $filtresRapports['visiteur_id'] === (int) $visiteur['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($visiteur['nom'] . ' ' . $visiteur['prenom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <a href="index.php?action=rapports" class="btn btn-secondary">Réinitialiser</a>
            <a href="<?php echo htmlspecialchars($urlExportCsv); ?>" class="btn btn-secondary">Exporter CSV</a>
        </div>
    </form>

    <div class="report-grid">
        <div class="report-card">
            <h3>Fiches totales</h3>
            <p class="report-value"><?php echo (int) $rapportSynthese['nb_fiches']; ?></p>
        </div>
        <div class="report-card">
            <h3>Montant total</h3>
            <p class="report-value"><?php echo number_format((float) $rapportSynthese['montant_total'], 2, ',', ' '); ?> €</p>
        </div>
        <div class="report-card">
            <h3>Montant moyen</h3>
            <p class="report-value"><?php echo number_format((float) $rapportSynthese['montant_moyen'], 2, ',', ' '); ?> €</p>
        </div>
        <div class="report-card">
            <h3>Justificatifs</h3>
            <p class="report-value"><?php echo (int) $rapportSynthese['nb_justificatifs']; ?></p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Répartition par statut</h2>
    </div>

    <?php if (empty($rapportParStatut)): ?>
        <p>Aucune donnée disponible.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Nombre de fiches</th>
                    <th>Montant total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rapportParStatut as $ligne): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ligne['statut']); ?></td>
                        <td><?php echo (int) $ligne['nb_fiches']; ?></td>
                        <td><?php echo number_format((float) $ligne['montant_total'], 2, ',', ' '); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h2>Évolution mensuelle (12 derniers mois disponibles)</h2>
    </div>

    <?php if (empty($rapportMensuel)): ?>
        <p>Aucune donnée disponible.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Nombre de fiches</th>
                    <th>Montant total</th>
                    <th>Montant moyen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rapportMensuel as $ligne): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ligne['mois']); ?></td>
                        <td><?php echo (int) $ligne['nb_fiches']; ?></td>
                        <td><?php echo number_format((float) $ligne['montant_total'], 2, ',', ' '); ?> €</td>
                        <td><?php echo number_format((float) $ligne['montant_moyen'], 2, ',', ' '); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h2>Top visiteurs (montant validé)</h2>
    </div>

    <?php if (empty($rapportTopVisiteurs)): ?>
        <p>Aucune donnée disponible.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Visiteur</th>
                    <th>Nombre de fiches</th>
                    <th>Montant total validé</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rapportTopVisiteurs as $ligne): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ligne['prenom'] . ' ' . $ligne['nom']); ?></td>
                        <td><?php echo (int) $ligne['nb_fiches']; ?></td>
                        <td><?php echo number_format((float) $ligne['montant_total'], 2, ',', ' '); ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'vues/menu_close.inc.php'; ?>
