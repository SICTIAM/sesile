perso_src = "/images/imghomme.png";
favoris_src = "/images/bookmark.png";
fleche_src = "/images/flechecircuit.png";
delete_src = "/images/delete.png";
favoris_url = "";
del_favoris_url = "";


$('#users_list option:first').attr("selected", "selected");


/*
 Ajoute un utilisateur dans le cadre "circuits"
 */
function ajoutUser(id) {
    var sel_user = $('#users_list option[value="' + id + '"]');
    if(sel_user.length == 0) {
        return false;
    }
    var new_perso = $('<div/>').data('id', id).addClass('perso_circuit').appendTo("#circuit");
    $('<img />').attr("src", perso_src).appendTo(new_perso);
    $('<span class="nom_perso" />').text(sel_user.text()).appendTo(new_perso);

    sel_user.remove();

    if ($('#users_list option').length == 0) {
        $("#useradd_btn").prop('disabled', true);
    }

    if ($(".perso_circuit").length > 1) {
        creerFleches();
    }
}

function creerFleches () {
    $(".fleche_circuit").remove();
    $("<img/>").attr("src", fleche_src).addClass("fleche_circuit").insertAfter(".perso_circuit:not(:last)");
}

function recreerCircuit (circuitArray) {
    // si des users ont été spécifiés on les ajoute en personnages :)
    if(circuitArray.length > 0) {
        $.each(circuitArray, function(k, v) {
            var sel_user = $('#users_list option[value="' + v + '"]');
            ajoutUser(sel_user.val());
        });
        creerFleches();
    }
}


$("#useradd_btn").click(function() {
    var sel_user = $('#users_list option:selected');
    if(sel_user.length > 0) {
        ajoutUser(sel_user.val());
    }
    $('#users_list option:first').attr("selected", "selected");
});

$("#circuit").sortable({
    items: '.perso_circuit',
    placeholder: 'emplacement',
    update: function(event, ui) {
        creerFleches();
    },
    over: function(e, ui) { sortableIn = 1; },
    out: function(e, ui) { sortableIn = 0; },
    beforeStop: function (event, ui) {
        if (sortableIn == 0) {
            elem = ui.item;
            $("<option/>").val(elem.data("id")).text(elem.find(".nom_perso").text()).appendTo("#users_list");
            elem.remove();
            $('#users_list option:first').attr("selected", "selected");
            $("#useradd_btn").prop('disabled', false);
            creerFleches();
        }
    }
 }).disableSelection();


$("#circuitadd_btn").click(function() {
    var sel_circuit = $('#circuits_list option:selected');
    var ordre = sel_circuit.attr("data-ordre").split(",");
    if(ordre.length > 0) {
        /*elem = ui.item;
        $("<option/>").val(elem.data("id")).text(elem.find(".nom_perso").text()).appendTo("#users_list");
        elem.remove();
        $('#users_list option:first').attr("selected", "selected");
        $("#useradd_btn").prop('disabled', false);
        */
        $("#circuit").empty();
        $.each(ordre, function (k, v) {
            ajoutUser(v);
        });
    }
    $('#users_list option:first').attr("selected", "selected");
});


 // si on est dans un form on lui passe le circuit qd on walid
var form_parent = $("#circuit").parents("form");

if(form_parent.length > 0 ) {
     form_parent.submit(function(e) {
         e.preventDefault();
         var ordre_circuit = new Array();
         $("#circuit .perso_circuit").each(function(k,v) {
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
