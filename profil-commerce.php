<?php
// Profil du commerce (EF-15)
require 'includes/fonctions.php';
require 'includes/commerce.php';

$user = exiger_role('GESTIONNAIRE');
$commerce = mon_commerce($user);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $commerce) {
    $commerces = charger('commerces');
    $i = trouver_index($commerces, $commerce['id']);
    $commerces[$i]['nom'] = trim($_POST['nom']);
    $commerces[$i]['adresse'] = trim($_POST['adresse']);
    $commerces[$i]['horaires'] = trim($_POST['horaires']);
    $commerces[$i]['description'] = trim($_POST['description']);
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] == 0) {
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            $chemin = 'assets/img/uploads/' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['photo']['tmp_name'], $chemin);
            $commerces[$i]['photo'] = $chemin;
        }
    }
    sauver('commerces', $commerces);
    flash('Profil du commerce enregistré.');
    rediriger('profil-commerce.php');
}

$titre_page = 'Profil du commerce';
$menu = menu_commercant();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <?php message_validation($commerce); ?>
        <div class="contenu-titre"><h1>Profil du commerce</h1></div>
        <form class="form-carte" method="post" enctype="multipart/form-data">
            <div class="ligne-2">
                <div class="champ"><label for="nom">Nom du commerce</label><input id="nom" name="nom" class="input" value="<?= e($commerce['nom']) ?>" required></div>
                <div class="champ"><label>SIRET</label><input class="input" value="<?= e($commerce['siret']) ?>" disabled></div>
            </div>
            <div class="champ"><label for="adresse">Adresse</label><input id="adresse" name="adresse" class="input" value="<?= e($commerce['adresse']) ?>"></div>
            <div class="champ"><label for="horaires">Horaires d'ouverture</label><input id="horaires" name="horaires" class="input" value="<?= e($commerce['horaires']) ?>"></div>
            <div class="champ"><label for="description">Description</label><textarea id="description" name="description" class="input"><?= e($commerce['description']) ?></textarea></div>
            <div class="champ">
                <label for="photo">Photo de la devanture</label>
                <input id="photo" name="photo" type="file" accept="image/*">
                <img id="apercu" class="apercu-photo" alt="Devanture" src="<?= e($commerce['photo']) ?>" style="display: <?= $commerce['photo'] ? 'block' : 'none' ?>">
            </div>
            <div class="boutons"><button class="btn btn-plein">Enregistrer</button></div>
        </form>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
