<?php
// Gestion des comptes (EF-22) : consulter, suspendre, supprimer
require 'includes/fonctions.php';

$user = exiger_role('ADMIN');

if (isset($_POST['suspendre']) || isset($_POST['supprimer'])) {
    $utilisateurs = charger('utilisateurs');
    $id = $_POST['suspendre'] ?? $_POST['supprimer'];
    $i = trouver_index($utilisateurs, $id);
    if ($i >= 0 && $id != $user['id']) {
        if (isset($_POST['suspendre'])) {
            $utilisateurs[$i]['statut'] = $utilisateurs[$i]['statut'] == 'ACTIF' ? 'SUSPENDU' : 'ACTIF';
            flash($utilisateurs[$i]['statut'] == 'SUSPENDU' ? 'Compte suspendu.' : 'Compte réactivé.');
        } else {
            array_splice($utilisateurs, $i, 1);
            flash('Compte supprimé.');
        }
        sauver('utilisateurs', $utilisateurs);
    }
    rediriger('admin-utilisateurs.php');
}

$recherche = $_GET['q'] ?? '';
$roles = ['CONSOMMATEUR' => 'Consommateur', 'GESTIONNAIRE' => 'Commerçant', 'ADMIN' => 'Administrateur'];
$liste = [];
foreach (charger('utilisateurs') as $u) {
    $texte = strtolower($u['prenom'] . ' ' . $u['nom'] . ' ' . $u['email']);
    if ($recherche == '' || strpos($texte, strtolower($recherche)) !== false) {
        $liste[] = $u;
    }
}

$titre_page = 'Utilisateurs';
$menu = menu_admin();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Utilisateurs</h1></div>
        <section class="panneau">
            <form method="get" style="display: flex; gap: 8px; margin-bottom: 10px">
                <label for="q" class="sr-only">Rechercher</label>
                <input id="q" name="q" class="input" style="max-width: 340px" placeholder="Rechercher un nom ou un e-mail" value="<?= e($recherche) ?>">
                <button class="btn">Rechercher</button>
            </form>
            <div class="scroll-x">
                <table class="tableau">
                    <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Inscription</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($liste as $u) { ?>
                        <tr>
                            <td><?= e($u['prenom'] . ' ' . $u['nom']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= $roles[$u['role']] ?></td>
                            <td><?= e($u['inscription']) ?></td>
                            <td><?= statut($u['statut']) ?></td>
                            <td>
                                <?php if ($u['id'] != $user['id']) { ?>
                                    <div class="actions">
                                        <form method="post">
                                            <button class="btn btn-petit" name="suspendre" value="<?= $u['id'] ?>"><?= $u['statut'] == 'ACTIF' ? 'Suspendre' : 'Réactiver' ?></button>
                                        </form>
                                        <button class="btn btn-petit btn-rouge" data-modale="suppr-<?= $u['id'] ?>">Supprimer</button>
                                    </div>
                                    <div class="fond-modale" id="suppr-<?= $u['id'] ?>">
                                        <div class="modale">
                                            <h2>Supprimer ce compte ?</h2>
                                            <p class="recap">Le compte de <?= e($u['prenom'] . ' ' . $u['nom']) ?> et ses données personnelles seront supprimés définitivement.</p>
                                            <form method="post" class="modale-actions">
                                                <button type="button" class="btn fermer-modale">Annuler</button>
                                                <button class="btn btn-rouge" name="supprimer" value="<?= $u['id'] ?>">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
