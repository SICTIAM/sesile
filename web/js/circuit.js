perso_src = "/images/imghomme.png";
favoris_src = "/images/bookmark.png";
fleche_src = "/images/flechecircuit.png";
delete_src = "/images/delete.png";
favoris_url = "";
del_favoris_url = "";


$('#users_list p:first, #circuits_list p:first').addClass("list_selected_element");


/*
 Ajoute un utilisateur dans le cadre "circuits"
 */
function ajoutUser(id) {
    var sel_user = $('#users_list p[data-id="' + id + '"]');
    if (sel_user.length == 0) {
        return false;
    }
    var new_perso = $('<div/>').data('id', id).addClass('perso_circuit').appendTo("#circuit");
    $("<span/>").addClass("suppr_perso glyphicon glyphicon-remove").appendTo(new_perso);
    $('<img />').attr("src", perso_src).appendTo(new_perso);
    $('<span class="nom_perso" />').text(sel_user.text()).appendTo(new_perso);

    sel_user.remove();

    if ($(".perso_circuit").length > 1) {
        creerFleches();
    }
}

function creerFleches() {
    $(".fleche_circuit").remove();
    $("<span/>").addClass("fleche_circuit glyphicon glyphicon-arrow-right").insertAfter(".perso_circuit:not(:last)");
}

function recreerCircuit(circuitArray) {
    // si des users ont été spécifiés on les ajoute en personnages :)
    if (circuitArray.length > 0) {
        $.each(circuitArray, function (k, v) {
            var sel_user = $('#users_list p[data-id="' + v + '"]');
            ajoutUser(v);
        });
        creerFleches();
    }
}


$("#useradd_btn").click(function () {
    var sel_user = $('#users_list .list_selected_element');
    if (sel_user.length > 0) {
        ajoutUser(sel_user.attr("data-id"));
    }
    $('#users_list p:first').addClass("list_selected_element");
});

$("#circuit").sortable({
    items: '.perso_circuit',
    placeholder: 'emplacement',
    update: function (event, ui) {
        creerFleches();
    },
    over: function (e, ui) {
        sortableIn = 1;
    },
    out: function (e, ui) {
        sortableIn = 0;
    },
    beforeStop: function (event, ui) {
        if (sortableIn == 0) {
            elem = ui.item;
            $("<p/>").attr("data-id", elem.data("id")).text(elem.find(".nom_perso").text()).appendTo("#users_list");
            elem.remove();
            $('#users_list p:first').addClass("list_selected_element");
            creerFleches();
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
            ajoutUser(v);
        });
    }
    $('#users_list p:first').addClass("list_selected_element");
    $("#circuit_name").val(sel_circuit.text());
    $("#circuit_modifier, #btn-group-supp").show();
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
