<?php
// Maquette 6 — Tableau de bord du gestionnaire (commerçant)
require 'includes/fonctions.php';
require 'includes/commerce.php';

$user = exiger_role('GESTIONNAIRE');
$commerce = mon_commerce($user);
traiter_retrait($commerce);

// clôturer une offre
if (isset($_POST['cloturer'])) {
    $offres = charger('offres');
    $i = trouver_index($offres, $_POST['cloturer']);
    if ($i >= 0 && $offres[$i]['commerceId'] == $commerce['id']) {
        $offres[$i]['statut'] = 'CLOTUREE';
        sauver('offres', $offres);
        flash('Offre clôturée.');
    }
    rediriger('commerce.php');
}

list($mes_offres, $resas_du_jour) = donnees_commerce($commerce);
$actives = [];
foreach ($mes_offres as $o) {
    if ($o['statut'] == 'PUBLIEE') $actives[] = $o;
}

// offre sélectionnée pour les boutons Modifier / Clôturer (la 1re par défaut)
$selection = $_GET['offre'] ?? ($actives[0]['id'] ?? '');

// les chiffres du mois sont fictifs pour la démo
$est_demo = $commerce && $commerce['id'] == 'c1';

$titre_page = 'Tableau de bord';
$menu = menu_commercant();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <?php message_validation($commerce); ?>
        <div class="contenu-titre">
            <h1>Tableau de bord · <?= e($commerce['nom'] ?? '') ?></h1>
            <a href="publier.php" class="btn btn-orange" style="height: 38px">+ Publier une offre</a>
        </div>

        <div class="stats">
            <div class="stat"><span>Offres actives</span><b><?= count($actives) ?></b></div>
            <div class="stat"><span>Paniers vendus (mois)</span><b><?= $est_demo ? 142 : 0 ?></b></div>
            <div class="stat"><span>Revenus récupérés</span><b><?= $est_demo ? 497 : 0 ?> €</b></div>
            <div class="stat"><span>Note moyenne</span><b><?= $commerce && $commerce['nbAvis'] > 0 ? note($commerce['note']) . ' ★' : '–' ?></b></div>
        </div>

        <div class="panneaux">
            <section class="panneau" style="min-height: 263px">
                <h2>Réservations du jour</h2>
                <?php tableau_reservations($resas_du_jour); ?>
            </section>

            <section class="panneau" style="min-height: 263px">
                <h2>Mes offres actives</h2>
                <?php if (count($actives) == 0) { ?><p class="vide">Aucune offre active.</p><?php } ?>
                <?php foreach ($actives as $o) {
                    $vendu = ($o['quantiteTotale'] - $o['stock']) / $o['quantiteTotale'] * 100; ?>
                    <a href="commerce.php?offre=<?= $o['id'] ?>" class="stock-ligne" style="display: block; opacity: <?= $selection == $o['id'] || !isset($_GET['offre']) ? 1 : 0.6 ?>">
                        <div class="stock-haut"><b><?= e($o['titreCourt']) ?></b><span><?= $o['stock'] ?> / <?= $o['quantiteTotale'] ?> restants</span></div>
                        <div class="barre-stock"><div style="width: <?= round($vendu) ?>%"></div></div>
                    </a>
                <?php } ?>
                <?php if (count($actives) > 0) { ?>
                    <div class="resa-actions" style="margin-top: 20px">
                        <a href="publier.php?modifier=<?= $selection ?>" class="btn btn-gris">Modifier</a>
                        <button class="btn btn-rouge" style="font-weight: 400" data-modale="modale-cloture">Clôturer</button>
                    </div>
                    <div class="fond-modale" id="modale-cloture">
                        <div class="modale">
                            <h2>Clôturer l'offre ?</h2>
                            <p class="recap">« <?= e(trouver($actives, $selection)['titre'] ?? '') ?> » ne sera plus réservable. Les réservations déjà faites restent valables.</p>
                            <form method="post" class="modale-actions">
                                <button type="button" class="btn fermer-modale">Annuler</button>
                                <button class="btn btn-rouge" name="cloturer" value="<?= $selection ?>">Clôturer</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </section>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
