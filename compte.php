<?php
// Maquette 3 — Tableau de bord du consommateur
require 'includes/fonctions.php';
require 'includes/reservation.php';

$user = exiger_role('CONSOMMATEUR');
traiter_annulation($user);

$offres = charger('offres');
$commerces = charger('commerces');

// réservations en cours du client
$en_cours = [];
foreach (charger('reservations') as $r) {
    if ($r['userId'] == $user['id'] && $r['statut'] == 'CONFIRMEE') {
        $en_cours[] = $r;
    }
}

// recommandations : offres d'autres commerces que ceux déjà réservés
$commerces_reserves = [];
foreach ($en_cours as $r) {
    $commerces_reserves[] = trouver($offres, $r['offreId'])['commerceId'];
}
$recommandations = [];
foreach ($offres as $o) {
    if ($o['statut'] == 'PUBLIEE' && !in_array($o['commerceId'], $commerces_reserves) && count($recommandations) < 3) {
        $recommandations[] = $o;
    }
}

$titre_page = 'Tableau de bord';
$menu = menu_consommateur($user);
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Tableau de bord</h1></div>

        <div class="stats">
            <div class="stat"><span>Paniers sauvés</span><b><?= $user['stats']['paniers'] ?></b></div>
            <div class="stat"><span>Économies réalisées</span><b><?= nombre($user['stats']['economies']) ?> €</b></div>
            <div class="stat"><span>CO₂ évité</span><b><?= $user['stats']['co2'] ?> kg</b></div>
            <div class="stat"><span>Réservations en cours</span><b><?= count($en_cours) ?></b></div>
        </div>

        <div class="panneaux">
            <section class="panneau" style="min-height: 258px">
                <h2>Réservation à retirer aujourd'hui</h2>
                <?php if (count($en_cours) == 0) { ?>
                    <p class="vide">Aucune réservation en cours. <a href="offres.php" class="btn-lien">Trouver un panier</a></p>
                <?php } else {
                    afficher_retrait($en_cours[0]);
                    if (count($en_cours) > 1) { ?>
                        <p style="margin-top: 14px; font-size: 14px">+ <?= count($en_cours) - 1 ?> autre(s) — <a href="reservations.php" class="btn-lien">voir tout</a></p>
                    <?php }
                } ?>
            </section>

            <section class="panneau" style="min-height: 258px">
                <h2>Recommandé pour vous</h2>
                <?php foreach ($recommandations as $o) {
                    $c = trouver($commerces, $o['commerceId']); ?>
                    <a href="offre.php?id=<?= $o['id'] ?>" class="mini-offre">
                        <img src="<?= e($o['thumb']) ?>" alt="">
                        <div>
                            <h3><?= e($o['titreCourt']) ?></h3>
                            <div class="meta">📍 <?= e($c['nom']) ?> · <?= distance($c['distance']) ?></div>
                            <div class="prix"><?= prix($o['prixReduit']) ?></div>
                        </div>
                    </a>
                <?php } ?>
            </section>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
