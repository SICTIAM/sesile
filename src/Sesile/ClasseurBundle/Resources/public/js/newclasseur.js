// Fonction permettant de charger les types en fonction du groupe selectionné
function loadtype() {
    $('#loadinggif').show();
    dataUserGroupe = { 'usergroupe': $('#userGroupe').val() };

    $.ajax({
        url: Routing.generate('user_groupe_selected'),
        type: "post",
        data: dataUserGroupe,
        success: function (html) {
            loadcorrectform();
            $('#type').html(html);
            $('#loadinggif').hide();
        }
    });
}

// Fonction permettant de charger le formulaire en fonction du type de classeur sélectionné
function loadcorrectform() {
    $('#loadinggif').show();
    datatosend = { 'type': $("#type").val() };

    $.ajax({
        url: Routing.generate('classeur_new_type'),
        type: "post",
        data: datatosend,
        success: function (html) {

            $('#contenttypedform').html(html);
            $('#circuitcontent').load(Routing.generate('new_circuit', { slug: 'walter'} ));
            $('#documentcontent').load(Routing.generate('new_document'));
            $('#loadinggif').hide();

        }
    });
}

$(document).ready(function () {
    $('#userGroupe').change(loadtype);
    loadtype();
    $("#type").change(loadcorrectform);
    //loadcorrectform();
});