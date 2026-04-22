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
                            <?php if ($fiche['statut'] === Frais::STATUS_EN_COURS): ?>
                                <a href="index.php?action=saisirFrais&fiche_id=<?php echo (int) $fiche['id']; ?>" class="btn btn-primary">Modifier</a>
                            <?php else: ?>
                                <button class="btn btn-primary" disabled title="Seules les fiches en cours peuvent être modifiées" style="opacity:0.4;cursor:not-allowed;">Modifier</button>
                            <?php endif; ?>
                            </td>
                            <td>
                            <?php if ($fiche['statut'] === Frais::STATUS_EN_COURS): ?>
                                <form action="index.php?action=supprimerFiche" method="POST" class="inline-form" onsubmit="return confirm('Supprimer définitivement cette fiche de frais ?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <input type="hidden" name="fiche_id" value="<?php echo (int) $fiche['id']; ?>">
                                    <button type="submit" class="btn btn-warning">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-warning" disabled title="Seules les fiches en cours peuvent être supprimées" style="opacity:0.4;cursor:not-allowed;">Supprimer</button>
                            <?php endif; ?>
                        </td>                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
