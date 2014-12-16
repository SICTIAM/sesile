function loadcorrectform() {
    $('#loadinggif').show();
    datatosend = { 'type': $("#type").val() };


    $.ajax({
        url: Routing.generate('classeur_new_type'),
        type: "post",
        data: datatosend,
        success: function (html) {


            /* Module de création de nouveau circuit */
            $('#contenttypedform').html(html);
            $('#circuitcontent').load(Routing.generate('new_circuit'));
            $('#circuitvalidation').load(Routing.generate('new_validate'));
            $('#documentcontent').load(Routing.generate('new_document'));
            $('#loadinggif').hide();


        }
    });
}


$(document).ready(function () {
    $("#type").change(loadcorrectform);
    loadcorrectform();

});