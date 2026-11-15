<?php
// Statistiques globales (EF-24) — chiffres fictifs pour la démo
require 'includes/fonctions.php';

$user = exiger_role('ADMIN');
$mois = ['Juin' => 1240, 'Juil.' => 1480, 'Août' => 1310, 'Sept.' => 1890, 'Oct.' => 2150, 'Nov.' => 2310];
$max = max($mois);

$titre_page = 'Statistiques globales';
$menu = menu_admin();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Statistiques globales</h1></div>
        <div class="stats">
            <div class="stat"><span>Paniers sauvés (total)</span><b>12 400</b></div>
            <div class="stat"><span>Nourriture sauvée</span><b>15,5 t</b></div>
            <div class="stat"><span>CO₂ évité</span><b>31 t</b></div>
            <div class="stat"><span>Taux de retrait</span><b>94 %</b></div>
        </div>
        <section class="panneau">
            <h2>Paniers sauvés par mois</h2>
            <div class="graph">
                <?php foreach ($mois as $m => $v) { ?>
                    <div class="col"><span><?= nombre($v) ?></span><div style="height: <?= round($v / $max * 140) ?>px"></div></div>
                <?php } ?>
            </div>
            <div class="graph-labels">
                <?php foreach ($mois as $m => $v) { ?><span><?= $m ?></span><?php } ?>
            </div>
        </section>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
