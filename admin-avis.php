<?php
// Avis et signalements (EF-23)
require 'includes/fonctions.php';
require 'includes/admin.php';

$user = exiger_role('ADMIN');
traiter_actions_admin();
$signalements = charger('signalements');

$titre_page = 'Avis et signalements';
$menu = menu_admin();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Avis et signalements</h1></div>
        <section class="panneau" style="max-width: 760px">
            <h2>Signalements ouverts (<?= count($signalements) ?>)</h2>
            <?php liste_signalements($signalements); ?>
        </section>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
