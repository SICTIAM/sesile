// Fonction permettant de charger les types en fonction du groupe selectionné
function loadtype() {
    $('#loadinggif').show();
    dataUserGroupe = { 'usergroupe': $('#userGroupe').val() };
    var id_so = $('#userGroupe').val();

    $.ajax({
        url: Routing.generate('user_groupe_selected'),
        type: "post",
        data: dataUserGroupe,
        success: function (html) {
            loadcorrectform(id_so);
            $('#type').html(html);
            $('#loadinggif').hide();
        }
    });
}

// Fonction permettant de charger le formulaire en fonction du type de classeur sélectionné
function loadcorrectform(id_so) {
    $('#loadinggif').show();
    datatosend = { 'type': $("#type").val() };

    $.ajax({
        url: Routing.generate('classeur_new_type'),
        type: "post",
        data: datatosend,
        success: function (html) {

            $('#contenttypedform').html(html);
            $('#circuitcontent').load(Routing.generate('new_circuit', { 'so': id_so} ));
            $('#documentcontent').load(Routing.generate('new_document'));
            $('#loadinggif').hide();

        }
    });
}

$(document).ready(function () {
    var id_so = $('#userGroupe').val();
    //$('#userGroupe').change(loadtype);
    $('#userGroupe').on('change', function() {
        loadtype();
    });
    loadtype();
    //$("#type").change(loadcorrectform(id_so));
    $("#type").on('change', function() {
        var id_so = $('#userGroupe').val();
        loadcorrectform(id_so);
    });
    //loadcorrectform();
});