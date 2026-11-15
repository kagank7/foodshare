<?php
require 'includes/fonctions.php';
$titre_page = 'Comment ça marche';
include 'includes/header.php';
?>
<main class="bloc-centre">
    <h1>Comment ça marche ?</h1>
    <p class="intro">Sauver un panier prend moins d'une minute. Le paiement se fait sur place, au moment du retrait.</p>
    <div class="etapes">
        <div class="etape">
            <div class="num">1</div>
            <h3>Trouvez</h3>
            <p>Cherchez les paniers disponibles autour de vous, en liste ou sur la carte, et filtrez par catégorie ou prix.</p>
        </div>
        <div class="etape">
            <div class="num">2</div>
            <h3>Réservez</h3>
            <p>Choisissez la quantité et le créneau. Vous recevez un code de retrait et un QR code.</p>
        </div>
        <div class="etape">
            <div class="num">3</div>
            <h3>Retirez</h3>
            <p>Présentez votre QR code au commerçant pendant le créneau, payez sur place et savourez !</p>
        </div>
    </div>
    <div class="faq">
        <details>
            <summary>Puis-je annuler une réservation ?</summary>
            <p>Oui, gratuitement jusqu'à 1 heure avant le début du créneau. Le panier est alors remis en vente.</p>
        </details>
        <details>
            <summary>Combien de paniers puis-je réserver ?</summary>
            <p>Jusqu'à 3 paniers par offre, pour que tout le monde puisse en profiter.</p>
        </details>
        <details>
            <summary>Que se passe-t-il si je ne viens pas ?</summary>
            <p>Après 3 réservations non retirées en 30 jours, le compte est suspendu pendant 14 jours.</p>
        </details>
    </div>
    <p style="margin-top: 24px"><a href="offres.php" class="btn btn-plein">Voir les offres près de moi</a></p>
</main>
<?php include 'includes/footer.php'; ?>
