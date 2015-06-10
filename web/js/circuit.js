perso_src = "/images/imghomme.png";
perso_src_init = "/images/imghomme.png";
favoris_src = "/images/bookmark.png";
fleche_src = "/images/flechecircuit.png";
delete_src = "/images/delete.png";
favoris_url = "";
del_favoris_url = "";

// Pour l affichage des boutons valider et signer
var valid_sign = 0;

$('#users_list p:first, #circuits_list p:first').addClass("list_selected_element");

$('#users_list p:selected').addClass("list_selected_element");

if (typeof circuit_users != 'undefined') {
    user_a_charger = circuit_users.split(",");
    if (user_a_charger.length > 0) {
        var sort = false;

        $.each(user_a_charger, function (k, v) {
            //console.log("Ordre : " + ordre_circuit + " - Variable k : " + k);
            if (typeof validant != 'undefined') { // && validant > 0 ????
                //if (v == validant && k > ordre_circuit) {
                if (k > ordre_circuit) {
                    //console.log("Variable k : " + k + " > Ordre : " + ordre_circuit);
                    ajoutUser(v, k);
                    sort = true;
                }
                else {
                    ajoutUser(v, k);
                }
            }
        });
    }
}

if (typeof deposant != 'undefined') {
    var new_perso = $('<div/>').addClass('deposant no_sort perso_circuit').insertBefore("#debut_circuit");
    perso_src = deposant.path ? path+deposant.path : perso_src;
    $('<img />').attr("src", perso_src).appendTo(new_perso);
    $('<span class="nom_perso" />').text(deposant.nom).appendTo(new_perso);
}
/*if ( typeof validant != 'undefined') {
    var new_perso = $('<div/>').addClass('no_sort perso_circuit curr_user').appendTo("#circuit");
    perso_src = validant.path ? path+validant.path : perso_src;
    $("<span/>").addClass("valid_perso glyphicon glyphicon-pencil").appendTo(new_perso);
    $('<img />').attr("src", perso_src).appendTo(new_perso);
    $('<span class="nom_perso" />').text(validant.nom).appendTo(new_perso);
    //$("<span/>").addClass("fleche_circuit glyphicon glyphicon-arrow-right").insertAfter(".perso_circuit:not(.deposant)");
}*/
// Fonction pour afficher les bouton valider et signer
function aff_button_valider (valid_sign) {
    if(valid_sign == 0) {
        $(".btn-valider-signer").css('display', 'inline-block');
        $(".btn-valider-non-signer").css('display', 'none');
        //console.log("valider ok : " + valid_sign);
    } else {
        $(".btn-valider-signer").css('display', 'none');
        $(".btn-valider-non-signer").css('display', 'inline-block');
        //console.log("valider : " + valid_sign);
    }
}

/* Ajoute un utilisateur dans le cadre "circuits" */
function ajoutUser(id, k) {

    var sel_user = $('#users_list p[data-id="' + id + '"]');
    if (sel_user.length == 0) {
        return false;
    }
    var new_perso = $('<div/>').data('id', id).addClass('perso_circuit').appendTo("#circuit");

    //if (typeof validant != 'undefined' && validant == id) { // pourquoi j'avais mis validant > 0 ????? ça merde plus mais à vérifier si y'avait pas une raison
    //    new_perso.addClass("curr_user");
    //    $("<span/>").addClass("valid_perso glyphicon glyphicon-pencil").appendTo(new_perso);
    //}

    //if (!sort || k < ordre_circuit) {
    //    console.log("L2 Variable k : " + k + " < Ordre : " + ordre_circuit);
    //    new_perso.addClass("no_sort");
    //    if (typeof validant != 'undefined' && validant != id) {
    //        $("<span/>").addClass("ok_perso glyphicon glyphicon-ok").appendTo(new_perso);
    //    }
    //}

    // Ajout du glyphicon validé
    if (typeof validant != 'undefined' && k < ordre_circuit) {
        //console.log("L2 Variable k : " + k + " < Ordre : " + ordre_circuit);
        new_perso.addClass("no_sort");
        $("<span/>").addClass("ok_perso glyphicon glyphicon-ok").appendTo(new_perso);
    }
    // Ajout du glyphicon mofication
    else if (typeof validant != 'undefined' && k == ordre_circuit && status != 0) {
        new_perso.addClass("curr_user");
        new_perso.addClass("no_sort");
        $("<span/>").addClass("valid_perso glyphicon glyphicon-pencil").appendTo(new_perso);
    }
    // Ajout du glyphicon suppresion
    // Suppression d un utilisateur dans le circuit
    else {

        $("<span/>").addClass("suppr_perso glyphicon glyphicon-remove").appendTo(new_perso).click(function () {

            // Cette ligne rajoute l'utilisateur dans la liste des utilisateurs quand il est supprimé du circuit
            //$("<p/>").attr("data-id", id).text(sel_user.text()).appendTo("#users_list");

            new_perso.remove();
            creerFleches();

            $('#users_list p').click(function () {
                $(this).addClass("list_selected_element");
                var sel_user = $('#users_list .list_selected_element');
                //ajoutUser(sel_user.attr("data-id"), true);
                creerFleches();
            });
            // pour les boutons valider et signer
            valid_sign = valid_sign -1;
            aff_button_valider (valid_sign)
        });
        // pour les boutons valider et signer
        this.valid_sign ++;
        aff_button_valider(valid_sign);
    }

    perso_src = sel_user.data("img") ? path + sel_user.data("img") : perso_src_init;

    $('<img />').attr("src", perso_src).appendTo(new_perso);
    $('<span class="nom_perso" />').text(sel_user.text()).appendTo(new_perso);

    // Cela permet de suppirmer l utilisateur selectionné de #users_list
    //sel_user.remove();

    if ($(".perso_circuit").length > 1) {
        creerFleches();
    }

    // Pour ajuster la hauteur du bloc #circuit on rajoute une div.clear a la fin du bloc
    $('.clear').remove();
    $('#circuit').append('<div class="clear"></div>');
}

function creerFleches() {
    $(".fleche_circuit").remove();
    $("<span/>").addClass("fleche_circuit glyphicon glyphicon-arrow-right").insertAfter(".perso_circuit:not(:last, .deposant)");
}



$("#useradd_btn").click(function () {
    var sel_user = $('#users_list .list_selected_element');
    if (sel_user.length > 0) {
        ajoutUser(sel_user.attr("data-id"));
    }
    $('#users_list p:first').addClass("list_selected_element");
});


$("#circuit").sortable({
    items: ".perso_circuit:not(.fleche_circuit, .no_sort)",
    placeholder: 'emplacement',
    tolerance: "pointer",
    stop: function (e, ui) {
        creerFleches();
    },
    over: function (e, ui) {
        sortableIn = 1;
    },
    out: function (e, ui) {
        sortableIn = 0;
    },
    start: function (e, ui) {
        $(".fleche_circuit").remove();
    },
    beforeStop: function (event, ui) {
        if (sortableIn == 0) {
            elem = ui.item;
            $("<p/>").attr("data-id", elem.data("id")).text(elem.find(".nom_perso").text()).appendTo("#users_list");
            elem.remove();
            $('#users_list p:first').addClass("list_selected_element");
        }
    }
}).disableSelection();





/* Fonction qui permet de cliquer et d'afficher les users */
// Sur le click du bouton
$("#circuitadd_btn").click(function () {
    var sel_circuit = $('#circuits_list .list_selected_element');
    ajoutGroupe(sel_circuit);
    //var ordre = sel_circuit.attr("data-ordre").split(",");
    //if (ordre.length > 0) {
    //    $.each($("#circuit .perso_circuit"), function (k, v) {
    //        var elem = $(this);
    //        $("<p/>").attr("data-id", elem.data("id")).text(elem.find(".nom_perso").text()).appendTo("#users_list");
    //        elem.remove();
    //    });
    //    creerFleches();
    //
    //    $.each(ordre, function (k, v) {
    //        ajoutUser(v, true);
    //    });
    //}
    //$('#users_list p:first').addClass("list_selected_element");
    //$("#circuit_name").val(sel_circuit.text());
    //$("#circuit_modifier, #btn-group-supp").css("display", "inline-block");
});

//// Sur le select du groupe
$("#userGroupe").val(function (i, val) {
    var sel_circuit = $('#userGroupe option:selected');
    ajoutGroupe(sel_circuit);
    return val;
});

function ajoutGroupe(sel_circuit) {
    ordre = sel_circuit.attr("data-ordre").split(",");
    if (ordre.length > 0) {
        $.each($("#circuit .perso_circuit"), function (k, v) {
            var elem = $(this);
            //$("<p/>").attr("data-id", elem.data("id")).text(elem.find(".nom_perso").text()).appendTo("#users_list");
            elem.remove();
        });
        creerFleches();

        $.each(ordre, function (k, v) {
            ajoutUser(v);
        });
    }
    $('#users_list p:first').addClass("list_selected_element");
    $("#circuit_name").val(sel_circuit.text());
    $("#circuit_modifier, #btn-group-supp").css("display", "inline-block");
}


$("#users_list p").click(function () {
    $('#users_list p:selected').addClass("list_selected_element");
    var sel_user = $('#users_list .list_selected_element');
    if (sel_user.length > 0) {
        //console.log(sel_user.attr("data-id"));
        ajoutUser(sel_user.attr("data-id"));
    }
});




// si on est dans un form on lui passe le circuit qd on walid
var form_parent = $("#circuit").parents("form");

if (form_parent.length > 0) {
    form_parent.submit(function (e) {
        e.preventDefault();
        var ordre_circuit = new Array();
        $("#circuit .perso_circuit:not(.deposant)").each(function (k, v) {
            ordre_circuit.push($(this).data("id"));
        });

        if(ordre_circuit.length == 0 && status != 0) {
            alert("Le circuit ne peut pas être vide");
            return false;
        }

        $('<input />')
            .attr({ 'type': 'hidden', 'name': "circuit" })
            .val(ordre_circuit)
            .appendTo(form_parent);

        if(typeof(dropzone) != 'undefined') {
            if( ( typeof(mockFile) != 'object' && dropzone.getAcceptedFiles().length == 0) || ( typeof(mockFile) == 'object' && mockFile == {} ) ) {
                alert("Vous devez au moins déposer un document dans le classeur");
                return false;
            }
        }

        this.submit();
        return false;
    });
}


$(document).ready(function () {
    // pour la recherche dans les utilisateurs
    $("#circuit-search").keyup(function () {
        var users = $(".users-search");

        users.each(function () {
            var search = $("#circuit-search").val().toLowerCase();
            if ($(this).html().toLowerCase().indexOf(search) < 0) {
                $(this).css('display', 'none');
            } else {
                $(this).css('display', 'block');
            }
//                            console.log($(this).html().toLowerCase().indexOf(search));
        });
    });
    // FIN de la recherche dans les utilisateurs
    aff_button_valider (valid_sign);
});