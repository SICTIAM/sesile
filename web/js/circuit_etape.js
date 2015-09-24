function findUsersByUserPackId(ident){
    var total = '';
    $.ajax({
        url: Routing.generate('show_userpack', {id: ident}),
        async: false,
        method: "GET",
        success:function(resp){
            $.each (resp,function(index,value){
                if(total == '')
                {
                    total = value;
                }
                else{
                    total += '<br>'+value;
                }

            });

        }
    });
    return total;
}

function initPopover(){



    $('[data-toggle="popover"]').popover({
        html: true,
        trigger: 'hover',
        container: 'body',
        title: function(){return $(this).text();},
        content: function(){return findUsersByUserPackId($(this).data('id'));}
    });
}

$(document).ready(function() {



    function formatState (state) {

        if (!state.id) { return state.text; }
        var tabType = state.id.split('-');

        if(tabType[0] == 'user')
        {
            return '<span><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;'+ state.text+'</span>';
        }
        else{
            return '<span data-toggle="popover" data-id="'+tabType[1]+'" style="display: block"><i class="fa fa-group"></i>&nbsp;&nbsp;'+ state.text+'</span>';
        }

    };


    $("select.selusers").select2({
        formatResult: formatState,
        formatSelection:formatState
    });



    initPopover();
    $(".selusers").on("select2-selecting", function () {
        $('[data-toggle="popover"]').popover('destroy');
        initPopover();

        //alert('select')
    });

    $(".selusers").on("select2-close", function () {
        //alert('close')
        initPopover();

    });
    $(".selusers").on("select2-open", function () {
        initPopover();

    });

  /*  var eventSelect = $(".selusers");
    eventSelect.select2(); */
    creerFleches();
    affButtons ();


    scrollStart ();
    // Pour scrolle le circuit tout a droite
    // Fonction permettant d agrandir la frise dynamiquement.
    function scrollStart () {
        var widthLine = Math.max.apply(null, $('#circuit_etapes').map(function () { return $('#circuit_etapes').width(); }).get());
        var widthScroll = Math.max.apply(null, $('#circuit_with_js').map(function () { return $('#circuit_with_js').width(); }).get());

        var scroll = widthScroll - widthLine;
        $('#circuit_etapes').scrollLeft(scroll);
        console.log(scroll);
    }
    // Pour le scroll à la molette de la souris
    $("#circuit_etapes").mousewheel(function(event, delta) {
        this.scrollLeft -= (delta * 100);
        event.preventDefault();
    });


    // Fonction pour calculer la hauteur max des etapes
    // Cette fonction n'est pas utilisée pour le mono-ligne
    function heightEtapes () {

        var heightEtapes = 0;
        var contetapes = $('#contetapes');

        contetapes.children('.etapes-circuit').each(function() {
            heightEtapeCurrent = Math.max.apply(null, $(this).map(function () { return $(this).height(); }).get()) + 40;
            if (heightEtapes < heightEtapeCurrent) {
                heightEtapes = heightEtapeCurrent;
            }
        });
        contetapes.children('.etapes-circuit').each(function() {
            $(this).css('height', heightEtapes);
        });
    }

    // Fonction initialisation pour les boutons
    function affButtons () {
        var contetapes = $('#contetapes');
        contetapes.children('.etapes-circuit').each(function() {
            var maxHeight = zoneHeight($(this).children('.selusers').children('.select2-choices').children('li'));

            //console.log($(this).children('.selusers').children('.select2-choices').children('li'));

            if (maxHeight > 85) {
                //var maxHeight = zoneHeight($(this).currentTarget.selectedOptions);
                console.log(maxHeight);
                $(this).children('#hiddeUsers').hide();
                $(this).children('#seeUsers').show();
            }
        });
        $('#hiddeUsers').hide();
        $('.etape-groupe').siblings('#seeUsers').show();
    }

    // Fonction permettant d afficher le bouton #seeUsers
    function affSeeUsers (that, aff) {
        if (!aff) {

            that.children('#seeUsers').show();
            that.children('#hiddeUsers').hide();
        }

    //    that.children('.select2-container-multi').children('.select2-choices').css('height', '25px');
        that.css('height', 'auto');

        //that.children('.etape-groupe').hide();

    }

    // Fonction permettant d afficher le bouton #hiddeUsers
    function affHiddeUsers (that) {

        that.children('.etape-groupe-users').show();
        that.children('.etape-groupe').hide();
        //$('.etape-groupe').siblings('#seeUsers, #hiddeUsers').hide();

        that.children('#seeUsers').hide();
        that.children('#hiddeUsers').show();
        that.children('.select2-container-multi').children('.select2-choices').css('height', 'auto');
        that.css('height', 'auto');
    }

    // Fonction pour voir tous les utilisateurs
    function affAllUsers () {
        var contetapes = $('#contetapes');
        contetapes.children('.etapes-circuit').each(function() {
            var clicked = $(this);
            var cpt = 0
            $(this).find('.selusers').find(':selected').each(function(){
                cpt ++;
            });
            if(cpt > 3){
                affHiddeUsers(clicked);
                clicked.children('.select2-container-multi').show();
                clicked.children('.etape-groupe').hide();
                clicked.children('.select2-container-multi').children('.select2-choices').css('height', 'auto');
            }
            else{
                clicked.children('#seeUsers').hide();
            }

            // Décommenter pour l'affichage multi-lignes
            //heightEtapes();
        });
    }

    // Fonction pour cacher tous les utilisateurs
    function hideAllUsers (aff) {
        var contetapes = $('#contetapes');
        contetapes.children('.etapes-circuit').each(function() {
            affSeeUsers($(this), aff);
        });
        //$('.etape-groupe').siblings('#seeUsers, #hiddeUsers').hide();
        $('.etape-groupe-users').hide();
        $('.etape-groupe').show();
        //$('.etape-groupe').siblings('#seeUsers, #hiddeUsers').hide();
        $('.etape-groupe').siblings('#hiddeUsers').hide();
    }

    // Fonction creer une fleche pour le sens de lecture
    function creerFleches() {
        $(".fleche_circuit").remove();
        //$("<span/>").addClass("fleche_circuit glyphicon glyphicon-arrow-right").insertAfter(".etapes-circuit:not(:last, .deposant)");
        //$("<span/>").addClass("fleche_circuit glyphicon glyphicon-chevron-right").insertAfter(".etapes-circuit:not(.deposant)");
        $("<span/>").addClass("fleche_circuit glyphicon glyphicon-chevron-right").insertAfter(".etapes-circuit:not(:last)");
    }

    // Fonction calculant la hauteur de la zone d'affichage des utilisateurs
    function zoneHeight (that) {
        /*that.each(function() {
            maxHeight += Math.max.apply(null, $(this).map(function () { return $(this).height(); }).get()) + 5;
            console.log('maxHeight : ' + maxHeight);
        });*/

        var maxHeight = 0;
        var x = that;
        for(var i= 0; i < x.length; i++) {
            maxHeight += x[i].clientHeight + 4;
        }

        return maxHeight;
    }

    // Après la selection d un utilisateur dans le menu déroulant
    $(document).on("select2-close", ".selusers", function(e) {
        //console.log(e.currentTarget.selectedOptions.length);

        var maxHeight = zoneHeight(e.currentTarget.selectedOptions);
        /*var x = e.currentTarget.selectedOptions;
        for(var i= 0; i < x.length; i++) {
            maxHeight += x[i].clientHeight + 4;
        }*/
        //console.log(maxHeight);

        //if (e.currentTarget.selectedOptions.length > 3) {
        //console.log('close : ' + maxHeight);
        if (maxHeight > 80) {
            //affSeeUsers($(this).parent());
            $(this).parent().children('#hiddeUsers').hide();
            $(this).parent().children('#seeUsers').show();
        }
    });

    // Quand un utilisateur est déselectionné
    $(document).on("select2-removed", ".selusers", function (e) {
        //console.log(e);
        //if (e.currentTarget.selectedOptions.length <= 3) {
        var maxHeight = zoneHeight(e.currentTarget.selectedOptions);
        //console.log('removed : ' + maxHeight);
        if (maxHeight < 80) {
            //affSeeUsers($(this).parent());

            $(this).parent().children('#hiddeUsers').hide();
            //console.log($(this).parent().children('#hideUsers').hide());
            $(this).parent().children('#seeUsers').hide();
        }
    });

    // Evenement du bouton voir les utilisateurs
    $(document).on('click','#seeUsers',function(event) {
        var clicked = $(this);
        //aff_users($(this));
        affHiddeUsers (clicked.parent());
        //clicked.parent().children('.select2-container-multi').children('.select2-choices').css('height', 'auto');
        clicked.siblings('.select2-container-multi').children('.select2-choices').css('height', 'auto');
        // Décommenter pour l affichage multi-lignes
        //heightEtapes();
    });


    // Evenement du bouton cacher les utilisateurs
    $(document).on('click','#hiddeUsers',function() {
        affSeeUsers($(this).parent());
    });


    // Evenement du bouton voir tous les utilisateurs
    $(document).on('click','#seeAllUsers',function(event) {


        affAllUsers ();
    });

    // Evenement du bouton ajouter une etape
    $(document).on('click', '#addetape', function (e) {
        hideAllUsers(true);
        creerFleches();
        $('.etape-groupe-users').hide();
        $('.etape-groupe').show();
        $('.etape-groupe').siblings('#hiddeUsers').hide();
        $('.etape-groupe').siblings('#seeUsers').show();
    });

    // Pour cacher tous les utilisateurs
    $(document).on('click','#hiddeAllUsers',function() {
        hideAllUsers ();
    });

    //$('#seeAllUsers').trigger( "click" );

/*    var tabGeneral = [];
    $('#new_classeur').submit(function(){
        *//*
         * Pour chaque etape de validation créé on récupère les options sélectionnées
         *
         * *//*
        $('select.selusers').each(function(){

            var Etapes = {};
            Etapes.etape_id = $(this).data('etape');
            Etapes.etapes = [];

            if($(this).val())
            {
                $(this).find(':selected').each(function(){
                    *//*
                     * Pour chaque option selectionnée, on récupère son data-type(groupe ou user) et sa value (id du user ou du userPack)
                     * *//*
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

        *//*
         * on met tout ça dans un tableau que l'on stringifie et qu'on met dans le hidden
         * *//*
        $('#valeurs').val(JSON.stringify(tabGeneral));

    });

    $(function() {
        $( "#contetapes" ).sortable({
            tolerance: "pointer"
        }).disableSelection();
    });*/

});