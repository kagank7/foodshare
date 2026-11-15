<?php
// Statistiques du commerce (EF-20) — chiffres fictifs pour la démo
require 'includes/fonctions.php';

$user = exiger_role('GESTIONNAIRE');
$commerce = mon_commerce($user);

$semaine = ['Lun' => 0, 'Mar' => 6, 'Mer' => 4, 'Jeu' => 7, 'Ven' => 9, 'Sam' => 11, 'Dim' => 5];
$max = max($semaine);

$titre_page = 'Statistiques';
$menu = menu_commercant();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Statistiques</h1></div>
        <div class="stats">
            <div class="stat"><span>Paniers vendus (mois)</span><b>142</b></div>
            <div class="stat"><span>Revenus récupérés</span><b>497 €</b></div>
            <div class="stat"><span>Kilos sauvés</span><b>185 kg</b></div>
            <div class="stat"><span>Note moyenne</span><b><?= $commerce ? note($commerce['note']) : '–' ?> ★</b></div>
        </div>
        <section class="panneau">
            <h2>Paniers vendus cette semaine</h2>
            <!-- graphique en barres fait en HTML/CSS : la hauteur de chaque barre est calculée en PHP -->
            <div class="graph">
                <?php foreach ($semaine as $jour => $valeur) { ?>
                    <div class="col">
                        <span><?= $valeur ?></span>
                        <div style="height: <?= round($valeur / $max * 140) ?>px"></div>
                    </div>
                <?php } ?>
            </div>
            <div class="graph-labels">
                <?php foreach ($semaine as $jour => $valeur) { ?><span><?= $jour ?></span><?php } ?>
            </div>
        </section>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
