/**
 * Created by f.laussinot on 12/06/2015.
 * script utilisé pour la configuration des datables : affichage liste_a_valider et liste_retired
 */

// Gestion de l affichage du bouton de signature par lot
console.log(".chk : " + $("chk").length);
if ($(".chk").length == 0) {
    $(".btn-sign-select").hide();
}

// Action au click du bouton de signature par lot
$(".btn-valider-signer").one('click', function () {
    $("#form_a_valider").attr("action", $(this).attr("data-action")).submit();
});

$.fn.dataTable.moment( 'DD/MM/YYYY' );

    // Fonction permettant le retour en haut de page lors du chargement d une nouvelle page
    /*$(document).on('click', '#classeur-a-valider .paginate_button', function() {
            console.log("OK Coral 4");
            //console.log($("#liste_classeurs").position().top);
            console.log("Scroll ValidTable : " + $("#liste_classeurs").position().top);
            $("html, body").animate({ scrollTop: 0 }, "slow");
            //$("html, body").animate({ scrollTop: $("#liste_classeurs").position().top }, "slow");
            return false;
        }
    );*/

    // Fonction permettant le retour en haut de page lors du chargement d une nouvelle page
    $(document).on('click', '#liste_classeurs .paginate_button', function() {
            console.log("Scoll liste_classeurs de valid : " + $("#liste_classeurs").position().top);
            $("html, body").animate({ scrollTop: $("#liste_classeurs").position().top }, "slow");
            //$("html, body").scrollTop("#liste_classeurs");
            return false;
        }
    );

    // Permet de supprimer le message d erreur de la datatable lors du chargement de la 2° table
    $.fn.dataTable.ext.errMode = 'none';

    var oldStart = 0;
    var tab = $("#validTable").DataTable({
        oLanguage: {
            sSearch: "Rechercher&nbsp;",
            sInfo: "Classeurs _START_ &agrave; _END_ sur _TOTAL_",
            sInfoEmpty: "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
            sInfoFiltered: "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            sZeroRecords: "Aucun enregistrement &agrave; afficher",
            sLengthMenu: "Afficher _MENU_ classeurs par page",
            oPaginate: {
                sFirst: "",
                sPrevious: "",
                sNext: "",
                sLast: ""
            }
        },
        order: [[ 1, "desc" ]],
        iDisplayLength: 15,
        aLengthMenu: [15, 30, 50, 100],
        sDom: 'lft<"footer_datatables"ip>',
        aoColumnDefs: [
            {
                aTargets: [1,2,3,4],
                sClass: "center"
            },
            {
                aTargets: [3],
                bSortable: false
            },
            {
                aTargets: [5],
                sClass: "center",
                bSortable: true,
                "mRender": function ( data, type, full ) {
                    console.log('Full  : ' + data);
                    return '<span class="glyphicon statut_' + data + '"><span class="hidden">' + data + '</span></span>';
                }
            },
            {
                aTargets: [6],
                sClass: "center",
                bSortable: false,
                "mRender": function ( data, type, full ) {
                    var retour = '<a class="col-sm-6" href="' + Routing.generate('classeur_edit', {id: data}) + '"><span class="glyphicon glyphicon-pencil" title="Editer le document"></span></a>';
                    if (full[8] == 2) {
                        retour += '<a class="col-sm-6"href="' + Routing.generate('visu', {id: full[7]}) + '"><span class="glyphicon glyphicon-eye-open" title="Voir le document"></span></a>';
                    }
                    return retour;
                }
            },
            {
                aTargets: [7,8],
                visible:false
            },
            {
                aTargets: [9],
                sClass: "center",
                bSortable: false
            }
        ],
        sPaginationType: "full_numbers",
        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {

            $(nRow).addClass("classeur-row statut_"+aData[5]+"_line");
            /*$(nRow).click(function (e) {
                document.location.href = Routing.generate('classeur_edit', {id: aData[6]});
            });*/
            return nRow;
        },
        "fnDrawCallback": function (o) {
            if ( o._iDisplayStart != oldStart ) {
                var targetOffset = $('#validTable').position().top;
                $('html,body').animate({scrollTop: targetOffset}, 500);
                oldStart = o._iDisplayStart;
            }
        },
       "initComplete" :function(){
         //  $.fn.dataTable.moment( 'DD/MM/YYYY' );
           var tab = $("#fulltable").DataTable({
               "processing": true,
               "serverSide": true,
               "ajax": {
                   "url": Routing.generate('liste_classeurs')
               },
               oLanguage: {
                   sSearch: "Rechercher&nbsp;",
                   sInfo: "Classeurs _START_ &agrave; _END_ sur _TOTAL_",
                   sInfoEmpty: "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
                   sInfoFiltered: "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                   sZeroRecords: "Aucun enregistrement &agrave; afficher",
                   sLengthMenu: "Afficher _MENU_ classeurs par page",
                   oPaginate: {
                       sFirst: "",
                       sPrevious: "",
                       sNext: "",
                       sLast: ""
                   }
               },
               order: [[ 1, "desc" ]],
               iDisplayLength: 15,
               aLengthMenu: [15, 30, 50, 100],
               sDom: 'lft<"footer_datatables"ip>',
               aoColumnDefs: [
                   {
                       aTargets: [1,2,3,4],
                       sClass: "center"
                   },
                   {
                       aTargets: [3],
                       bSortable: false
                   },
                   {
                       aTargets: [5],
                       sClass: "center",
                       "mRender": function ( data, type, full ) {
                           return '<span class="glyphicon statut_' + data + '"></span>';
                       }
                   },
                   {
                       aTargets: [6],
                       sClass: "center",
                       bSortable: false,
                       "mRender": function ( data, type, full ) {
                           var retour = '<a class="col-sm-6" href="' + Routing.generate('classeur_edit', {id: data}) + '"><span class="glyphicon glyphicon-pencil" title="Editer le document"></span></a>';
                           if (full[8] == 2) {
                               retour += '<a class="col-sm-6" href="' + Routing.generate('visu', {id: full[7]}) + '"><span class="glyphicon glyphicon-eye-open" title="Voir le document"></span></a>';
                           }
                           return retour;
                       }
                   },
                   {
                       aTargets: [7,8],
                       visible:false
                   }
               ],
               sPaginationType: "full_numbers",
               fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                   /*  if (aData[5] == 0)
                    {
                    $(nRow).addClass("text-danger statut_"+aData[5]+"-line");
                    } */
                   $(nRow).addClass("classeur-row statut_"+aData[5]+"_line");
                   /*$(nRow).click(function (e) {
                       document.location.href = Routing.generate('classeur_edit', {id: aData[6]});
                   });*/
                   return nRow;
               }
           });
       }

    });
