# FoodShare

Application web anti-gaspillage alimentaire : les commerçants publient leurs invendus du jour
à prix réduit, les consommateurs les réservent et viennent les chercher avec un QR code.

Projet de conception d'application web — Bachelor 2 informatique, ECE Paris
Équipe : Oussama, Kagan, Yasser, Samy

## Technologies

- HTML / CSS
- PHP 8 (pages, formulaires, sessions)
- JavaScript (petites interactions : menu mobile, fenêtres de confirmation, quantité, vérification des champs)
- [phpqrcode](http://phpqrcode.sourceforge.net/) pour générer les QR codes (dossier `lib/`)

Il n'y a pas de base de données : les données sont stockées dans des fichiers JSON
(dossier `data/`), lus et modifiés par PHP.

## Installation

Il faut PHP 8 avec l'extension **GD** (activée par défaut dans XAMPP / WAMP / MAMP).

**Avec XAMPP / WAMP :** copier le dossier dans `htdocs` (ou `www`) puis ouvrir
http://localhost/foodshare/

**Ou en ligne de commande**, depuis le dossier du projet :

```
php -S localhost:8000
```

puis ouvrir http://localhost:8000

Au premier lancement, les fichiers de `data/init/` sont copiés dans `data/`.
Le dossier `data/` et `assets/img/uploads/` doivent être accessibles en écriture.

## Comptes de démonstration

Mot de passe pour tous les comptes : `demo1234`

| Rôle | E-mail |
|------|--------|
| Consommateur | camille.martin@email.fr |
| Commerçant (Boulangerie Dupain) | contact@dupain.fr |
| Administrateur | admin@foodshare.fr |

Pour remettre les données de départ : se connecter en admin → Paramètres →
« Réinitialiser les données de démo ».

L'heure est simulée à 14h00 (constante `HEURE_DEMO` dans `includes/fonctions.php`)
pour que les règles sur les créneaux de retrait donnent toujours le même résultat pendant la démo.

## Organisation des fichiers

```
index.php                 accueil (maquette 1)
connexion.php             inscription / connexion (maquette 2)
compte.php                tableau de bord consommateur (maquette 3)
offres.php                recherche, liste + carte (maquette 4)
offre.php                 détail d'une offre et réservation (maquette 5)
commerce.php              tableau de bord commerçant (maquette 6)
admin.php                 tableau de bord administrateur (maquette 7)
reservations.php, favoris.php, notifications.php, profil.php           espace consommateur
mes-offres.php, publier.php, retraits.php, statistiques.php, profil-commerce.php   espace commerçant
admin-commerces.php, admin-utilisateurs.php, admin-avis.php, admin-statistiques.php, admin-parametres.php   espace admin
comment-ca-marche.php, commercants.php   pages d'information
qrcode.php                génère l'image du QR code d'une réservation
deconnexion.php

includes/   fonctions communes, en-tête, pied de page, menus
data/       fichiers JSON (data/init = données de départ)
assets/     css, js, images, polices
lib/        librairie phpqrcode
```
