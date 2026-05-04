<div class="container">
    <aside class="sidebar">
        <nav>
            <ul>
                <?php if (aLeRole(User::ROLE_VISITOR)): ?>
                    <li><a href="index.php?action=accueil" class="<?php echo $action === 'accueil' ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="index.php?action=saisirFrais" class="<?php echo $action === 'saisirFrais' ? 'active' : ''; ?>">Saisir mes frais</a></li>
                    <li><a href="index.php?action=mesFrais" class="<?php echo $action === 'mesFrais' ? 'active' : ''; ?>">Mes fiches de frais</a></li>
                <?php endif; ?>
                
                <?php if (aLeRole(User::ROLE_ACCOUNTANT)): ?>
                    <li><a href="index.php?action=accueil" class="<?php echo $action === 'accueil' ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="index.php?action=validerFrais" class="<?php echo $action === 'validerFrais' ? 'active' : ''; ?>">Valider les frais</a></li>
                    <li><a href="index.php?action=parcAutomobileListe" class="<?php echo $action === 'parcAutomobileListe' ? 'active' : ''; ?>">Parc automobile</a></li>
                <?php endif; ?>
                
                <?php if (aLeRole(User::ROLE_ADMIN)): ?>
                    <li><a href="index.php?action=accueil" class="<?php echo $action === 'accueil' ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="index.php?action=gestionUtilisateurs" class="<?php echo $action === 'gestionUtilisateurs' ? 'active' : ''; ?>">Gestion utilisateurs</a></li>
                    <li><a href="index.php?action=rapports" class="<?php echo $action === 'rapports' ? 'active' : ''; ?>">Rapports</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
    <main class="main-content">
        <div class="content-wrapper">
