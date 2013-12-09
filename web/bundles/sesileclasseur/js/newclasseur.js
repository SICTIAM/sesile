



$(document).ready(function(){

    $( "#type" ).change(function() {


        datatosend = { 'type': $( "#type").val() };

        $.ajax({
            url: Routing.generate('classeur_new_type'),
            type: "post",
            data: datatosend,
            success: function(html){

                $('#contenttypedform').html(html);

            }
        });


    });


    $('#circuitcontent').load()


});