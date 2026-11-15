<?php
// Maquette 7 — Tableau de bord de l'administrateur
require 'includes/fonctions.php';
require 'includes/admin.php';

$user = exiger_role('ADMIN');
traiter_actions_admin();

$signalements = charger('signalements');
$valides = 0;
foreach (charger('commerces') as $c) {
    if ($c['statut'] == 'VALIDE') $valides++;
}
// 175 commerces "hors démo" + ceux de notre jeu de données
$commerces_actifs = 175 + $valides;

$titre_page = 'Administration';
$menu = menu_admin();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Administration</h1></div>
        <div class="stats">
            <div class="stat"><span>Utilisateurs inscrits</span><b>8 920</b></div>
            <div class="stat"><span>Commerces actifs</span><b><?= $commerces_actifs ?></b></div>
            <div class="stat"><span>Paniers sauvés (mois)</span><b>2 310</b></div>
            <div class="stat"><span>Signalements ouverts</span><b><?= count($signalements) ?></b></div>
        </div>
        <div class="panneaux">
            <section class="panneau" style="min-height: 267px">
                <h2>Commerces en attente de validation</h2>
                <?php tableau_commerces_attente(commerces_en_attente()); ?>
            </section>
            <section class="panneau" style="min-height: 267px">
                <h2>Avis signalés</h2>
                <?php liste_signalements(array_slice($signalements, 0, 2)); ?>
            </section>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
