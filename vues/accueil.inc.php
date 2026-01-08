<div class="card">
    <div class="card-header">
        <h2>Bienvenue sur <?php echo APP_NAME; ?></h2>
    </div>
    
    <div class="card-body">
        <p>Bienvenue <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>,</p>
        
        <?php if (aLeRole(User::ROLE_VISITOR)): ?>
            <p>En tant que visiteur, vous pouvez :</p>
            <ul>
                <li>Saisir vos frais mensuels (forfait et hors forfait)</li>
                <li>Consulter l'état de vos fiches de frais</li>
                <li>Suivre le traitement de vos demandes de remboursement</li>
            </ul>
            <p class="mt-20">
                <a href="index.php?action=saisirFrais" class="btn btn-primary">Saisir mes frais</a>
                <a href="index.php?action=mesFrais" class="btn btn-secondary">Mes fiches de frais</a>
            </p>
        <?php endif; ?>
        
        <?php if (aLeRole(User::ROLE_ACCOUNTANT)): ?>
            <p>En tant que comptable, vous pouvez :</p>
            <ul>
                <li>Consulter les fiches de frais des visiteurs</li>
                <li>Valider les fiches de frais</li>
                <li>Gérer le cycle de remboursement (En cours → Cloturé → Validé → Remboursé)</li>
            </ul>
            <p class="mt-20">
                <a href="index.php?action=validerFrais" class="btn btn-primary">Valider les frais</a>
            </p>
        <?php endif; ?>
        
        <?php if (aLeRole(User::ROLE_ADMIN)): ?>
            <p>En tant qu'administrateur, vous pouvez :</p>
            <ul>
                <li>Gérer les utilisateurs du système</li>
                <li>Consulter les rapports et statistiques</li>
                <li>Administrer l'ensemble de l'application</li>
            </ul>
            <p class="mt-20">
                <a href="index.php?action=gestionUtilisateurs" class="btn btn-primary">Gestion utilisateurs</a>
                <a href="index.php?action=rapports" class="btn btn-secondary">Rapports</a>
            </p>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
