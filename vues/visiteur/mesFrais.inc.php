<div class="card"  id="listeInfosVisit">
    <div class="card-header">
        <table>
            <tbody style="text-align: center;">
                <tr>
                    <td><h2>Mes fiches de frais</h2></td>
                    <td><button href="page2.html"style="float: right;" class="btn btn-primary" onclick="window.location.href='index.php?action=saisirFrais'">Saisir mes frais</button></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <?php if (empty($ficheFrais)): ?>
        <p>Vous n'avez pas encore de fiche de frais.</p>
        <p><a href="index.php?action=saisirFrais" class="btn btn-primary">Saisir mes frais</a></p>
    <?php else: ?>
        <p>Fiches de frais de <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>.</p>

        <table id="listeInfosVisit">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mois</th>
                    <th>Montant total</th>
                    <th>Nb justificatifs</th>
                    <th>Statut</th>
                    <th>Date modification</th>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ficheFrais as $fiche): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fiche['id']); ?></td>
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
                            <?php if ($fiche['statut'] === 'En cours'): ?>
                                <a href="index.php?action=saisirFrais&fiche_id=<?php echo $fiche['id']; ?>" class="btn btn-primary">Modifier</a>
                            <?php else: ?>
                                <span class="text-muted">Non modifiable</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="index.php?action=supprimerHorsForfait" method="POST" class="inline-form js-confirm-action" data-confirm-message="Supprimer ce frais hors forfait ?">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                <input type="hidden" name="ligne_id" value="<?php echo (int) $fiche['id']; ?>">
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
