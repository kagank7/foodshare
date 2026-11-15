<?php
// Notifications de l'utilisateur (EF-12)
// la page est partagée entre consommateur et commerçant
require 'includes/fonctions.php';

$user = utilisateur();
if (!$user) {
    rediriger('connexion.php?onglet=connexion');
}

if (isset($_POST['tout_lu'])) {
    $notifs = charger('notifications');
    foreach ($notifs as $i => $n) {
        if ($n['userId'] == $user['id']) {
            $notifs[$i]['lu'] = true;
        }
    }
    sauver('notifications', $notifs);
    rediriger('notifications.php');
}

$miennes = [];
foreach (charger('notifications') as $n) {
    if ($n['userId'] == $user['id']) {
        $miennes[] = $n;
    }
}

$titre_page = 'Notifications';
$menu = $user['role'] == 'GESTIONNAIRE' ? menu_commercant() : menu_consommateur($user);
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre">
            <h1>Notifications</h1>
            <form method="post"><button class="btn btn-petit" name="tout_lu">Tout marquer comme lu</button></form>
        </div>
        <div class="liste-cartes">
            <?php if (count($miennes) == 0) { ?>
                <p class="vide">Aucune notification.</p>
            <?php } ?>
            <?php foreach ($miennes as $n) { ?>
                <div class="notif <?= $n['lu'] ? '' : 'non-lu' ?>">
                    <div style="flex: 1">
                        <h3><?= e($n['titre']) ?> <?php if (!$n['lu']) { ?><span class="pastille" style="margin-left: 6px">Nouveau</span><?php } ?></h3>
                        <p><?= e($n['texte']) ?></p>
                    </div>
                    <small><?= e($n['date']) ?></small>
                </div>
            <?php } ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
