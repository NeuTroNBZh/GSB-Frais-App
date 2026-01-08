<div class="card">
    <div class="card-header">
        <h2>Fiches de frais de <?php echo htmlspecialchars($visiteur['nom'] . ' ' . $visiteur['prenom']); ?></h2>
    </div>
    
    <p><a href="index.php?action=validerFrais" class="btn btn-secondary">← Retour</a></p>
    
    <?php if (empty($ficheFrais)): ?>
        <p>Ce visiteur n'a pas encore de fiche de frais.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Montant validé</th>
                    <th>Nb justificatifs</th>
                    <th>Statut</th>
                    <th>Date modification</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ficheFrais as $fiche): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fiche['mois']); ?></td>
                        <td><?php echo number_format($fiche['montant_valide'], 2, ',', ' '); ?> €</td>
                        <td><?php echo htmlspecialchars($fiche['nb_justificatifs']); ?></td>
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
                        <td><?php echo htmlspecialchars($fiche['date_modif']); ?></td>
                        <td>
                            <a href="index.php?action=detailFrais&fiche=<?php echo $fiche['id']; ?>" class="btn btn-primary">Détails</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
