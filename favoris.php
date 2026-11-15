<?php
// Mes favoris + alertes sur les nouvelles offres (EF-14)
require 'includes/fonctions.php';

$user = exiger_role('CONSOMMATEUR');

if (isset($_POST['retirer']) || isset($_POST['alerte'])) {
    $utilisateurs = charger('utilisateurs');
    $i = trouver_index($utilisateurs, $user['id']);
    if (isset($_POST['retirer'])) {
        $cid = $_POST['retirer'];
        $utilisateurs[$i]['favoris'] = array_values(array_diff($utilisateurs[$i]['favoris'], [$cid]));
        $utilisateurs[$i]['alertes'] = array_values(array_diff($utilisateurs[$i]['alertes'], [$cid]));
        flash('Favori retiré.');
    } else {
        $cid = $_POST['alerte'];
        if (in_array($cid, $utilisateurs[$i]['alertes'])) {
            $utilisateurs[$i]['alertes'] = array_values(array_diff($utilisateurs[$i]['alertes'], [$cid]));
            flash('Alerte désactivée.');
        } else {
            $utilisateurs[$i]['alertes'][] = $cid;
            flash('Vous serez prévenu des nouvelles offres.');
        }
    }
    sauver('utilisateurs', $utilisateurs);
    rediriger('favoris.php');
}

$commerces = charger('commerces');
$offres = charger('offres');

$titre_page = 'Mes favoris';
$menu = menu_consommateur($user);
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Mes favoris</h1></div>
        <div class="liste-cartes">
            <?php if (count($user['favoris']) == 0) { ?>
                <p class="vide">Vous n'avez pas encore de favori. Ajoutez-en depuis la page d'une offre.</p>
            <?php } ?>
            <?php foreach ($user['favoris'] as $cid) {
                $c = trouver($commerces, $cid);
                if (!$c) continue;
                $nb = 0;
                foreach ($offres as $o) {
                    if ($o['commerceId'] == $cid && $o['statut'] == 'PUBLIEE') $nb++;
                }
                $alerte = in_array($cid, $user['alertes']);
                ?>
                <div class="ligne-resa">
                    <img src="<?= e($c['photo']) ?>" alt="">
                    <div class="infos">
                        <h3><?= e($c['nom']) ?></h3>
                        <div class="meta"><?= e($c['type']) ?> · <?= e($c['adresse']) ?> · <?= $nb ?> offre(s) en ce moment</div>
                    </div>
                    <form method="post" class="actions">
                        <button class="btn btn-petit <?= $alerte ? 'btn-plein' : '' ?>" name="alerte" value="<?= $cid ?>">
                            <?= $alerte ? '🔔 Alerte activée' : 'M\'alerter des nouvelles offres' ?>
                        </button>
                        <button class="btn btn-petit btn-rouge" name="retirer" value="<?= $cid ?>">Retirer</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
