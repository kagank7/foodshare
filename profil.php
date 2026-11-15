<?php
// Mon profil (EF-03) + suppression du compte (RGPD)
require 'includes/fonctions.php';

$user = exiger_role('CONSOMMATEUR');
$preferences_possibles = ['Végétarien', 'Vegan', 'Sans gluten', 'Halal', 'Sans lactose'];

if (isset($_POST['enregistrer'])) {
    $utilisateurs = charger('utilisateurs');
    $i = trouver_index($utilisateurs, $user['id']);
    $utilisateurs[$i]['prenom'] = trim($_POST['prenom']);
    $utilisateurs[$i]['nom'] = trim($_POST['nom']);
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $utilisateurs[$i]['email'] = trim($_POST['email']);
    }
    $utilisateurs[$i]['rayon'] = (int) $_POST['rayon'];
    $utilisateurs[$i]['preferences'] = $_POST['preferences'] ?? [];
    sauver('utilisateurs', $utilisateurs);
    flash('Profil enregistré.');
    rediriger('profil.php');
}

if (isset($_POST['supprimer'])) {
    $utilisateurs = charger('utilisateurs');
    array_splice($utilisateurs, trouver_index($utilisateurs, $user['id']), 1);
    sauver('utilisateurs', $utilisateurs);
    unset($_SESSION['user_id']);
    flash('Votre compte a été supprimé.');
    rediriger('index.php');
}

$titre_page = 'Mon profil';
$menu = menu_consommateur($user);
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Mon profil</h1></div>
        <form class="form-carte" method="post">
            <h2>Informations personnelles</h2>
            <div class="ligne-2">
                <div class="champ"><label for="prenom">Prénom</label><input id="prenom" name="prenom" class="input" value="<?= e($user['prenom']) ?>" required></div>
                <div class="champ"><label for="nom">Nom</label><input id="nom" name="nom" class="input" value="<?= e($user['nom']) ?>" required></div>
            </div>
            <div class="champ"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" class="input" value="<?= e($user['email']) ?>" required></div>
            <hr>
            <h2>Recherche</h2>
            <div class="champ">
                <label for="rayon">Rayon de recherche (km)</label>
                <input id="rayon" name="rayon" type="number" min="1" max="10" class="input" style="max-width: 120px" value="<?= $user['rayon'] ?>">
            </div>
            <span class="label">Préférences alimentaires</span>
            <div class="coches" style="margin: 8px 0 14px">
                <?php foreach ($preferences_possibles as $p) { ?>
                    <label><input type="checkbox" name="preferences[]" value="<?= $p ?>" <?= in_array($p, $user['preferences']) ? 'checked' : '' ?>> <?= $p ?></label>
                <?php } ?>
            </div>
            <hr>
            <h2>Notifications</h2>
            <div class="coches" style="flex-direction: column; margin-bottom: 12px">
                <label><input type="checkbox" checked> Confirmation de réservation par e-mail</label>
                <label><input type="checkbox" checked> Rappel 1 h avant le retrait</label>
                <label><input type="checkbox" checked> Nouvelles offres de mes favoris</label>
            </div>
            <div class="boutons">
                <button class="btn btn-plein" name="enregistrer">Enregistrer</button>
                <button class="btn btn-rouge" type="button" data-modale="modale-suppression">Supprimer mon compte</button>
            </div>
        </form>

        <div class="fond-modale" id="modale-suppression">
            <div class="modale">
                <h2>Supprimer mon compte ?</h2>
                <p class="recap">Conformément au RGPD, toutes vos données personnelles seront effacées. Cette action est irréversible.</p>
                <form method="post" class="modale-actions">
                    <button type="button" class="btn fermer-modale">Annuler</button>
                    <button class="btn btn-rouge" name="supprimer">Supprimer définitivement</button>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
