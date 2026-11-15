<?php
// Paramètres de la plateforme
require 'includes/fonctions.php';

$user = exiger_role('ADMIN');

if (isset($_POST['reinitialiser'])) {
    reinitialiser_donnees();
    flash('Données de démonstration réinitialisées.');
    rediriger('admin-parametres.php');
}

$titre_page = 'Paramètres';
$menu = menu_admin();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Paramètres</h1></div>
        <form class="form-carte" method="post">
            <h2>Règles de la plateforme</h2>
            <div class="ligne-2">
                <div class="champ"><label>Remise minimale</label><input class="input" value="50 %" disabled></div>
                <div class="champ"><label>Paniers max. par offre</label><input class="input" value="3" disabled></div>
                <div class="champ"><label>Délai d'annulation</label><input class="input" value="1 h avant le créneau" disabled></div>
                <div class="champ"><label>Suspension après non-retraits</label><input class="input" value="3 en 30 jours → 14 jours" disabled></div>
            </div>
            <hr>
            <h2>Démonstration</h2>
            <p class="aide" style="margin-bottom: 10px">Remet toutes les données fictives (offres, réservations, comptes) dans leur état de départ.</p>
            <button class="btn btn-rouge" name="reinitialiser">Réinitialiser les données de démo</button>
        </form>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
