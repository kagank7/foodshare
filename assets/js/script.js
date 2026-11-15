// Petits scripts de l'interface FoodShare
// (le reste est géré en PHP avec les formulaires)

// ----- Menu burger (mobile) -----
var burger = document.getElementById('burger');
if (burger) {
    burger.addEventListener('click', function () {
        document.getElementById('nav').classList.toggle('ouvert');
    });
}

// ----- Menu du compte (clic sur l'avatar) -----
var boutonAvatar = document.getElementById('bouton-avatar');
if (boutonAvatar) {
    boutonAvatar.addEventListener('click', function () {
        document.getElementById('menu-user').classList.toggle('ouvert');
    });
}

// ----- Fenêtres de confirmation -----
// un bouton avec data-modale="id" ouvre la fenêtre qui a cet id
var boutonsModale = document.querySelectorAll('[data-modale]');
for (var i = 0; i < boutonsModale.length; i++) {
    boutonsModale[i].addEventListener('click', function () {
        document.getElementById(this.dataset.modale).classList.add('ouverte');
    });
}

function fermerModales() {
    var modales = document.querySelectorAll('.fond-modale');
    for (var i = 0; i < modales.length; i++) {
        modales[i].classList.remove('ouverte');
    }
}

var boutonsFermer = document.querySelectorAll('.fermer-modale');
for (var i = 0; i < boutonsFermer.length; i++) {
    boutonsFermer[i].addEventListener('click', fermerModales);
}

// clic sur le fond gris = fermer
var fonds = document.querySelectorAll('.fond-modale');
for (var i = 0; i < fonds.length; i++) {
    fonds[i].addEventListener('click', function (event) {
        if (event.target == this) {
            fermerModales();
        }
    });
}

document.addEventListener('keydown', function (event) {
    if (event.key == 'Escape') {
        fermerModales();
    }
});

// ----- Inscription : choix Consommateur / Commerçant -----
var boutonsRole = document.querySelectorAll('.choix-role button');
for (var i = 0; i < boutonsRole.length; i++) {
    boutonsRole[i].addEventListener('click', function () {
        for (var j = 0; j < boutonsRole.length; j++) {
            boutonsRole[j].classList.remove('actif');
        }
        this.classList.add('actif');
        document.getElementById('role').value = this.dataset.role;

        // on affiche les champs du commerce seulement pour un commerçant
        var champsCommerce = document.getElementById('champs-commerce');
        var champs = champsCommerce.querySelectorAll('input');
        if (this.dataset.role == 'GESTIONNAIRE') {
            champsCommerce.style.display = 'block';
            for (var k = 0; k < champs.length; k++) { if (champs[k].type != 'file') champs[k].required = true; }
        } else {
            champsCommerce.style.display = 'none';
            for (var k = 0; k < champs.length; k++) { champs[k].required = false; }
        }
    });
}

// ----- Vérification des champs en direct -----
// le message d'erreur est dans l'attribut data-erreur du champ
var champsAVerifier = document.querySelectorAll('[data-erreur]');
for (var i = 0; i < champsAVerifier.length; i++) {
    champsAVerifier[i].addEventListener('blur', function () {
        verifierChamp(this);
    });
}

function verifierChamp(champ) {
    var message = champ.parentElement.querySelector('.msg-erreur');
    var ok = champ.checkValidity();

    // cas particulier : confirmation du mot de passe
    if (champ.id == 'mdp2' && champ.value != document.getElementById('mdp').value) {
        ok = false;
    }

    if (ok) {
        champ.classList.remove('erreur');
        if (message) message.remove();
    } else {
        champ.classList.add('erreur');
        if (!message) {
            message = document.createElement('span');
            message.className = 'msg-erreur';
            champ.parentElement.appendChild(message);
        }
        message.textContent = champ.dataset.erreur;
    }
    return ok;
}

// ----- Page détail : quantité et total -----
var moins = document.getElementById('moins');
var plus = document.getElementById('plus');
if (moins && plus) {
    var affichage = document.getElementById('quantite-affichee');
    var champQuantite = document.getElementById('quantite');
    var total = document.getElementById('total');
    var prixUnitaire = parseFloat(total.dataset.prix);
    var max = parseInt(champQuantite.dataset.max);

    function majQuantite(q) {
        if (q < 1) q = 1;
        if (q > max) q = max;
        champQuantite.value = q;
        affichage.textContent = q;
        total.textContent = (prixUnitaire * q).toFixed(2).replace('.', ',') + ' €';
    }

    moins.addEventListener('click', function () {
        majQuantite(parseInt(champQuantite.value) - 1);
    });
    plus.addEventListener('click', function () {
        majQuantite(parseInt(champQuantite.value) + 1);
    });
}

// ----- Recherche : survol d'une offre = épingle en orange sur la carte -----
var lignesOffre = document.querySelectorAll('.offre-ligne');
for (var i = 0; i < lignesOffre.length; i++) {
    lignesOffre[i].addEventListener('mouseenter', function () {
        var epingles = document.querySelectorAll('.epingle');
        for (var j = 0; j < epingles.length; j++) {
            epingles[j].classList.remove('active');
        }
        document.getElementById('pin-' + this.dataset.id).classList.add('active');
    });
}

// ----- Publier une offre : calcul de la remise en direct (RG-02) -----
var prixInitial = document.getElementById('pi');
var prixReduit = document.getElementById('pr');
if (prixInitial && prixReduit) {
    function majRemise() {
        var a = parseFloat(prixInitial.value.replace(',', '.'));
        var b = parseFloat(prixReduit.value.replace(',', '.'));
        var info = document.getElementById('info-remise');
        if (a > 0 && b > 0) {
            if (b > a / 2) {
                info.className = 'msg-erreur';
                info.textContent = 'Le prix réduit doit être ≤ 50 % du prix initial (max ' + (a / 2).toFixed(2).replace('.', ',') + ' €).';
            } else {
                info.className = 'msg-ok';
                info.textContent = 'Remise de ' + Math.round((1 - b / a) * 100) + ' %';
            }
        } else {
            info.textContent = '';
        }
    }
    prixInitial.addEventListener('input', majRemise);
    prixReduit.addEventListener('input', majRemise);
}

// ----- Aperçu de la photo choisie -----
var champPhoto = document.getElementById('photo');
if (champPhoto) {
    champPhoto.addEventListener('change', function () {
        if (this.files[0]) {
            var apercu = document.getElementById('apercu');
            apercu.src = URL.createObjectURL(this.files[0]);
            apercu.style.display = 'block';
        }
    });
}
