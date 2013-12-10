

function loadcorrectform(){
    datatosend = { 'type': $( "#type").val() };

    $.ajax({
        url: Routing.generate('classeur_new_type'),
        type: "post",
        data: datatosend,
        success: function(html){

            $('#contenttypedform').html(html);
            $('#circuitcontent').load(Routing.generate('new_circuit'));
            $('#documentcontent').load(Routing.generate('new_document'));

        }
    });
}


$(document).ready(function(){

    $( "#type" ).change(loadcorrectform);
    loadcorrectform();

});