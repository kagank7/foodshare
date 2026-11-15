<?php
// Mes offres (EF-17) : publier un brouillon, modifier, clôturer
require 'includes/fonctions.php';
require 'includes/commerce.php';

$user = exiger_role('GESTIONNAIRE');
$commerce = mon_commerce($user);

if (isset($_POST['cloturer']) || isset($_POST['publier'])) {
    $offres = charger('offres');
    $id = $_POST['cloturer'] ?? $_POST['publier'];
    $i = trouver_index($offres, $id);
    if ($i >= 0 && $offres[$i]['commerceId'] == $commerce['id']) {
        $offres[$i]['statut'] = isset($_POST['cloturer']) ? 'CLOTUREE' : 'PUBLIEE';
        sauver('offres', $offres);
        flash(isset($_POST['cloturer']) ? 'Offre clôturée.' : 'Offre publiée.');
    }
    rediriger('mes-offres.php');
}

list($mes_offres, $resas) = donnees_commerce($commerce);

$titre_page = 'Mes offres';
$menu = menu_commercant();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <?php message_validation($commerce); ?>
        <div class="contenu-titre">
            <h1>Mes offres</h1>
            <a href="publier.php" class="btn btn-orange">+ Publier une offre</a>
        </div>
        <section class="panneau">
            <div class="scroll-x">
                <table class="tableau">
                    <thead>
                    <tr><th>Offre</th><th>Prix</th><th>Stock</th><th>Créneau</th><th>Récurrence</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($mes_offres as $o) { ?>
                        <tr>
                            <td><?= e($o['titre']) ?></td>
                            <td><?= prix($o['prixReduit']) ?> <s class="barre"><?= prix($o['prixInitial']) ?></s></td>
                            <td><?= $o['stock'] ?> / <?= $o['quantiteTotale'] ?></td>
                            <td><?= $o['debut'] ?> – <?= $o['fin'] ?></td>
                            <td><?= count($o['recurrence']) > 0 ? implode(', ', $o['recurrence']) : 'Ponctuelle' ?></td>
                            <td><?= statut($o['statut']) ?></td>
                            <td>
                                <form method="post" class="actions">
                                    <?php if ($o['statut'] == 'BROUILLON') { ?>
                                        <button class="btn btn-petit btn-plein" name="publier" value="<?= $o['id'] ?>">Publier</button>
                                    <?php } ?>
                                    <?php if ($o['statut'] == 'PUBLIEE' || $o['statut'] == 'BROUILLON') { ?>
                                        <a href="publier.php?modifier=<?= $o['id'] ?>" class="btn btn-petit">Modifier</a>
                                        <button class="btn btn-petit btn-rouge" name="cloturer" value="<?= $o['id'] ?>">Clôturer</button>
                                    <?php } ?>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
