<div class="card">
    <div class="card-header">
        
    </div>
    <!-- Noms collones -->
    <table id="listeInfosVisit">
        <thead>
            <ul>
                <li><h1>LISTE DES VISITEURS</h1></li>
                <li><p>Ajouter</p> <strong>+</strong></li>
            </ul>
            <tr>
                <th>Identifiant</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Adresse</th>
                <th>Ville</th>
                <th>Code postal</th>
                <th>Date d'embauche</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $utilisateur): ?>
                <tr>
                    <td><?php echo htmlspecialchars($utilisateur['id']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['nom']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['adresse']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['ville']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['cp']); ?></td>
                    <td><?php echo htmlspecialchars($utilisateur['date_embauche']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h1>Cette section permet de gérer les utilisateurs de l'application (fonctionnalité à développer).</h1>



    <div class="alert alert-info">
        <strong>À venir :</strong> Création, modification et suppression d'utilisateurs.
    </div>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
