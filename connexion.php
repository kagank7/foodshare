<?php
// Maquette 2 — Inscription et connexion (un seul écran avec deux onglets)
require 'includes/fonctions.php';

$onglet = $_GET['onglet'] ?? 'inscription';
$retour = $_GET['retour'] ?? '';
$erreurs = [];
$erreur_connexion = '';
$info = '';
$valeurs = $_POST; // pour re-remplir le formulaire en cas d'erreur

// ----- Connexion -----
if (isset($_POST['connexion'])) {
    $onglet = 'connexion';
    $email = trim($_POST['email']);

    // NF-S5 : blocage après 5 échecs
    if (($_SESSION['echecs'] ?? 0) >= 5) {
        $erreur_connexion = 'Trop de tentatives. Compte bloqué temporairement (15 min).';
    } else {
        $trouve = null;
        foreach (charger('utilisateurs') as $u) {
            if (strtolower($u['email']) == strtolower($email)) {
                $trouve = $u;
            }
        }
        if (!$trouve || !password_verify($_POST['mdp'], $trouve['mdp'])) {
            $_SESSION['echecs'] = ($_SESSION['echecs'] ?? 0) + 1;
            $erreur_connexion = 'Adresse e-mail ou mot de passe incorrect.';
        } elseif ($trouve['statut'] == 'SUSPENDU') {
            $erreur_connexion = 'Ce compte est suspendu. Contactez le support.';
        } else {
            $_SESSION['echecs'] = 0;
            $_SESSION['user_id'] = $trouve['id'];
            if ($retour != '') {
                rediriger($retour);
            }
            rediriger(page_espace($trouve['role']));
        }
    }
}

// ----- Mot de passe oublié (simulé : pas d'envoi d'e-mail) -----
if (isset($_POST['oubli'])) {
    $onglet = 'connexion';
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erreur_connexion = "Saisissez d'abord votre adresse e-mail.";
    } else {
        $info = 'Un lien de réinitialisation a été envoyé à ' . $_POST['email'] . ' (simulation).';
    }
}

// ----- Inscription -----
if (isset($_POST['inscription'])) {
    $role = $_POST['role'] == 'GESTIONNAIRE' ? 'GESTIONNAIRE' : 'CONSOMMATEUR';

    // vérifications côté serveur (en plus de celles du navigateur)
    if (trim($_POST['prenom']) == '') $erreurs['prenom'] = 'Le prénom est obligatoire.';
    if (trim($_POST['nom']) == '') $erreurs['nom'] = 'Le nom est obligatoire.';
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $erreurs['email'] = 'Adresse e-mail invalide.';
    if (strlen($_POST['mdp']) < 8) $erreurs['mdp'] = '8 caractères minimum.';
    if ($_POST['mdp2'] != $_POST['mdp']) $erreurs['mdp2'] = 'Les mots de passe ne correspondent pas.';
    if ($role == 'GESTIONNAIRE') {
        if (trim($_POST['commerce']) == '') $erreurs['commerce'] = 'Nom du commerce obligatoire.';
        if (!preg_match('/^[0-9]{14}$/', str_replace(' ', '', $_POST['siret']))) $erreurs['siret'] = 'Le SIRET contient 14 chiffres.';
        if (trim($_POST['adresse']) == '') $erreurs['adresse'] = 'Adresse obligatoire.';
    }
    if (!isset($_POST['cgu'])) $erreurs['cgu'] = "Vous devez accepter les conditions d'utilisation.";

    $utilisateurs = charger('utilisateurs');
    foreach ($utilisateurs as $u) {
        if (strtolower($u['email']) == strtolower($_POST['email'])) {
            $erreurs['email'] = 'Un compte existe déjà avec cette adresse e-mail.';
        }
    }

    if (count($erreurs) == 0) {
        $id = 'u' . time();
        $utilisateurs[] = [
            'id' => $id,
            'prenom' => trim($_POST['prenom']),
            'nom' => trim($_POST['nom']),
            'email' => trim($_POST['email']),
            'mdp' => password_hash($_POST['mdp'], PASSWORD_DEFAULT), // NF-S1 : mot de passe haché
            'role' => $role,
            'statut' => 'ACTIF',
            'inscription' => date('d/m/Y'),
            'rayon' => 2,
            'preferences' => [],
            'favoris' => [],
            'alertes' => [],
            'stats' => ['paniers' => 0, 'economies' => 0, 'co2' => 0],
        ];
        sauver('utilisateurs', $utilisateurs);

        if ($role == 'GESTIONNAIRE') {
            // RG-07 : le commerce attend la validation d'un administrateur
            $commerces = charger('commerces');
            $commerces[] = [
                'id' => 'c' . time(),
                'nom' => trim($_POST['commerce']),
                'type' => $_POST['type'],
                'adresse' => trim($_POST['adresse']),
                'ville' => 'Paris',
                'distance' => 0,
                'note' => 0,
                'nbAvis' => 0,
                'photo' => '',
                'horaires' => '',
                'description' => '',
                'siret' => $_POST['siret'],
                'statut' => 'EN_ATTENTE',
                'dateDemande' => date('d/m'),
                'gestionnaireId' => $id,
            ];
            sauver('commerces', $commerces);
            flash('Compte créé ! Votre commerce est en attente de validation par FoodShare.');
        } else {
            flash('Bienvenue sur FoodShare !');
        }
        $_SESSION['user_id'] = $id;
        rediriger(page_espace($role));
    }
}

$role_choisi = $valeurs['role'] ?? 'CONSOMMATEUR';

// affiche un champ du formulaire d'inscription avec son erreur éventuelle
function champ($nom, $label, $placeholder, $type, $attributs, $message, $erreurs, $valeurs)
{
    $classe = isset($erreurs[$nom]) ? 'input erreur' : 'input';
    $valeur = ($type == 'password') ? '' : e($valeurs[$nom] ?? '');
    echo '<div class="champ">';
    echo '<label for="' . $nom . '">' . $label . '</label>';
    echo '<input id="' . $nom . '" name="' . $nom . '" type="' . $type . '" class="' . $classe . '" placeholder="' . $placeholder . '" value="' . $valeur . '" ' . $attributs . ' data-erreur="' . $message . '">';
    if (isset($erreurs[$nom])) {
        echo '<span class="msg-erreur">' . $erreurs[$nom] . '</span>';
    }
    echo '</div>';
}

$titre_page = $onglet == 'connexion' ? 'Connexion' : 'Inscription';
include 'includes/header.php';
?>
<main class="auth-fond">
    <div class="auth">
        <div class="auth-gauche">
            <h1>Rejoignez le mouvement anti-gaspi</h1>
            <p>Réservez des paniers à prix réduit ou valorisez vos invendus en quelques clics.</p>
            <img src="assets/img/cart.jpg" alt="Chariot de courses dans un supermarché">
        </div>

        <div class="auth-droite">
            <div class="onglets">
                <a href="connexion.php<?= $retour ? '?retour=' . urlencode($retour) : '' ?>" class="onglet <?= $onglet != 'connexion' ? 'actif' : '' ?>">Inscription</a>
                <a href="connexion.php?onglet=connexion<?= $retour ? '&retour=' . urlencode($retour) : '' ?>" class="onglet <?= $onglet == 'connexion' ? 'actif' : '' ?>">Connexion</a>
            </div>

            <?php if ($onglet != 'connexion') { ?>
                <!-- ===== Formulaire d'inscription ===== -->
                <form method="post" enctype="multipart/form-data">
                    <span class="label">Je suis...</span>
                    <div class="choix-role">
                        <button type="button" data-role="CONSOMMATEUR" class="<?= $role_choisi == 'CONSOMMATEUR' ? 'actif' : '' ?>">Consommateur</button>
                        <button type="button" data-role="GESTIONNAIRE" class="<?= $role_choisi == 'GESTIONNAIRE' ? 'actif' : '' ?>">Commerçant</button>
                    </div>
                    <input type="hidden" name="role" id="role" value="<?= e($role_choisi) ?>">

                    <div class="ligne-2">
                        <?php champ('prenom', 'Prénom', 'Camille', 'text', 'required', 'Le prénom est obligatoire.', $erreurs, $valeurs); ?>
                        <?php champ('nom', 'Nom', 'Martin', 'text', 'required', 'Le nom est obligatoire.', $erreurs, $valeurs); ?>
                    </div>

                    <!-- champs en plus pour un commerçant (affichés par le JavaScript) -->
                    <div id="champs-commerce" style="display: <?= $role_choisi == 'GESTIONNAIRE' ? 'block' : 'none' ?>">
                        <div class="ligne-2">
                            <?php champ('commerce', 'Nom du commerce', 'Boulangerie Dupain', 'text', '', 'Nom du commerce obligatoire.', $erreurs, $valeurs); ?>
                            <div class="champ">
                                <label for="type">Type de commerce</label>
                                <select id="type" name="type" class="input">
                                    <option>Boulangerie</option>
                                    <option>Restaurant</option>
                                    <option>Supermarché</option>
                                    <option>Primeur</option>
                                    <option>Traiteur</option>
                                </select>
                            </div>
                        </div>
                        <div class="ligne-2">
                            <?php champ('siret', 'Numéro SIRET', '812 345 678 00012', 'text', 'pattern="[0-9 ]{14,17}"', 'Le SIRET contient 14 chiffres.', $erreurs, $valeurs); ?>
                            <?php champ('adresse', 'Adresse du commerce', '12 rue des Lilas, 75011 Paris', 'text', '', 'Adresse obligatoire.', $erreurs, $valeurs); ?>
                        </div>
                        <div class="champ">
                            <label for="justif">Justificatif (Kbis, PDF)</label>
                            <input id="justif" name="justif" type="file" accept=".pdf,image/*">
                        </div>
                    </div>

                    <?php champ('email', 'Adresse e-mail', 'camille.martin@email.fr', 'email', 'required', 'Adresse e-mail invalide.', $erreurs, $valeurs); ?>
                    <?php champ('mdp', 'Mot de passe', '••••••••••', 'password', 'required minlength="8"', '8 caractères minimum.', $erreurs, $valeurs); ?>
                    <?php champ('mdp2', 'Confirmer le mot de passe', '••••••••••', 'password', 'required', 'Les mots de passe ne correspondent pas.', $erreurs, $valeurs); ?>

                    <label class="case-cgu">
                        <input type="checkbox" name="cgu" <?= isset($valeurs['cgu']) ? 'checked' : '' ?>>
                        J'accepte les conditions d'utilisation et la politique de confidentialité
                    </label>
                    <?php if (isset($erreurs['cgu'])) { ?>
                        <p class="msg-erreur" style="margin: -6px 0 8px"><?= $erreurs['cgu'] ?></p>
                    <?php } ?>

                    <button class="btn btn-plein btn-large" type="submit" name="inscription">Créer mon compte</button>
                    <p class="sous-lien">Déjà inscrit ? <a class="btn-lien" href="connexion.php?onglet=connexion">Se connecter</a></p>
                </form>

            <?php } else { ?>
                <!-- ===== Formulaire de connexion ===== -->
                <form method="post">
                    <div class="champ">
                        <label for="email">Adresse e-mail</label>
                        <input id="email" name="email" type="email" class="input" placeholder="camille.martin@email.fr" value="<?= e($valeurs['email'] ?? '') ?>">
                    </div>
                    <div class="champ">
                        <label for="mdp">Mot de passe</label>
                        <input id="mdp" name="mdp" type="password" class="input" placeholder="••••••••••">
                    </div>
                    <?php if ($erreur_connexion) { ?><p class="msg-erreur" style="margin-bottom: 8px"><?= e($erreur_connexion) ?></p><?php } ?>
                    <?php if ($info) { ?><p class="msg-ok" style="margin-bottom: 8px"><?= e($info) ?></p><?php } ?>
                    <button class="btn btn-plein btn-large" type="submit" name="connexion">Se connecter</button>
                    <p class="sous-lien">
                        <button name="oubli" class="btn-lien" style="font-weight: 600">Mot de passe oublié ?</button>
                    </p>
                    <p class="sous-lien">Pas encore de compte ? <a class="btn-lien" href="connexion.php">S'inscrire</a></p>
                    <div class="demo-comptes">
                        <b>Comptes de démonstration</b> (mot de passe <code>demo1234</code>) :<br>
                        camille.martin@email.fr (consommateur)<br>
                        contact@dupain.fr (commerçant)<br>
                        admin@foodshare.fr (administrateur)
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
