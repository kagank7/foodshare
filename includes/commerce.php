<?php
// Fonctions communes aux pages de l'espace commerçant

// offres du commerce + réservations du jour
function donnees_commerce($commerce)
{
    $mes_offres = [];
    $ids = [];
    foreach (charger('offres') as $o) {
        if ($commerce && $o['commerceId'] == $commerce['id']) {
            $mes_offres[] = $o;
            $ids[] = $o['id'];
        }
    }
    $a_retirer = [];
    $retirees = [];
    foreach (charger('reservations') as $r) {
        if (in_array($r['offreId'], $ids) && $r['date'] == "Aujourd'hui") {
            if ($r['statut'] == 'CONFIRMEE') $a_retirer[] = $r;
            if ($r['statut'] == 'RETIREE') $retirees[] = $r;
        }
    }
    // les réservations à retirer d'abord
    return [$mes_offres, array_merge($a_retirer, $retirees)];
}

// message tant que le commerce n'est pas validé (RG-07)
function message_validation($commerce)
{
    if (!$commerce || $commerce['statut'] == 'VALIDE') {
        return;
    }
    if ($commerce['statut'] == 'EN_ATTENTE') {
        $texte = "⏳ Votre commerce est en attente de validation par l'équipe FoodShare. Vos offres ne sont pas encore visibles.";
    } else {
        $texte = "✕ Votre demande a été refusée. Contactez le support pour plus d'informations.";
    }
    echo '<p class="pastille" style="height: auto; padding: 8px 14px; margin-bottom: 14px; border-radius: 8px">' . $texte . '</p>';
}

// validation d'un retrait (cas d'utilisation « Valider un retrait »)
function traiter_retrait($commerce)
{
    if (!isset($_POST['valider_retrait'])) {
        return;
    }
    $reservations = charger('reservations');
    $offres = charger('offres');
    $i = trouver_index($reservations, $_POST['valider_retrait']);
    $o = trouver($offres, $reservations[$i]['offreId']);

    if ($i >= 0 && $o['commerceId'] == $commerce['id'] && $reservations[$i]['statut'] == 'CONFIRMEE') {
        $reservations[$i]['statut'] = 'RETIREE';
        sauver('reservations', $reservations);

        // on met à jour les statistiques du client
        $utilisateurs = charger('utilisateurs');
        $j = trouver_index($utilisateurs, $reservations[$i]['userId']);
        if ($j >= 0) {
            $q = $reservations[$i]['quantite'];
            $utilisateurs[$j]['stats']['paniers'] += $q;
            $utilisateurs[$j]['stats']['economies'] += round(($o['prixInitial'] - $o['prixReduit']) * $q);
            $utilisateurs[$j]['stats']['co2'] += 2.5 * $q;
            sauver('utilisateurs', $utilisateurs);
            notifier($utilisateurs[$j]['id'], 'Panier retiré', 'Merci ! Vous pouvez maintenant noter ' . $o['titreCourt'] . '.');
        }
        flash('Retrait validé (' . $reservations[$i]['client'] . ').');
    }
    rediriger(basename($_SERVER['PHP_SELF']));
}

// fenêtre de confirmation d'un retrait
function modale_retrait($r, $o)
{
    ?>
    <div class="fond-modale" id="retrait-<?= $r['id'] ?>">
        <div class="modale">
            <h2>Valider le retrait</h2>
            <div class="recap">
                <div>Client : <b><?= e($r['client']) ?></b></div>
                <div>Offre : <b><?= e($o['titre']) ?></b></div>
                <div>Quantité : <b><?= $r['quantite'] ?></b> · Créneau : <b><?= $o['debut'] ?> – <?= $o['fin'] ?></b></div>
                <div>Code : <b><?= e($r['code']) ?></b></div>
                <div>Montant à encaisser : <b><?= prix($o['prixReduit'] * $r['quantite']) ?></b></div>
            </div>
            <form method="post" class="modale-actions">
                <button type="button" class="btn fermer-modale">Annuler</button>
                <button class="btn btn-plein" name="valider_retrait" value="<?= $r['id'] ?>">Confirmer la remise et l'encaissement</button>
            </form>
        </div>
    </div>
    <?php
}

function tableau_reservations($resas)
{
    $offres = charger('offres');
    if (count($resas) == 0) {
        echo '<p class="vide">Aucune réservation aujourd\'hui.</p>';
        return;
    }
    ?>
    <div class="scroll-x">
        <table class="tableau">
            <thead>
            <tr><th>Client</th><th>Offre</th><th>Créneau</th><th>Code</th><th>Statut</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($resas as $r) {
                $o = trouver($offres, $r['offreId']); ?>
                <tr>
                    <td><?= e($r['client']) ?></td>
                    <td><?= e($o['titreCourt']) ?></td>
                    <td><?= $r['heure'] ?></td>
                    <td><?= e($r['code']) ?></td>
                    <td><?= statut($r['statut']) ?></td>
                    <td>
                        <?php if ($r['statut'] == 'CONFIRMEE') { ?>
                            <button class="btn btn-gris" style="width: 71px; font-weight: 400" data-modale="retrait-<?= $r['id'] ?>">Valider</button>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
    foreach ($resas as $r) {
        if ($r['statut'] == 'CONFIRMEE') {
            modale_retrait($r, trouver($offres, $r['offreId']));
        }
    }
}
