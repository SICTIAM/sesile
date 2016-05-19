/**
 * Created by f.laussinot on 18/05/2016.
 */

$(document).ready(function() {
    // Personnalisation de l input file
    $(":file").filestyle({/*input: false, */buttonBefore: true, size: "sm", buttonText: "&nbsp;Télécharger une image", badge: false});


    // Fonction permettant l ajout et la suppression de role multiple
    // On récupère la balise <div> en question qui contient l'attribut « data-prototype » qui nous intéresse.
    var $container = $('div#sesile_userbundle_user_userRole');

    // On recupere l enfant du container pour lui attribuer une classe et faire un affichage perso
    var $containerChild = $('div#sesile_userbundle_user_userRole > div');
    $containerChild.addClass('row one-role-user');

    // On ajoute un lien pour ajouter une nouvelle catégorie
    var $addLink = $('<div class="row"><div class="col-md-2 col-md-push-10 text-center"> <a href="#" id="add_category" class="btn btn-circle-md btn-info first"><span class="glyphicon glyphicon-plus"></span></a> </div></div> <div class="row">&nbsp;</div>');

//            $container.prepend($addLink);
    $container.append($addLink);


    // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.
    $addLink.click(function(e) {
        addCategory($container);
        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
        return false;
    });

    // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
    var index = $container.find(':input').length / 2;
    //console.log('inex : ' + index);

    // On ajoute un premier champ automatiquement s'il n'en existe pas déjà un (cas d'une nouvelle annonce par exemple).
    if (index == 0) {
        addCategory($container);
    } else {
        // Pour chaque catégorie déjà existante, on ajoute un lien de suppression
//                $container.children('div:not(.row)').each(function() {
        $container.children('div.one-role-user').each(function() {
            addDeleteLink($(this));
        });
    }
//            data-prototype='<div><label class="required">__name__label__</label><div id="sesile_userbundle_user_userRole___name__" class=""><div><label class="col-md-5 col-sm-5 col-xs-5 required" for="sesile_userbundle_user_userRole___name___userRoles">Rôle utilisateur</label><input type="text" id="sesile_userbundle_user_userRole___name___userRoles" name="sesile_userbundle_user[userRole][__name__][userRoles]" required="required" class="col-md-5 col-sm-5 col-xs-5" /></div><div><label for="sesile_userbundle_user_userRole___name___user">User</label><select id="sesile_userbundle_user_userRole___name___user" name="sesile_userbundle_user[userRole][__name__][user]"><option value=""></option><option value="38">f.laussinot@sictiam.fr</option><option value="39">f.boucher@sictiam.fr</option><option value="40">j.mercier@sictiam.fr</option><option value="41">flaussinot@free.fr</option><option value="42">a.cauvin@sictiam.fr</option><option value="43">b.colinet@sictiam.fr</option><option value="44">as.leveque@sictiam.fr</option><option value="46">as.leveque@sictiam.com</option><option value="47">fltest@sictiam.fr</option><option value="48">fltest2@sictiam.fr</option><option value="58">j.leroyer@sictiam.fr</option></select></div></div></div>'
    // La fonction qui ajoute un formulaire Categorie
    function addCategory($container) {
        // Dans le contenu de l'attribut « data-prototype », on remplace :
        // - le texte "__name__label__" qu'il contient par le label du champ
        // - le texte "__name__" qu'il contient par le numéro du champ
        var $prototype = $($container.attr('data-prototype')
                .replace(/__name__label__/g, 'Rôle n°' + (index+1))
                .replace(/__name__/g, index)
                .replace(/<div><label class="required">/g, '<div class="row one-role-user"><label class="required">')
        );

        // On ajoute au prototype un lien pour pouvoir supprimer la catégorie
        addDeleteLink($prototype);

        // On ajoute le prototype modifié à la fin de la balise <div>
        $container.append($prototype);

        // On modifie la valeur du select pour correspondre a l utilisateur courant
        $("select#sesile_userbundle_user_userRole_" + index + "_user").val("{{ entity.id }}");
        //$("select#sesile_userbundle_user_userRole_" + index + "_user").hide();

        // On ajoute le bouton d ajout de formulaire en fin
        $container.append($addLink);

        // Enfin, on incrémente le compteur pour que le prochain ajout se fasse avec un autre numéro
        index++;
    }

    // La fonction qui ajoute un lien de suppression d'un role
    function addDeleteLink($prototype) {
        // Création du lien
        $deleteLink = $('<div class="col-md-2 col-sm-2 col-xs-2"><a href="#" class="btn btn-circle-md btn-danger btn-supRoles"><span class="glyphicon glyphicon-remove"></span></a></div>');

        // Ajout du lien
        $prototype.append($deleteLink);

        // Ajout du listener sur le clic du lien
        $deleteLink.click(function(e) {
            $prototype.remove();
            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            return false;
        });
    }


});