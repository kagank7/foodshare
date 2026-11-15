<?php
// Fonctions utilisées sur toutes les pages du site FoodShare
// (pas de vraie base de données : on lit et on écrit des fichiers JSON dans le dossier data/)

session_start();

// Heure "simulée" pour la démo : on fait comme s'il était 14h00
// sinon les règles sur les créneaux changent selon l'heure où on teste
define('HEURE_DEMO', '14:00');
define('DOSSIER_DATA', __DIR__ . '/../data/');

// ---------- Lecture / écriture des données ----------

function charger($nom)
{
    $fichier = DOSSIER_DATA . $nom . '.json';
    if (!file_exists($fichier)) {
        // premier lancement : on copie les données de départ
        copy(DOSSIER_DATA . 'init/' . $nom . '.json', $fichier);
    }
    return json_decode(file_get_contents($fichier), true);
}

function sauver($nom, $donnees)
{
    file_put_contents(DOSSIER_DATA . $nom . '.json', json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function reinitialiser_donnees()
{
    foreach (glob(DOSSIER_DATA . 'init/*.json') as $f) {
        copy($f, DOSSIER_DATA . basename($f));
    }
}

// cherche un élément par son id dans un tableau
function trouver($liste, $id)
{
    foreach ($liste as $element) {
        if ($element['id'] == $id) {
            return $element;
        }
    }
    return null;
}

function trouver_index($liste, $id)
{
    foreach ($liste as $i => $element) {
        if ($element['id'] == $id) {
            return $i;
        }
    }
    return -1;
}

// ---------- Affichage ----------

// protège contre les failles XSS
function e($texte)
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES, 'UTF-8');
}

function prix($n)
{
    return number_format($n, 2, ',', ' ') . ' €';
}

// prix barré : "10 €" ou "10,50 €"
function prix_court($n)
{
    if ($n == floor($n)) {
        return $n . ' €';
    }
    return number_format($n, 2, ',', ' ') . ' €';
}

function distance($metres)
{
    if ($metres < 1000) {
        return $metres . ' m';
    }
    return number_format($metres / 1000, 1, ',', '') . ' km';
}

function note($n)
{
    return number_format($n, 1, ',', '');
}

function nombre($n)
{
    return number_format($n, 0, ',', ' ');
}

function minutes($heure)
{
    $morceaux = explode(':', $heure);
    return $morceaux[0] * 60 + $morceaux[1];
}

// RG-04 : une réservation est annulable jusqu'à 1 h avant le début du créneau
function est_annulable($debut)
{
    return minutes(HEURE_DEMO) <= minutes($debut) - 60;
}

function generer_code()
{
    $lettres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = 'FS-';
    for ($i = 0; $i < 4; $i++) {
        $code .= $lettres[rand(0, strlen($lettres) - 1)];
    }
    return $code;
}

// ---------- Statuts (texte + couleur, jamais la couleur seule) ----------

function statut($s)
{
    $libelles = [
        'CONFIRMEE' => ['À retirer', ''],
        'RETIREE' => ['Retiré', 'pastille-verte'],
        'ANNULEE' => ['Annulée', 'pastille-grise'],
        'EXPIREE' => ['Expirée', 'pastille-rose'],
        'BROUILLON' => ['Brouillon', 'pastille-grise'],
        'PUBLIEE' => ['Publiée', 'pastille-verte'],
        'EPUISEE' => ['Épuisée', ''],
        'CLOTUREE' => ['Clôturée', 'pastille-grise'],
        'EN_ATTENTE' => ['En attente', ''],
        'VALIDE' => ['Validé', 'pastille-verte'],
        'REFUSE' => ['Refusé', 'pastille-rose'],
        'ACTIF' => ['Actif', 'pastille-verte'],
        'SUSPENDU' => ['Suspendu', 'pastille-rose'],
    ];
    $l = $libelles[$s] ?? [$s, ''];
    return '<span class="pastille ' . $l[1] . '">' . $l[0] . '</span>';
}

// ---------- Messages "flash" (affichés une seule fois après une action) ----------

function flash($message)
{
    $_SESSION['flash'] = $message;
}

function afficher_flash()
{
    if (!empty($_SESSION['flash'])) {
        echo '<div class="toast" role="status">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function rediriger($url)
{
    header('Location: ' . $url);
    exit;
}

// ---------- Utilisateur connecté ----------

function utilisateur()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return trouver(charger('utilisateurs'), $_SESSION['user_id']);
}

// page réservée à un rôle : sinon on renvoie vers la connexion
function exiger_role($role)
{
    $u = utilisateur();
    if (!$u) {
        rediriger('connexion.php?onglet=connexion&retour=' . urlencode(basename($_SERVER['REQUEST_URI'])));
    }
    if ($u['role'] != $role) {
        rediriger('index.php');
    }
    return $u;
}

function page_espace($role)
{
    if ($role == 'ADMIN') {
        return 'admin.php';
    }
    if ($role == 'GESTIONNAIRE') {
        return 'commerce.php';
    }
    return 'compte.php';
}

// le commerce géré par l'utilisateur connecté
function mon_commerce($user)
{
    foreach (charger('commerces') as $c) {
        if ($c['gestionnaireId'] == $user['id']) {
            return $c;
        }
    }
    return null;
}

function notifier($userId, $titre, $texte)
{
    $notifs = charger('notifications');
    array_unshift($notifs, [
        'id' => 'n' . time() . rand(100, 999),
        'userId' => $userId,
        'titre' => $titre,
        'texte' => $texte,
        'date' => "Aujourd'hui · " . HEURE_DEMO,
        'lu' => false,
    ]);
    sauver('notifications', $notifs);
}

require_once __DIR__ . '/menus.php';
