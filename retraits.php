<?php
// Retraits du jour (EF-19) : le commerçant saisit le code du client
require 'includes/fonctions.php';
require 'includes/commerce.php';

$user = exiger_role('GESTIONNAIRE');
$commerce = mon_commerce($user);
traiter_retrait($commerce);

$erreur = '';
$trouvee = null;

if (isset($_POST['code'])) {
    $code = strtoupper(trim($_POST['code']));
    foreach (charger('reservations') as $r) {
        if ($r['code'] == $code) {
            $trouvee = $r;
        }
    }
    if (!$trouvee) {
        $erreur = 'Code inconnu.';
    } else {
        $o = trouver(charger('offres'), $trouvee['offreId']);
        if ($o['commerceId'] != $commerce['id']) {
            $erreur = 'Cette réservation concerne un autre commerce.';
        } elseif ($trouvee['statut'] == 'RETIREE') {
            $erreur = 'Ce code a déjà été utilisé.';
        } elseif ($trouvee['statut'] != 'CONFIRMEE') {
            $erreur = 'Cette réservation est annulée ou expirée.';
        }
        if ($erreur) {
            $trouvee = null;
        }
    }
}

list($mes_offres, $resas_du_jour) = donnees_commerce($commerce);

$titre_page = 'Retraits du jour';
$menu = menu_commercant();
include 'includes/header.php';
?>
<div class="espace">
    <?php include 'includes/sidebar.php'; ?>
    <main class="contenu">
        <div class="contenu-titre"><h1>Retraits du jour</h1></div>

        <section class="panneau" style="margin-bottom: 16px">
            <h2>Valider un retrait</h2>
            <p class="aide" style="margin-bottom: 10px">Saisissez le code de retrait du client (il est écrit sous son QR code).</p>
            <form class="code-saisie" method="post">
                <label for="code" class="sr-only">Code de retrait</label>
                <input id="code" name="code" class="input" placeholder="FS-XXXX" required>
                <button class="btn btn-plein">Vérifier</button>
            </form>
            <?php if ($erreur) { ?><p class="msg-erreur" style="margin-top: 8px"><?= e($erreur) ?></p><?php } ?>
        </section>

        <section class="panneau">
            <h2>Réservations d'aujourd'hui</h2>
            <?php tableau_reservations($resas_du_jour); ?>
        </section>

        <?php if ($trouvee) {
            // le code est bon : on ouvre directement la fenêtre de confirmation
            // (elle existe déjà si la réservation est dans le tableau du jour)
            $deja_affichee = false;
            foreach ($resas_du_jour as $r) {
                if ($r['id'] == $trouvee['id']) $deja_affichee = true;
            }
            if (!$deja_affichee) {
                modale_retrait($trouvee, trouver(charger('offres'), $trouvee['offreId']));
            } ?>
            <script>document.getElementById('retrait-<?= $trouvee['id'] ?>').classList.add('ouverte');</script>
        <?php } ?>
    </main>
</div>
<?php include 'includes/footer.php'; ?>
