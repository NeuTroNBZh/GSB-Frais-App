<div class="login-container">
    <div class="login-box">
        <h1><?php echo APP_NAME; ?></h1>
        
        <?php if (!empty($erreur)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($erreur); ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php?action=connexion" method="POST" id="frmConnexion">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="form-group">
                <label for="txtLogin" class="required">Identifiant</label>
                <input type="text" id="txtLogin" name="txtLogin" required>
            </div>
            
            <div class="form-group">
                <label for="txtPassword" class="required">Mot de passe</label>
                <input type="password" id="txtPassword" name="txtPassword" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>
</div>
