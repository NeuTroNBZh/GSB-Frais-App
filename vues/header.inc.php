<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php if (estConnecte()): ?>
<header>
    <h1><?php echo APP_NAME; ?></h1>
    <div class="user-info">
        Connecté en tant que: <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
        (<?php echo htmlspecialchars($user['role']); ?>)
        | <a href="index.php?action=deconnexion" style="color: white;">Déconnexion</a>
    </div>
</header>
<?php endif; ?>
