$(document).ready(function() {

    $(".selusers").select2();

    // Pour le bouton de suppression de l etape
    $(document).on('click','#suppetape',function() {
        $(this).parent().remove();
    });

    var tabGeneral = [];
    $('#new_classeur').submit(function(){
        /*
         * Pour chaque etape de validation créé on récupère les options sélectionnées
         *
         * */
        $('select.selusers').each(function(){

            var Etapes = {};
            Etapes.etape_id = $(this).data('etape');
            Etapes.etapes = [];

            if($(this).val())
            {
                $(this).find(':selected').each(function(){
                    /*
                     * Pour chaque option selectionnée, on récupère son data-type(groupe ou user) et sa value (id du user ou du userPack)
                     * */
                    Etapes.etapes.push({
                        entite: $(this).data('type'),
                        id: $(this).val()
                    });
                });
            } else {
                e.preventDefault();
            }
            tabGeneral.push(Etapes);
        });

        /*
         * on met tout ça dans un tableau que l'on stringifie et qu'on met dans le hidden
         * */
        $('#valeurs').val(JSON.stringify(tabGeneral));

    });

    $(function() {
        $( "#contetapes" ).sortable({
            tolerance: "pointer"
        }).disableSelection();
    });

});