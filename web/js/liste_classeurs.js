/**
 * Created by f.laussinot on 12/06/2015.
 * script utilisé pour la configuration des datables : affichage liste_a_valider et liste_retired
 */
$(document).ready(function () {
    $("#validTable").dataTable({
        oLanguage: {
            sSearch: "Rechercher&nbsp;",
            sInfo: "Classeur  _START_ &agrave; _END_ sur _TOTAL_",
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
                    var retour = '<a class="col-sm-6" href="' + Routing.generate('classeur_edit', {id: data}) + '"><span class="glyphicon glyphicon-pencil"></span></a>';
                    if (full[8] == 2) {
                        retour += '<a class="col-sm-6"href="' + Routing.generate('visu', {id: full[7]}) + '"><span class="glyphicon glyphicon-eye-open"></span></a>';
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
            $(nRow).addClass("classeur-row");
            $(nRow).click(function (e) {
                document.location.href = Routing.generate('classeur_edit', {id: aData[6]});
            });
            return nRow;
        }
    });
});