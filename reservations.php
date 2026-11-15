<?php
// Mes réservations : en cours + historique, notation après retrait
require 'includes/fonctions.php';
require 'includes/reservation.php';

$user = exiger_role('CONSOMMATEUR');
traiter_annulation($user);

// ----- Publier un avis (RG-06 : seulement si la réservation est retirée) -----
if (isset($_POST['noter'])) {
    $reservations = charger('reservations');
    $i = trouver_index($reservations, $_POST['noter']);
    $note = (int) $_POST['note'];
    if ($i >= 0 && $reservations[$i]['userId'] == $user['id'] && $reservations[$i]['statut'] == 'RETIREE'
        && $reservations[$i]['note'] === null && $note >= 1 && $note <= 5) {
        $reservations[$i]['note'] = $note;
        sauver('reservations', $reservations);

        $offre = trouver(charger('offres'), $reservations[$i]['offreId']);
        $commerces = charger('commerces');
        $j = trouver_index($commerces, $offre['commerceId']);
        // nouvelle moyenne du commerce
        $commerces[$j]['note'] = ($commerces[$j]['note'] * $commerces[$j]['nbAvis'] + $note) / ($commerces[$j]['nbAvis'] + 1);
        $commerces[$j]['nbAvis']++;
        sauver('commerces', $commerces);

        $avis = charger('avis');
        array_unshift($avis, [
            'id' => 'a' . time(),
            'commerceId' => $commerces[$j]['id'],
            'auteur' => $reservations[$i]['client'],
            'note' => $note,
            'texte' => trim($_POST['commentaire']),
            'date' => "Aujourd'hui",
        ]);
        sauver('avis', $avis);
        flash('Merci pour votre avis !');
    }
    rediriger('reservations.php?onglet=historique');
}

$onglet = $_GET['onglet'] ?? 'cours';
$nouvelle = $_GET['nouvelle'] ?? '';
$offres = charger('offres');
$commerces = charger('commerces');

$en_cours = [];
$historique = [];
foreach (charger('reservations') as $r) {
    if ($r['userId'] != $user['id']) continue;
    if ($r['statut'] == 'CONFIRMEE') {
        $en_cours[] = $r;
    } else {
        $historique[] = $r;
    }
}

$titre_page = 'Mes réservations';
$menu = menu_consommateur($user);
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Mes réservations</h1></div>

        <?php if ($nouvelle) { ?>
            <p class="pastille pastille-verte" style="margin-bottom: 14px; height: 30px; padding: 0 14px">✓ Réservation confirmée — présentez le QR code au commerçant.</p>
        <?php } ?>

        <div class="onglets-page">
            <a href="reservations.php" class="btn btn-petit <?= $onglet == 'cours' ? 'btn-plein' : '' ?>">En cours (<?= count($en_cours) ?>)</a>
            <a href="reservations.php?onglet=historique" class="btn btn-petit <?= $onglet == 'historique' ? 'btn-plein' : '' ?>">Historique (<?= count($historique) ?>)</a>
        </div>

        <div class="liste-cartes">
            <?php if ($onglet == 'cours') {
                if (count($en_cours) == 0) { ?>
                    <p class="vide">Aucune réservation en cours.</p>
                <?php }
                foreach ($en_cours as $r) { ?>
                    <div class="panneau" <?= $r['id'] == $nouvelle ? 'style="outline: 2px solid var(--vert)"' : '' ?>>
                        <?php afficher_retrait($r); ?>
                    </div>
                <?php }
            } else {
                foreach ($historique as $r) {
                    $o = trouver($offres, $r['offreId']);
                    $c = trouver($commerces, $o['commerceId']); ?>
                    <div class="ligne-resa">
                        <img src="<?= e($o['thumb']) ?>" alt="">
                        <div class="infos">
                            <h3><?= e($o['titre']) ?></h3>
                            <div class="meta"><?= e($c['nom']) ?> · <?= e($r['date']) ?> · <?= $r['quantite'] ?> panier(s) · <?= prix($o['prixReduit'] * $r['quantite']) ?></div>
                        </div>
                        <div class="actions">
                            <?= statut($r['statut']) ?>
                            <?php if ($r['statut'] == 'RETIREE' && $r['note'] === null) { ?>
                                <button class="btn btn-petit" data-modale="noter-<?= $r['id'] ?>">Noter</button>
                            <?php } ?>
                            <?php if ($r['note'] !== null) { ?>
                                <span class="aide">Votre note : <?= str_repeat('★', $r['note']) ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <?php if ($r['statut'] == 'RETIREE' && $r['note'] === null) { ?>
                        <div class="fond-modale" id="noter-<?= $r['id'] ?>">
                            <form class="modale" method="post">
                                <h2>Noter ce commerce</h2>
                                <p class="recap"><?= e($c['nom']) ?></p>
                                <label class="label" for="note-<?= $r['id'] ?>">Note</label>
                                <select id="note-<?= $r['id'] ?>" name="note" class="input" style="margin: 6px 0 12px">
                                    <option value="5">★★★★★ (5)</option>
                                    <option value="4">★★★★ (4)</option>
                                    <option value="3">★★★ (3)</option>
                                    <option value="2">★★ (2)</option>
                                    <option value="1">★ (1)</option>
                                </select>
                                <label class="label" for="com-<?= $r['id'] ?>">Commentaire</label>
                                <textarea id="com-<?= $r['id'] ?>" name="commentaire" class="input" placeholder="Qu'avez-vous pensé du panier ?" style="margin-top: 6px"></textarea>
                                <div class="modale-actions">
                                    <button type="button" class="btn fermer-modale">Annuler</button>
                                    <button class="btn btn-plein" name="noter" value="<?= $r['id'] ?>">Publier l'avis</button>
                                </div>
                            </form>
                        </div>
                    <?php }
                }
            } ?>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
