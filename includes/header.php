<?php
// En-tête commun à toutes les pages
// avant d'inclure ce fichier, la page définit $titre_page
$user = utilisateur();
$nom_affiche = '';
if ($user) {
    $nom_affiche = $user['prenom'];
    if ($user['role'] == 'GESTIONNAIRE' && mon_commerce($user)) {
        $nom_affiche = mon_commerce($user)['nom'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titre_page ?? 'FoodShare') ?> — FoodShare</title>
    <meta name="description" content="FoodShare : réservez les invendus des commerçants près de chez vous à prix réduit.">
    <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="header">
    <a href="index.php" class="logo"><span class="logo-carre"></span>FoodShare</a>

    <nav class="nav" id="nav">
        <a href="index.php">Accueil</a>
        <a href="offres.php">Offres près de moi</a>
        <a href="comment-ca-marche.php">Comment ça marche</a>
        <a href="commercants.php">Commerçants</a>
        <?php if ($user) { ?>
            <a href="<?= page_espace($user['role']) ?>" class="lien-mobile">Mon espace</a>
            <a href="deconnexion.php" class="lien-mobile">Se déconnecter</a>
        <?php } else { ?>
            <a href="connexion.php?onglet=connexion" class="lien-mobile">Se connecter</a>
            <a href="connexion.php" class="lien-mobile">S'inscrire</a>
        <?php } ?>
    </nav>

    <div class="header-droite">
        <?php if ($user) { ?>
            <span class="bonjour">Bonjour, <?= e($nom_affiche) ?></span>
            <button class="avatar" id="bouton-avatar" aria-label="Menu du compte"><?= e(mb_strtoupper(mb_substr($user['prenom'], 0, 1))) ?></button>
            <div class="menu-user" id="menu-user">
                <a href="<?= page_espace($user['role']) ?>">Mon espace</a>
                <?php if ($user['role'] == 'CONSOMMATEUR') { ?>
                    <a href="reservations.php">Mes réservations</a>
                <?php } ?>
                <a href="deconnexion.php">Se déconnecter</a>
            </div>
        <?php } else { ?>
            <a href="connexion.php?onglet=connexion" class="btn">Se connecter</a>
            <a href="connexion.php" class="btn btn-plein">S'inscrire</a>
        <?php } ?>
    </div>

    <button class="burger" id="burger" aria-label="Ouvrir le menu">☰</button>
</header>
