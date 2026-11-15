<?php
// Maquette 1 — Page d'accueil
require 'includes/fonctions.php';

$offres = charger('offres');
$commerces = charger('commerces');

// les 4 premières offres publiées
$offres_du_moment = [];
foreach ($offres as $o) {
    if ($o['statut'] == 'PUBLIEE' && count($offres_du_moment) < 4) {
        $offres_du_moment[] = $o;
    }
}

$titre_page = 'Accueil';
include 'includes/header.php';
?>
<main class="page">
    <section class="hero">
        <div>
            <span class="pastille">Anti-gaspillage · près de chez vous</span>
            <h1>Sauvez de bons repas, payez moins cher.</h1>
            <p class="hero-texte">
                Boulangeries, restaurants et supermarchés proposent leurs invendus du jour à prix réduit.
                Réservez, retirez, savourez.
            </p>
            <form class="recherche" action="offres.php" method="get" role="search">
                <label for="adresse" class="sr-only">Adresse</label>
                <input id="adresse" name="lieu" class="input" placeholder="Adresse, ville ou code postal">
                <button class="btn btn-plein" type="submit">Trouver des paniers</button>
            </form>
            <div class="chiffres">
                <div class="chiffre"><b>12 400</b><span>paniers sauvés</span></div>
                <div class="chiffre"><b>180</b><span>commerces partenaires</span></div>
                <div class="chiffre"><b>31 t</b><span>de CO₂ évitées</span></div>
            </div>
        </div>
        <img class="hero-img" src="assets/img/hero.jpg" alt="Panier de fruits et légumes frais">
    </section>

    <h2 class="titre-section">Offres du moment près de vous</h2>
    <div class="grille-offres">
        <?php foreach ($offres_du_moment as $o) {
            $c = trouver($commerces, $o['commerceId']); ?>
            <a href="offre.php?id=<?= $o['id'] ?>" class="carte-offre">
                <img src="<?= e($o['photo']) ?>" alt="<?= e($o['titre']) ?>">
                <h3><?= e($o['titre']) ?></h3>
                <div class="meta">📍 <?= e($c['nom']) ?> · <?= distance($c['distance']) ?></div>
                <div class="prix-ligne">
                    <span class="prix"><?= prix($o['prixReduit']) ?><s><?= prix_court($o['prixInitial']) ?></s></span>
                    <span class="pastille">Plus que <?= $o['stock'] ?></span>
                </div>
            </a>
        <?php } ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
