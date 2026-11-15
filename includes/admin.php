<?php
// Fonctions communes aux pages d'administration

// Valider / refuser un commerce, traiter un signalement
function traiter_actions_admin()
{
    if (isset($_POST['valider_commerce']) || isset($_POST['refuser_commerce'])) {
        $valide = isset($_POST['valider_commerce']);
        $id = $valide ? $_POST['valider_commerce'] : $_POST['refuser_commerce'];
        $commerces = charger('commerces');
        $i = trouver_index($commerces, $id);
        if ($i >= 0) {
            $commerces[$i]['statut'] = $valide ? 'VALIDE' : 'REFUSE';
            sauver('commerces', $commerces);
            if ($commerces[$i]['gestionnaireId']) {
                notifier($commerces[$i]['gestionnaireId'], $valide ? 'Commerce validé' : 'Commerce refusé',
                    $valide ? 'Votre commerce est maintenant visible des consommateurs.' : "Votre demande n'a pas été acceptée.");
            }
            flash($commerces[$i]['nom'] . ($valide ? ' validé.' : ' refusé.'));
        }
        rediriger(basename($_SERVER['PHP_SELF']));
    }

    if (isset($_POST['conserver']) || isset($_POST['supprimer_avis'])) {
        $id = $_POST['conserver'] ?? $_POST['supprimer_avis'];
        $signalements = charger('signalements');
        array_splice($signalements, trouver_index($signalements, $id), 1);
        sauver('signalements', $signalements);
        flash(isset($_POST['conserver']) ? 'Avis conservé.' : 'Avis supprimé.');
        rediriger(basename($_SERVER['PHP_SELF']));
    }
}

function tableau_commerces_attente($liste)
{
    if (count($liste) == 0) {
        echo '<p class="vide">Aucun commerce en attente.</p>';
        return;
    }
    ?>
    <div class="scroll-x">
        <table class="tableau">
            <thead><tr><th>Commerce</th><th>Type</th><th>Ville</th><th>Demande</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($liste as $c) { ?>
                <tr>
                    <td><?= e($c['nom']) ?></td>
                    <td><?= e($c['type']) ?></td>
                    <td><?= e($c['ville']) ?></td>
                    <td><?= e($c['dateDemande']) ?></td>
                    <td>
                        <form method="post" class="actions">
                            <button class="btn btn-plein" style="width: 71px; font-weight: 400" name="valider_commerce" value="<?= $c['id'] ?>">Valider</button>
                            <button class="btn btn-gris" style="width: 73px" name="refuser_commerce" value="<?= $c['id'] ?>">Refuser</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}

function liste_signalements($liste)
{
    if (count($liste) == 0) {
        echo '<p class="vide">Aucun avis signalé.</p>';
        return;
    }
    foreach ($liste as $s) { ?>
        <div class="signalement">
            <h3><?= e($s['commerce']) ?> · ★ <?= $s['note'] ?></h3>
            <p>« <?= e($s['texte']) ?> »</p>
            <form method="post" class="actions">
                <button class="btn btn-gris" style="width: 88px" name="conserver" value="<?= $s['id'] ?>">Conserver</button>
                <button class="btn btn-rouge" style="width: 91px; font-weight: 400" name="supprimer_avis" value="<?= $s['id'] ?>">Supprimer</button>
            </form>
        </div>
    <?php }
}

function commerces_en_attente()
{
    $liste = [];
    foreach (charger('commerces') as $c) {
        if ($c['statut'] == 'EN_ATTENTE') $liste[] = $c;
    }
    return $liste;
}
