<div class="card">
    <div class="card-header">
        <h2>Mes fiches de frais</h2>
    </div>
    
    <?php if (empty($ficheFrais)): ?>
        <p>Vous n'avez pas encore de fiche de frais.</p>
        <p><a href="index.php?action=saisirFrais" class="btn btn-primary">Saisir mes frais</a></p>
    <?php else: ?>
        <!-- Header -->
        <ul>
            <li><h1>Fiche de frais de : <?php echo $_SESSION['nom']; ?></h1></li>
            <li><p>Ajouter</p> <strong>+</strong></li>
        </ul>
        <!-- Noms collones -->
        <table id="listeInfosVisit">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date</th>
                    <th>Montant total</th>
                    <th>Statut</th>
                    <th>Supprimer</th>
                    <th>Modifier</th>
                    <th>Voir</th>
                </tr>
            </thead>
            <!-- Contenu ligne -->
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
