<?php
// Maquette 4 — Recherche d'offres (liste + carte)
require 'includes/fonctions.php';

$offres = charger('offres');
$commerces = charger('commerces');
$user = utilisateur();

// filtres passés dans l'URL
$categorie = $_GET['categorie'] ?? 'Tout';
$vegetarien = isset($_GET['vegetarien']);
$moins5 = isset($_GET['moins5']);
$soir = isset($_GET['soir']);
$lieu = $_GET['lieu'] ?? '';
if ($lieu == '') {
    $lieu = 'Paris 11e · rayon ' . ($user['rayon'] ?? 2) . ' km';
}

$resultats = [];
foreach ($offres as $o) {
    $c = trouver($commerces, $o['commerceId']);
    // RG-07 : seulement les commerces validés
    if ($o['statut'] != 'PUBLIEE' || $c['statut'] != 'VALIDE') continue;
    if ($categorie != 'Tout' && $o['categorie'] != $categorie) continue;
    if ($vegetarien && !in_array('Végétarien', $o['regimes'])) continue;
    if ($moins5 && $o['prixReduit'] >= 5) continue;
    if ($soir && minutes($o['debut']) < minutes('19:00')) continue;
    $resultats[] = $o;
}

// construit l'URL d'un filtre en gardant les autres
function lien_filtre($changement)
{
    $params = array_merge($_GET, $changement);
    foreach ($params as $cle => $valeur) {
        if ($valeur === null) unset($params[$cle]);
    }
    return 'offres.php?' . http_build_query($params);
}

$titre_page = 'Offres près de moi';
include 'includes/header.php';
?>
<main class="recherche-page">
    <section class="liste-col">
        <form action="offres.php" method="get">
            <label for="lieu" class="sr-only">Lieu de recherche</label>
            <input id="lieu" name="lieu" class="input" value="<?= e($lieu) ?>">
        </form>

        <div class="puces">
            <?php foreach (['Tout', 'Boulangerie', 'Restaurant', 'Supermarché'] as $cat) { ?>
                <a href="<?= e(lien_filtre(['categorie' => $cat == 'Tout' ? null : $cat])) ?>" class="puce <?= $categorie == $cat ? 'active' : '' ?>"><?= $cat ?></a>
            <?php } ?>
            <a href="<?= e(lien_filtre(['vegetarien' => $vegetarien ? null : 1])) ?>" class="puce <?= $vegetarien ? 'active' : '' ?>">Végétarien</a>
            <a href="<?= e(lien_filtre(['moins5' => $moins5 ? null : 1])) ?>" class="puce <?= $moins5 ? 'active' : '' ?>">Moins de 5 €</a>
            <a href="<?= e(lien_filtre(['soir' => $soir ? null : 1])) ?>" class="puce <?= $soir ? 'active' : '' ?>">Après 19 h</a>
        </div>

        <p class="compte-offres"><?= count($resultats) ?> offre<?= count($resultats) > 1 ? 's' : '' ?> disponible<?= count($resultats) > 1 ? 's' : '' ?> · triées par distance</p>

        <?php if (count($resultats) == 0) { ?>
            <p class="vide">Aucune offre ne correspond à vos filtres.</p>
        <?php } ?>

        <?php foreach ($resultats as $o) {
            $c = trouver($commerces, $o['commerceId']); ?>
            <a href="offre.php?id=<?= $o['id'] ?>" class="offre-ligne" data-id="<?= $o['id'] ?>">
                <img src="<?= e($o['thumb']) ?>" alt="">
                <div>
                    <h3><?= e($o['titre']) ?></h3>
                    <div class="meta"><?= e($c['nom']) ?> · <?= distance($c['distance']) ?> · ★ <?= note($c['note']) ?></div>
                    <div class="meta">🕒 Retrait <?= $o['debut'] ?>–<?= $o['fin'] ?></div>
                    <div class="prix"><?= prix($o['prixReduit']) ?><s><?= prix_court($o['prixInitial']) ?></s></div>
                </div>
                <span class="pastille">Plus que <?= $o['stock'] ?></span>
            </a>
        <?php } ?>
    </section>

    <section class="carte-col">
        <div class="carte">
            <!-- carte simplifiée : fond vert + la Seine en bleu, comme sur la maquette -->
            <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                <polygon points="0,51.1 100,35.9 100,42.4 0,57.7" fill="#bfdaef" />
            </svg>
            <?php foreach ($resultats as $i => $o) { ?>
                <a href="offre.php?id=<?= $o['id'] ?>" id="pin-<?= $o['id'] ?>" class="epingle <?= $i == 0 ? 'active' : '' ?>"
                   style="left: <?= $o['pin']['x'] ?>%; top: <?= $o['pin']['y'] ?>%;" title="<?= e($o['titre']) ?>"><?= $i + 1 ?></a>
            <?php } ?>
            <span class="carte-legende">Carte simplifiée · données fictives</span>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
