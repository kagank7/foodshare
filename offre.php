<?php
// Maquette 5 — Détail d'une offre et réservation
require 'includes/fonctions.php';

$offres = charger('offres');
$commerces = charger('commerces');
$user = utilisateur();
$id = $_GET['id'] ?? '';
$offre = trouver($offres, $id);
$erreur = '';

// ----- Ajouter / retirer des favoris -----
if (isset($_POST['favori']) && $user && $offre) {
    $utilisateurs = charger('utilisateurs');
    $i = trouver_index($utilisateurs, $user['id']);
    $cid = $offre['commerceId'];
    if (in_array($cid, $utilisateurs[$i]['favoris'])) {
        $utilisateurs[$i]['favoris'] = array_values(array_diff($utilisateurs[$i]['favoris'], [$cid]));
        flash('Commerce retiré de vos favoris.');
    } else {
        $utilisateurs[$i]['favoris'][] = $cid;
        flash('Commerce ajouté à vos favoris.');
    }
    sauver('utilisateurs', $utilisateurs);
    rediriger('offre.php?id=' . $id);
}

// ----- Réservation -----
if (isset($_POST['reserver']) && $offre) {
    $quantite = (int) $_POST['quantite'];

    if (!$user) {
        // pas connecté : on va à la connexion et on revient ici après
        rediriger('connexion.php?onglet=connexion&retour=' . urlencode('offre.php?id=' . $id));
    }

    // on compte ce que le client a déjà réservé sur cette offre
    $reservations = charger('reservations');
    $deja = 0;
    foreach ($reservations as $r) {
        if ($r['userId'] == $user['id'] && $r['offreId'] == $id && $r['statut'] == 'CONFIRMEE') {
            $deja += $r['quantite'];
        }
    }

    if ($user['role'] != 'CONSOMMATEUR') {
        $erreur = 'Seuls les comptes consommateurs peuvent réserver.';
    } elseif ($offre['statut'] != 'PUBLIEE' || $offre['stock'] <= 0 || minutes(HEURE_DEMO) > minutes($offre['fin'])) {
        $erreur = "Désolé, cette offre n'est plus disponible."; // RG-01
    } elseif ($quantite < 1 || $quantite > $offre['stock']) {
        $erreur = 'Il ne reste que ' . $offre['stock'] . ' panier(s).';
    } elseif ($deja + $quantite > 3) {
        $erreur = 'Vous ne pouvez pas réserver plus de 3 paniers pour une même offre.'; // RG-03
    } else {
        // tout est bon : on crée la réservation et on baisse le stock
        $code = generer_code();
        $nouvelle = [
            'id' => 'r' . time(),
            'code' => $code,
            'offreId' => $id,
            'userId' => $user['id'],
            'client' => $user['prenom'] . ' ' . mb_substr($user['nom'], 0, 1) . '.',
            'quantite' => $quantite,
            'statut' => 'CONFIRMEE',
            'date' => "Aujourd'hui",
            'heure' => $offre['debut'],
            'note' => null,
        ];
        array_unshift($reservations, $nouvelle);
        sauver('reservations', $reservations);

        $i = trouver_index($offres, $id);
        $offres[$i]['stock'] -= $quantite;
        if ($offres[$i]['stock'] == 0) {
            $offres[$i]['statut'] = 'EPUISEE';
        }
        sauver('offres', $offres);

        $c = trouver($commerces, $offre['commerceId']);
        notifier($user['id'], 'Réservation confirmée', $offre['titre'] . ' chez ' . $c['nom'] . '. Code ' . $code . '.');
        if ($c['gestionnaireId']) {
            notifier($c['gestionnaireId'], 'Nouvelle réservation', $nouvelle['client'] . ' a réservé ' . $quantite . ' « ' . $offre['titreCourt'] . ' » (' . $offre['debut'] . ').');
        }

        flash('Réservation confirmée ! Votre code : ' . $code);
        rediriger('reservations.php?nouvelle=' . $nouvelle['id']);
    }
}

$titre_page = $offre ? $offre['titre'] : 'Offre introuvable';
include 'includes/header.php';

if (!$offre) { ?>
    <main class="bloc-centre">
        <h1>Offre introuvable</h1>
        <p class="intro">Cette offre n'existe pas ou n'est plus en ligne.</p>
        <a href="offres.php" class="btn btn-plein">Voir les offres</a>
    </main>
    <?php include 'includes/footer.php';
    exit;
}

$c = trouver($commerces, $offre['commerceId']);
$remise = round((1 - $offre['prixReduit'] / $offre['prixInitial']) * 100);
$max = min(3, $offre['stock']);
$disponible = $offre['statut'] == 'PUBLIEE' && $offre['stock'] > 0;
$favori = $user && in_array($c['id'], $user['favoris']);

// 3 derniers avis du commerce
$avis = [];
foreach (charger('avis') as $a) {
    if ($a['commerceId'] == $c['id'] && count($avis) < 3) {
        $avis[] = $a;
    }
}
?>
<main class="detail">
    <div>
        <nav class="fil">
            <a href="offres.php">Offres</a> › <?= e($c['nom']) ?> › <?= e($offre['titreCourt']) ?>
        </nav>
        <img class="detail-img" src="<?= e($offre['photoGrande']) ?>" alt="<?= e($offre['titre']) ?>">
        <h1><?= e($offre['titre']) ?></h1>
        <div class="detail-tags">
            <span class="pastille pastille-verte"><?= e($offre['categorie']) ?></span>
            <span class="pastille">Plus que <?= $offre['stock'] ?> panier<?= $offre['stock'] > 1 ? 's' : '' ?></span>
            <span>★ <?= note($c['note']) ?> (<?= $c['nbAvis'] ?> avis)</span>
        </div>
        <p class="detail-desc">
            <?= e($offre['description']) ?>
            <?php if (count($offre['allergenes']) > 0) { ?>
                Allergènes : <?= e(implode(', ', $offre['allergenes'])) ?>.
            <?php } ?>
        </p>
        <div class="fiche-commerce">
            <img src="<?= e($c['photo']) ?>" alt="">
            <div>
                <h3><?= e($c['nom']) ?></h3>
                <div class="meta">📍 <?= e($c['adresse']) ?> · à <?= distance($c['distance']) ?></div>
            </div>
            <?php if ($user && $user['role'] == 'CONSOMMATEUR') { ?>
                <form method="post" class="coeur-form">
                    <button name="favori" class="coeur <?= $favori ? 'actif' : '' ?>"><?= $favori ? '♥ Favori' : '♡ Favori' ?></button>
                </form>
            <?php } ?>
        </div>

        <?php if (count($avis) > 0) { ?>
            <div class="avis-liste">
                <h2>Avis vérifiés</h2>
                <?php foreach ($avis as $a) { ?>
                    <div class="avis"><b><?= e($a['auteur']) ?></b> · <?= str_repeat('★', $a['note']) ?> — <?= e($a['texte']) ?></div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <form class="panneau-resa" method="post">
        <div class="gros-prix">
            <b><?= prix($offre['prixReduit']) ?></b>
            <s><?= number_format($offre['prixInitial'], 2, ',', '') ?> €</s>
            <span class="pastille pastille-rose">-<?= $remise ?> %</span>
        </div>

        <label class="label" for="creneau">Créneau de retrait</label>
        <select id="creneau" class="input select-creneau">
            <option>🕒 Aujourd'hui · <?= $offre['debut'] ?> – <?= $offre['fin'] ?></option>
        </select>

        <span class="label">Quantité</span>
        <div class="quantite">
            <button type="button" id="moins" aria-label="Diminuer">−</button>
            <b id="quantite-affichee">1</b>
            <button type="button" id="plus" aria-label="Augmenter">+</button>
            <span><?= $offre['stock'] ?> restant<?= $offre['stock'] > 1 ? 's' : '' ?></span>
        </div>
        <input type="hidden" name="quantite" id="quantite" value="1" data-max="<?= $max ?>">

        <div class="total">
            <span>Total à payer au retrait</span>
            <b id="total" data-prix="<?= $offre['prixReduit'] ?>"><?= prix($offre['prixReduit']) ?></b>
        </div>

        <button class="btn btn-orange btn-large" name="reserver" <?= $disponible ? '' : 'disabled' ?>>
            <?= $disponible ? 'Réserver ce panier' : 'Offre épuisée' ?>
        </button>
        <?php if ($erreur) { ?>
            <p class="msg-erreur" style="margin-top: 8px"><?= e($erreur) ?></p>
        <?php } ?>
        <p class="petit-centre">Annulation gratuite jusqu'à 1 h avant le retrait</p>
    </form>
</main>
<?php include 'includes/footer.php'; ?>
