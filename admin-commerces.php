<?php
// Commerces à valider (EF-21) + liste des commerces partenaires
require 'includes/fonctions.php';
require 'includes/admin.php';

$user = exiger_role('ADMIN');
traiter_actions_admin();

$en_attente = commerces_en_attente();
$traites = [];
foreach (charger('commerces') as $c) {
    if ($c['statut'] != 'EN_ATTENTE') $traites[] = $c;
}

$titre_page = 'Commerces à valider';
$menu = menu_admin();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Commerces à valider</h1></div>
        <section class="panneau" style="margin-bottom: 16px">
            <h2>En attente (<?= count($en_attente) ?>)</h2>
            <?php tableau_commerces_attente($en_attente); ?>
            <?php if (count($en_attente) > 0) { ?>
                <p class="aide" style="margin-top: 10px">Détail d'une demande :
                    <?php foreach ($en_attente as $c) { ?>
                        <button class="btn-lien" style="margin-right: 10px" data-modale="detail-<?= $c['id'] ?>"><?= e($c['nom']) ?></button>
                    <?php } ?>
                </p>
            <?php } ?>
        </section>

        <section class="panneau">
            <h2>Commerces partenaires</h2>
            <div class="scroll-x">
                <table class="tableau">
                    <thead><tr><th>Commerce</th><th>Type</th><th>Adresse</th><th>SIRET</th><th>Statut</th></tr></thead>
                    <tbody>
                    <?php foreach ($traites as $c) { ?>
                        <tr>
                            <td><?= e($c['nom']) ?></td>
                            <td><?= e($c['type']) ?></td>
                            <td><?= e($c['adresse']) ?></td>
                            <td><?= e($c['siret']) ?></td>
                            <td><?= statut($c['statut']) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php foreach ($en_attente as $c) { ?>
            <div class="fond-modale" id="detail-<?= $c['id'] ?>">
                <div class="modale">
                    <h2><?= e($c['nom']) ?></h2>
                    <div class="recap">
                        <div>Type : <b><?= e($c['type']) ?></b></div>
                        <div>Adresse : <b><?= e($c['adresse']) ?></b></div>
                        <div>SIRET : <b><?= e($c['siret']) ?></b></div>
                        <div>Demande reçue le : <b><?= e($c['dateDemande']) ?></b></div>
                        <div>Justificatif : <b>kbis.pdf</b> (fictif)</div>
                    </div>
                    <div class="modale-actions"><button class="btn fermer-modale">Fermer</button></div>
                </div>
            </div>
        <?php } ?>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
