(function($){
    var Circuiter = function(element, options)
    {
        var elem = $(element);
        var obj = this;
        var settings = $.extend({

        }, options || {});
    };

    $.fn.circuiter = function(options) {
        return this.each(function()
        {
            var defaults = {
                image_src: "/images/bonhomme.jpg",
                users: new Array()
            };

            var params = $.extend(defaults, options);

            // TODO l'élement choisi doit être un select
            that = $(this);


            // cadre pour afficher les personnes du circuit
            circuit = $("<div/>").attr("id", "circuit").insertAfter(that);
            that.data("circuit", circuit);
            // bouton OK
            bouton = $("<button/>").attr({id: "user_ok", type: "button"}).text("OK").insertAfter(that);



            // si des users ont été spécifiés on les ajoute en personnages :)
            if(params.users.length > 0) {
                $.each(params.users, function(k, v) {
                    var user_to_ajout = that.find('option[value="' + v + '"]');

                    var new_perso = $('<div/>').data('id', user_to_ajout.val()).addClass('perso_circuit').appendTo(circuit);
                    $('<img />').attr("src", params.image_src).appendTo(new_perso);
                    $('<span class="nom_perso" />').text(user_to_ajout.text()).appendTo(new_perso);

                    user_to_ajout.remove();
                    // desactive le bouton si select si vide
                    if(that.find('option').length == 0) {
                        bouton.prop('disabled', true);
                    }

                    // ajout de la fleche
                    if(circuit.find(".perso_circuit").length > 1) {
                        $('<div/>').html("&larr;").addClass('fleche_circuit').html('&rarr;').insertBefore(circuit.find(".perso_circuit:last"));
                    }
                });
            }

            bouton.click(function() {
                var sel_user = that.find(":selected");
                var new_perso = $('<div/>').data('id', sel_user.val()).addClass('perso_circuit').appendTo(circuit);
                $('<img />').attr("src", params.image_src).appendTo(new_perso);
                $('<span class="nom_perso" />').text(sel_user.text()).appendTo(new_perso);

                sel_user.remove();
                // desactive le bouton si select si vide
                if(that.find('option').length == 0) {
                    $(this).prop('disabled', true);
                }

                // ajout de la fleche
                if(circuit.find(".perso_circuit").length > 1) {
                    $('<div/>').html("&larr;").addClass('fleche_circuit').html('&rarr;').insertBefore(circuit.find(".perso_circuit:last"));
                }
            });

            circuit.sortable({
                items: '.perso_circuit',
                placeholder: 'emplacement',
                update: function(event, ui) {
                    $('.fleche_circuit').remove();
                    if($("#circuit .perso_circuit").length > 1) {
                        $('<div/>').html("&larr;").addClass('fleche_circuit').html('&rarr;').insertAfter(circuit.find(".perso_circuit:not(:last)"));
                    }
                },
                over: function(e, ui) { sortableIn = 1; },
                out: function(e, ui) { sortableIn = 0; },
                beforeStop: function (event, ui) {
                    if (sortableIn == 0) {
                        elem = ui.item;
                        $("<option/>").val(elem.data("id")).text(elem.find(".nom_perso").text()).appendTo(that);
                        elem.remove();
                        bouton.prop('disabled', false);
                        $('.fleche_circuit').remove();
                        if($("#circuit .perso_circuit").length > 1) {
                            $('<div/>').html("&larr;").addClass('fleche_circuit').html('&rarr;').insertAfter(circuit.find(".perso_circuit:not(:last)"));
                        }
                    }
                }
            }).disableSelection();

            if (that.data('circuiter')) return;
            var circuiter = new Circuiter(this, params);
            that.data('circuiter', circuiter);


            // si on est dans un form on lui passe le circuit qd on walid
            var form_parent = that.parents("form")
            if(form_parent.length ) {
                form_parent.submit(function(e) {
                    e.preventDefault();
                    var ordre_circuit = new Array();
                    $("#circuit .perso_circuit").each(function(k,v) {
                        ordre_circuit.push($(this).data("id"));
                    });
                    $('<input />').attr('type', 'hidden').attr('name', "circuit").attr('value', ordre_circuit).appendTo(form_parent);
                    this.submit();
                    return false;
                });
            }
        });
    };
})(jQuery);
