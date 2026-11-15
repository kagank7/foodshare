<?php
// Publier ou modifier une offre (EF-16, EF-17, EF-18)
require 'includes/fonctions.php';

$user = exiger_role('GESTIONNAIRE');
$commerce = mon_commerce($user);
$offres = charger('offres');
$existante = trouver($offres, $_GET['modifier'] ?? '');
if ($existante && $existante['commerceId'] != $commerce['id']) {
    $existante = null; // on ne modifie pas l'offre d'un autre commerce
}

$jours = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
$liste_allergenes = ['gluten', 'œufs', 'lait', 'fruits à coque', 'arachides', 'soja', 'poisson', 'crustacés'];
$liste_regimes = ['Végétarien', 'Vegan', 'Sans gluten', 'Halal'];

// valeurs du formulaire (offre existante ou valeurs par défaut)
$f = [
    'titre' => $existante['titre'] ?? '',
    'description' => $existante['description'] ?? '',
    'categorie' => $existante['categorie'] ?? ($commerce['type'] ?? 'Boulangerie'),
    'prixInitial' => $existante['prixInitial'] ?? '',
    'prixReduit' => $existante['prixReduit'] ?? '',
    'quantite' => $existante['quantiteTotale'] ?? 5,
    'debut' => $existante['debut'] ?? '18:00',
    'fin' => $existante['fin'] ?? '19:00',
    'allergenes' => $existante['allergenes'] ?? [],
    'regimes' => $existante['regimes'] ?? [],
    'recurrence' => $existante['recurrence'] ?? [],
];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $f['titre'] = trim($_POST['titre']);
    $f['description'] = trim($_POST['description']);
    $f['categorie'] = $_POST['categorie'];
    $f['prixInitial'] = str_replace(',', '.', $_POST['prixInitial']);
    $f['prixReduit'] = str_replace(',', '.', $_POST['prixReduit']);
    $f['quantite'] = (int) $_POST['quantite'];
    $f['debut'] = $_POST['debut'];
    $f['fin'] = $_POST['fin'];
    $f['allergenes'] = $_POST['allergenes'] ?? [];
    $f['regimes'] = $_POST['regimes'] ?? [];
    $f['recurrence'] = $_POST['recurrence'] ?? [];

    $pi = (float) $f['prixInitial'];
    $pr = (float) $f['prixReduit'];
    if (strlen($f['titre']) < 3) $erreurs['titre'] = 'Donnez un titre à votre offre.';
    if ($pi <= 0) $erreurs['prixInitial'] = 'Prix initial invalide.';
    if ($pr <= 0) $erreurs['prixReduit'] = 'Prix réduit invalide.';
    elseif ($pi > 0 && $pr > $pi / 2) $erreurs['prixReduit'] = 'Le prix réduit doit être ≤ 50 % du prix initial (max ' . prix($pi / 2) . ').'; // RG-02
    if ($f['quantite'] < 1) $erreurs['quantite'] = 'Au moins 1 panier.';
    if ($f['fin'] <= $f['debut']) $erreurs['fin'] = 'La fin du créneau doit être après le début.';

    // photo envoyée ?
    $photo = $existante['photo'] ?? 'assets/img/card-1.jpg';
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == 0) {
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            $photo = 'assets/img/uploads/' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        } else {
            $erreurs['photo'] = 'Format accepté : JPG, PNG ou WEBP.';
        }
    }

    if (count($erreurs) == 0) {
        $vendus = $existante ? $existante['quantiteTotale'] - $existante['stock'] : 0;
        $statut = isset($_POST['brouillon']) ? 'BROUILLON' : 'PUBLIEE';
        $offre = [
            'id' => $existante['id'] ?? 'o' . time(),
            'titre' => $f['titre'],
            'titreCourt' => implode(' ', array_slice(explode(' ', $f['titre']), 0, 2)),
            'commerceId' => $commerce['id'],
            'categorie' => $f['categorie'],
            'description' => $f['description'],
            'allergenes' => $f['allergenes'],
            'regimes' => $f['regimes'],
            'prixInitial' => $pi,
            'prixReduit' => $pr,
            'quantiteTotale' => $f['quantite'],
            'stock' => max(0, $f['quantite'] - $vendus),
            'debut' => $f['debut'],
            'fin' => $f['fin'],
            'photo' => $photo,
            'photoGrande' => $existante && $photo == $existante['photo'] ? $existante['photoGrande'] : $photo,
            'thumb' => $existante && $photo == $existante['photo'] ? $existante['thumb'] : $photo,
            'statut' => $existante ? $existante['statut'] : $statut,
            'recurrence' => $f['recurrence'],
            'pin' => $existante['pin'] ?? ['x' => rand(15, 85), 'y' => rand(15, 85)],
        ];

        if ($existante) {
            $offres[trouver_index($offres, $existante['id'])] = $offre;
        } else {
            $offres[] = $offre;
            // EF-12 : on prévient les clients qui ont mis une alerte sur ce commerce
            if ($statut == 'PUBLIEE') {
                foreach (charger('utilisateurs') as $u) {
                    if (in_array($commerce['id'], $u['alertes'])) {
                        notifier($u['id'], "Nouvelle offre d'un favori", '« ' . $offre['titre'] . ' » à ' . prix($pr) . '.');
                    }
                }
            }
        }
        sauver('offres', $offres);
        flash($existante ? 'Offre modifiée.' : ($statut == 'PUBLIEE' ? 'Offre publiée !' : 'Brouillon enregistré.'));
        rediriger('mes-offres.php');
    }
}

function erreur($nom, $erreurs)
{
    if (isset($erreurs[$nom])) {
        echo '<span class="msg-erreur">' . $erreurs[$nom] . '</span>';
    }
}

$titre_page = $existante ? "Modifier l'offre" : 'Publier une offre';
$menu = menu_commercant();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1><?= $titre_page ?></h1></div>
        <form class="form-carte" method="post" enctype="multipart/form-data">
            <h2>Le panier</h2>
            <div class="champ">
                <label for="titre">Titre</label>
                <input id="titre" name="titre" class="input" placeholder="Panier surprise boulangerie" value="<?= e($f['titre']) ?>" required minlength="3" data-erreur="Donnez un titre à votre offre.">
                <?php erreur('titre', $erreurs); ?>
            </div>
            <div class="champ">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="input" placeholder="Contenu du panier, conseils de conservation…"><?= e($f['description']) ?></textarea>
            </div>
            <div class="ligne-2">
                <div class="champ">
                    <label for="categorie">Catégorie</label>
                    <select id="categorie" name="categorie" class="input">
                        <?php foreach (['Boulangerie', 'Restaurant', 'Supermarché', 'Primeur', 'Traiteur'] as $cat) { ?>
                            <option <?= $f['categorie'] == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="champ">
                    <label for="photo">Photo</label>
                    <input id="photo" name="photo" type="file" accept="image/*">
                    <img id="apercu" class="apercu-photo" alt="Aperçu" src="<?= e($existante['photo'] ?? '') ?>" style="display: <?= $existante ? 'block' : 'none' ?>">
                    <?php erreur('photo', $erreurs); ?>
                </div>
            </div>
            <hr>
            <h2>Prix et quantité</h2>
            <div class="ligne-3">
                <div class="champ">
                    <label for="pi">Prix initial (€)</label>
                    <input id="pi" name="prixInitial" class="input" inputmode="decimal" placeholder="10" value="<?= e($f['prixInitial']) ?>" required>
                    <?php erreur('prixInitial', $erreurs); ?>
                </div>
                <div class="champ">
                    <label for="pr">Prix réduit (€)</label>
                    <input id="pr" name="prixReduit" class="input" inputmode="decimal" placeholder="3,50" value="<?= e($f['prixReduit']) ?>" required>
                    <span id="info-remise"></span>
                    <?php erreur('prixReduit', $erreurs); ?>
                </div>
                <div class="champ">
                    <label for="quantite">Quantité</label>
                    <input id="quantite" name="quantite" type="number" min="1" class="input" value="<?= e($f['quantite']) ?>">
                    <?php erreur('quantite', $erreurs); ?>
                </div>
            </div>
            <hr>
            <h2>Retrait</h2>
            <div class="ligne-2">
                <div class="champ"><label for="debut">Début du créneau</label><input id="debut" name="debut" type="time" class="input" value="<?= e($f['debut']) ?>"></div>
                <div class="champ"><label for="fin">Fin du créneau</label><input id="fin" name="fin" type="time" class="input" value="<?= e($f['fin']) ?>"><?php erreur('fin', $erreurs); ?></div>
            </div>
            <span class="label">Répéter chaque semaine (offre récurrente)</span>
            <div class="coches" style="margin: 8px 0 14px">
                <?php foreach ($jours as $j) { ?>
                    <label><input type="checkbox" name="recurrence[]" value="<?= $j ?>" <?= in_array($j, $f['recurrence']) ? 'checked' : '' ?>> <?= $j ?></label>
                <?php } ?>
            </div>
            <hr>
            <h2>Allergènes et régimes</h2>
            <div class="coches" style="margin-bottom: 12px">
                <?php foreach ($liste_allergenes as $a) { ?>
                    <label><input type="checkbox" name="allergenes[]" value="<?= $a ?>" <?= in_array($a, $f['allergenes']) ? 'checked' : '' ?>> <?= $a ?></label>
                <?php } ?>
            </div>
            <div class="coches" style="margin-bottom: 14px">
                <?php foreach ($liste_regimes as $r) { ?>
                    <label><input type="checkbox" name="regimes[]" value="<?= $r ?>" <?= in_array($r, $f['regimes']) ? 'checked' : '' ?>> <?= $r ?></label>
                <?php } ?>
            </div>
            <div class="boutons">
                <button class="btn btn-orange" name="publier"><?= $existante ? 'Enregistrer les modifications' : "Publier l'offre" ?></button>
                <?php if (!$existante) { ?>
                    <button class="btn" name="brouillon" formnovalidate>Enregistrer en brouillon</button>
                <?php } ?>
                <a href="mes-offres.php" class="btn btn-gris">Annuler</a>
            </div>
        </form>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
