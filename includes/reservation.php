<?php
// Bloc "réservation à retirer" avec le QR code
// utilisé sur le tableau de bord et sur la page Mes réservations

// annulation d'une réservation (formulaire envoyé depuis la fenêtre de confirmation)
function traiter_annulation($user)
{
    if (!isset($_POST['annuler'])) {
        return;
    }
    $reservations = charger('reservations');
    $offres = charger('offres');
    $i = trouver_index($reservations, $_POST['annuler']);
    if ($i < 0 || $reservations[$i]['userId'] != $user['id']) {
        return;
    }
    $j = trouver_index($offres, $reservations[$i]['offreId']);

    if (!est_annulable($offres[$j]['debut'])) {
        flash("L'annulation n'est plus possible (moins d'1 h avant le retrait).");
    } else {
        $reservations[$i]['statut'] = 'ANNULEE';
        // RG-04 : le stock est rendu
        $offres[$j]['stock'] += $reservations[$i]['quantite'];
        if ($offres[$j]['statut'] == 'EPUISEE') {
            $offres[$j]['statut'] = 'PUBLIEE';
        }
        sauver('reservations', $reservations);
        sauver('offres', $offres);
        flash('Réservation annulée, le panier est remis en vente.');
    }
    rediriger(basename($_SERVER['PHP_SELF']));
}

function afficher_retrait($r)
{
    $o = trouver(charger('offres'), $r['offreId']);
    $c = trouver(charger('commerces'), $o['commerceId']);
    $annulable = est_annulable($o['debut']);
    $adresse_courte = explode(',', $c['adresse'])[0];
    ?>
    <div class="resa">
        <img class="qr" src="qrcode.php?code=<?= e($r['code']) ?>" alt="QR code <?= e($r['code']) ?>">
        <div>
            <h3><?= e($o['titre']) ?></h3>
            <div class="meta"><?= e($c['nom']) ?> · <?= e($adresse_courte) ?></div>
            <span class="pastille">🕒 Retrait <?= $o['debut'] ?> – <?= $o['fin'] ?></span>
            <div class="resa-code">
                Code : <b><?= e($r['code']) ?></b> · Montant : <b><?= prix($o['prixReduit'] * $r['quantite']) ?></b>
                <?php if ($r['quantite'] > 1) { ?><span class="aide">(<?= $r['quantite'] ?> paniers)</span><?php } ?>
            </div>
            <div class="resa-actions">
                <a class="btn btn-gris" target="_blank" href="https://www.openstreetmap.org/search?query=<?= urlencode($c['adresse']) ?>">Itinéraire</a>
                <button class="btn btn-rouge" style="font-weight: 400" data-modale="annuler-<?= $r['id'] ?>" <?= $annulable ? '' : 'disabled title="Moins d\'1 h avant le retrait"' ?>>Annuler</button>
            </div>
        </div>
    </div>

    <div class="fond-modale" id="annuler-<?= $r['id'] ?>">
        <div class="modale">
            <h2>Annuler la réservation ?</h2>
            <p class="recap">Le panier « <?= e($o['titre']) ?> » sera remis en vente. Cette action est définitive.</p>
            <form method="post" class="modale-actions">
                <button type="button" class="btn fermer-modale">Garder</button>
                <button class="btn btn-rouge" name="annuler" value="<?= $r['id'] ?>">Oui, annuler</button>
            </form>
        </div>
    </div>
    <?php
}
