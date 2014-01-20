perso_src = "/images/imghomme.png";
favoris_src = "/images/bookmark.png";
fleche_src = "/images/flechecircuit.png";
delete_src = "/images/delete.png";
favoris_url = "";
del_favoris_url = "";


$('#users_list p:first, #circuits_list p:first').addClass("list_selected_element");


if (typeof circuit_users != 'undefined') {
    user_a_charger = circuit_users.split(",");
    if (user_a_charger.length > 0) {
        var sort = false;
        $.each(user_a_charger, function (k, v) {
            if (typeof validant != 'undefined' && validant > 0 ) {
                if(v == validant) {
                    ajoutUser(v, sort);
                    sort = true;
                }
                else {
                    ajoutUser(v, sort);
                }
            }
        });
    }
}

if (typeof deposant != 'undefined') {
    var new_perso = $('<div/>').addClass('deposant no_sort perso_circuit').insertBefore("#debut_circuit");
    perso_src = deposant.path || perso_src;
    $('<img />').attr("src", perso_src).appendTo(new_perso);
    $('<span class="nom_perso" />').text(deposant.nom).appendTo(new_perso);
}

/*
 Ajoute un utilisateur dans le cadre "circuits"
 */
function ajoutUser(id, sort) {
    var sel_user = $('#users_list p[data-id="' + id + '"]');
    if (sel_user.length == 0) {
        return false;
    }
    var new_perso = $('<div/>').data('id', id).addClass('perso_circuit').appendTo("#circuit");

    if(!sort) {
        new_perso.addClass("no_sort");
    }

    if (typeof validant != 'undefined' && validant > 0 && validant == id) {
        new_perso.addClass("curr_user");
    }

    $("<span/>").addClass("suppr_perso glyphicon glyphicon-remove").appendTo(new_perso).click(function () {
        $("<p/>").attr("data-id", id).text(sel_user.text()).appendTo("#users_list");
        new_perso.remove();
        $('#users_list p:first').addClass("list_selected_element");
        creerFleches();
    });

    perso_src = sel_user.data("img") || perso_src;

    $('<img />').attr("src", perso_src).appendTo(new_perso);
    $('<span class="nom_perso" />').text(sel_user.text()).appendTo(new_perso);

    sel_user.remove();

    if ($(".perso_circuit").length > 1) {
        creerFleches();
    }
}

function creerFleches() {
    $(".fleche_circuit").remove();
    $("<span/>").addClass("fleche_circuit glyphicon glyphicon-arrow-right").insertAfter(".perso_circuit:not(:last, .deposant)");
}

$("#useradd_btn").click(function () {
    var sel_user = $('#users_list .list_selected_element');
    if (sel_user.length > 0) {
        ajoutUser(sel_user.attr("data-id"), true);
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

$("#circuitadd_btn").click(function () {
    var sel_circuit = $('#circuits_list .list_selected_element');
    var ordre = sel_circuit.attr("data-ordre").split(",");
    if (ordre.length > 0) {
        $.each($("#circuit .perso_circuit"), function (k, v) {
            var elem = $(this);
            $("<p/>").attr("data-id", elem.data("id")).text(elem.find(".nom_perso").text()).appendTo("#users_list");
            elem.remove();
        });
        creerFleches();

        $.each(ordre, function (k, v) {
            ajoutUser(v, true);
        });
    }
    $('#users_list p:first').addClass("list_selected_element");
    $("#circuit_name").val(sel_circuit.text());
    $("#circuit_modifier, #btn-group-supp").css("display", "inline-block");
});

// si on est dans un form on lui passe le circuit qd on walid
var form_parent = $("#circuit").parents("form");

if (form_parent.length > 0) {
    form_parent.submit(function (e) {
        e.preventDefault();
        var ordre_circuit = new Array();
        $("#circuit .perso_circuit").each(function (k, v) {
            ordre_circuit.push($(this).data("id"));
        });
        $('<input />')
            .attr({ 'type': 'hidden', 'name': "circuit" })
            .val(ordre_circuit)
            .appendTo(form_parent);
        this.submit();
        return false;
    });
}
