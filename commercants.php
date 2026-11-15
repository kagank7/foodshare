<?php
require 'includes/fonctions.php';
$titre_page = 'Commerçants';
include 'includes/header.php';
?>
<main class="bloc-centre">
    <h1>Vous êtes commerçant ?</h1>
    <p class="intro">
        Boulangerie, restaurant, primeur ou supermarché : publiez vos invendus en 2 minutes,
        récupérez une partie de votre marge et faites venir de nouveaux clients.
    </p>
    <div class="etapes">
        <div class="etape">
            <div class="num">1</div>
            <h3>Inscrivez votre commerce</h3>
            <p>Nom, SIRET, adresse et justificatif. L'équipe FoodShare valide votre compte sous 48 h.</p>
        </div>
        <div class="etape">
            <div class="num">2</div>
            <h3>Publiez vos paniers</h3>
            <p>Prix réduit d'au moins 50 %, quantité et créneau de retrait. Programmez des offres récurrentes.</p>
        </div>
        <div class="etape">
            <div class="num">3</div>
            <h3>Validez les retraits</h3>
            <p>Scannez le QR code du client ou saisissez son code. Suivez vos statistiques dans le tableau de bord.</p>
        </div>
    </div>
    <p style="margin-top: 24px"><a href="connexion.php" class="btn btn-orange">Inscrire mon commerce</a></p>
</main>
<?php include 'includes/footer.php'; ?>
