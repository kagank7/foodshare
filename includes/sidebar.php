<?php
// Menu latéral des espaces connectés
// la page définit $menu (liste de [lien, texte]) avant d'inclure ce fichier
$page_actuelle = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <?php foreach ($menu as $lien) {
        $classe = ($lien[0] == $page_actuelle) ? 'active' : '';
        ?>
        <a href="<?= $lien[0] ?>" class="<?= $classe ?>">
            <?= $lien[1] ?>
            <?php if (!empty($lien[2])) { ?><span class="compteur"><?= $lien[2] ?></span><?php } ?>
        </a>
    <?php } ?>
</aside>
