<?php
// Les menus latéraux de chaque espace

function menu_consommateur($user)
{
    $non_lues = 0;
    foreach (charger('notifications') as $n) {
        if ($n['userId'] == $user['id'] && !$n['lu']) {
            $non_lues++;
        }
    }
    return [
        ['compte.php', 'Tableau de bord'],
        ['offres.php', 'Rechercher des offres'],
        ['reservations.php', 'Mes réservations'],
        ['favoris.php', 'Mes favoris'],
        ['notifications.php', 'Notifications', $non_lues],
        ['profil.php', 'Mon profil'],
    ];
}

function menu_commercant()
{
    return [
        ['commerce.php', 'Tableau de bord'],
        ['mes-offres.php', 'Mes offres'],
        ['publier.php', 'Publier une offre'],
        ['retraits.php', 'Retraits du jour'],
        ['statistiques.php', 'Statistiques'],
        ['profil-commerce.php', 'Profil du commerce'],
    ];
}

function menu_admin()
{
    return [
        ['admin.php', "Vue d'ensemble"],
        ['admin-commerces.php', 'Commerces à valider'],
        ['admin-utilisateurs.php', 'Utilisateurs'],
        ['admin-avis.php', 'Avis et signalements'],
        ['admin-statistiques.php', 'Statistiques globales'],
        ['admin-parametres.php', 'Paramètres'],
    ];
}
