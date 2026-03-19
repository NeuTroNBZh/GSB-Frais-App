<div class="card">
    <div class="card-header">
        <h2>Gestion des utilisateurs</h2>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($erreur !== ''): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($erreur); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-20">
        <div class="card-header">
            <h2><?php echo $utilisateurEnEdition ? 'Modifier un utilisateur' : 'Creer un utilisateur'; ?></h2>
        </div>

        <form action="index.php?action=gestionUtilisateurs<?php echo $utilisateurEnEdition ? '&edit=' . (int) $utilisateurEnEdition['id'] : ''; ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="admin_action" value="<?php echo $utilisateurEnEdition ? 'update' : 'create'; ?>">

            <?php if ($utilisateurEnEdition): ?>
                <input type="hidden" name="user_id" value="<?php echo (int) $utilisateurEnEdition['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="txtLogin" class="required">Login</label>
                <input type="text" id="txtLogin" name="txtLogin" value="<?php echo htmlspecialchars($utilisateurEnEdition ? $utilisateurEnEdition['login'] : ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="txtNom" class="required">Nom</label>
                <input type="text" id="txtNom" name="txtNom" value="<?php echo htmlspecialchars($utilisateurEnEdition ? $utilisateurEnEdition['nom'] : ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="txtPrenom" class="required">Prenom</label>
                <input type="text" id="txtPrenom" name="txtPrenom" value="<?php echo htmlspecialchars($utilisateurEnEdition ? $utilisateurEnEdition['prenom'] : ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="lstRole" class="required">Role</label>
                <select id="lstRole" name="lstRole" required>
                    <?php foreach ($rolesDisponibles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role); ?>" <?php echo $utilisateurEnEdition && $utilisateurEnEdition['role'] === $role ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="txtPassword" class="<?php echo $utilisateurEnEdition ? '' : 'required'; ?>">
                    <?php echo $utilisateurEnEdition ? 'Nouveau mot de passe' : 'Mot de passe'; ?>
                </label>
                <input type="password" id="txtPassword" name="txtPassword" <?php echo $utilisateurEnEdition ? '' : 'required'; ?> >
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $utilisateurEnEdition ? 'Enregistrer les modifications' : 'Creer l utilisateur'; ?>
            </button>

            <?php if ($utilisateurEnEdition): ?>
                <a href="index.php?action=gestionUtilisateurs" class="btn btn-secondary">Annuler</a>
            <?php endif; ?>
        </form>
    </div>

    <p>Liste des comptes existants dans l'application.</p>

    <?php if (empty($utilisateurs)): ?>
        <p>Aucun utilisateur n'est enregistré.</p>
    <?php else: ?>
        <table id="listeInfosVisit">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Rôle</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $utilisateur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($utilisateur['id']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['login']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['nom']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['role']); ?></td>
                        <td><?php echo htmlspecialchars($utilisateur['date_creation']); ?></td>
                        <td class="actions-cell">
                            <a href="index.php?action=gestionUtilisateurs&edit=<?php echo (int) $utilisateur['id']; ?>" class="btn btn-secondary">Modifier</a>

                            <?php if ((int) $utilisateur['id'] !== (int) $user['id']): ?>
                                <form action="index.php?action=gestionUtilisateurs" method="POST" class="inline-form js-confirm-action" data-confirm-message="Supprimer cet utilisateur ?">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <input type="hidden" name="admin_action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo (int) $utilisateur['id']; ?>">
                                    <button type="submit" class="btn btn-warning">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a class="btn btn-secondary" href="index.php?action=gestionUtilisateurs&page=<?php echo $page - 1; ?>">Précédent</a>
                <?php endif; ?>

                <span class="pagination-info">Page <?php echo $page; ?> / <?php echo $totalPages; ?></span>

                <?php if ($page < $totalPages): ?>
                    <a class="btn btn-secondary" href="index.php?action=gestionUtilisateurs&page=<?php echo $page + 1; ?>">Suivant</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require_once 'vues/menu_close.inc.php'; ?>
