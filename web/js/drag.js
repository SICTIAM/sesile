/**
 * Created by j.mercier on 08/11/2015.
 */
(function($) {

    $.fn.drag = function(action,options){

        options = $.extend({
                cHeight:"297px",
                cWidth:"210px",
                cVerticalPadding:"10px",
                cHorizontalPadding:"10px",
                cBorder:"solid 1px #000000",
                cColor:"#000000",
                cBgCol:"#ffffff",
                dHeight:"32px",
                dWidth:"64px",
                dBorder: "solid 1px #000000",
                dColor:"#000000",
                dBgCol:"#ffffff",
                dText: "VISA",
                posX:"134px",
                posY:"10px"

        }, options);

        if (action == "getPosX") {
            return Math.trunc($('.draggable').offset().left - $('.draggable').parent().offset().left).toString();
        }

        if (action == "getPosY") {
            return Math.trunc($('.draggable').offset().top - $('.draggable').parent().offset().top).toString();
        }

		if(action == "init")
		{
			return this.each(function() {
                $(this).addClass('visaContainer');
                $(this).empty();
                $(this).append('<div class="draggable ui-widget-content text-center">'+options.dText+'</div>');

                $('#containment .draggable').draggable({
                    containment:"parent"
                });

                $('.visaContainer').css({

                    height:options.cHeight,
                    width:options.cWidth,
                    "padding-top": options.cVerticalPadding,
                    "padding-left": options.cHorizontalPadding,
                    "padding-bottom": options.cVerticalPadding,
                    "padding-right": options.cHorizontalPadding,
                    border: options.cBorder,
                    color: options.cColor,
                    "background-color": options.cBgCol
                });

                var padTop = parseInt(options.cVerticalPadding.substr(0,options.cVerticalPadding.length-2));
                var padLeft = parseInt(options.cHorizontalPadding.substr(0,options.cHorizontalPadding.length-2));

                $('#containment .draggable').css({
                    position: "relative",
                    top: options.posY - padTop ,
                    left: options.posX - padLeft,
                    height:options.dHeight,
                    width:options.dWidth,
                    border: options.dBorder,
                    color: options.dColor,
                    "background-color": options.dBgCol
                });
            });
		}

		if(action == "initSign")
		{
			return this.each(function() {
                $(this).addClass('visaContainer');
                $(this).empty();
                $(this).append('<div class="draggable ui-widget-content text-center">'+options.dText+'</div>');

                $('#containmentSignature .draggable').draggable({
                    containment:"parent"
                });

                $('.visaContainer').css({

                    height:options.cHeight,
                    width:options.cWidth,
                    "padding-top": options.cVerticalPadding,
                    "padding-left": options.cHorizontalPadding,
                    "padding-bottom": options.cVerticalPadding,
                    "padding-right": options.cHorizontalPadding,
                    border: options.cBorder,
                    color: options.cColor,
                    "background-color": options.cBgCol
                });

                var padTop = parseInt(options.cVerticalPadding.substr(0,options.cVerticalPadding.length-2));
                var padLeft = parseInt(options.cHorizontalPadding.substr(0,options.cHorizontalPadding.length-2));

                $('#containmentSignature .draggable').css({
                    position: "relative",
                    top: options.posY - padTop ,
                    left: options.posX - padLeft,
                    height:options.dHeight,
                    width:options.dWidth,
                    border: options.dBorder,
                    color: options.dColor,
                    "background-color": options.dBgCol
                });
            });
		}
        
    };

})(jQuery);


tinymce.init({
    selector:'#sesile_mainbundle_collectivite_textmailnew, #sesile_mainbundle_collectivite_textmailwalid',
    plugins: "template table",
    templates: [
        {title: 'Validant', description: 'Intègre le validant', content: '&#123;&#123; validant &#125;&#125;'},
        {title: 'Rôle', description: 'Intègre le rôle du validant', content: '&#123;&#123; role &#125;&#125;'},
        {title: 'Titre classeur', description: 'Intègre le titre du classeur', content: '&#123;&#123; titre_classeur &#125;&#125;'},
        {title: 'Déposant', description: 'Intègre le déposant', content: '&#123;&#123; deposant &#125;&#125;'},
        {title: 'Date', description: 'Intègre la date de validation', content: '&#123;&#123; date_limite | date(\'d/m/Y\') &#125;&#125;'},
        {title: 'Lien du classeur', description: 'Intègre le lien vers le classeur', content: '&#123;&#123; lien|raw &#125;&#125;'},
        {title: 'Type de classeur', description: 'Intègre le type du classeur', content: '&#123;&#123; type &#125;&#125;'},
        {title: 'Logo collectivité', description: 'Intègre le logo de la collectivité', content: '**logo_coll**'}
    ]
});

tinymce.init({
    selector:'#sesile_mainbundle_collectivite_textmailrefuse',
    plugins: "template table",
    templates: [
        {title: 'Validant', description: 'Intègre le validant', content: '&#123;&#123; validant &#125;&#125;'},
        {title: 'Rôle', description: 'Intègre le rôle du validant', content: '&#123;&#123; role &#125;&#125;'},
        {title: 'Titre classeur', description: 'Intègre le titre du classeur', content: '&#123;&#123; titre_classeur &#125;&#125;'},
        {title: 'Motif du refus', description: 'Intègre le motif du refus', content: '&#123;&#123; motif &#125;&#125;'},
        {title: 'Date', description: 'Intègre la date de validation', content: '&#123;&#123; date_limite | date(\'d/m/Y\') &#125;&#125;'},
        {title: 'Lien du classeur', description: 'Intègre le lien vers le classeur', content: '&#123;&#123; lien|raw &#125;&#125;'},
        {title: 'Type de classeur', description: 'Intègre le type du classeur', content: '&#123;&#123; type &#125;&#125;'},
        {title: 'Logo collectivité', description: 'Intègre le logo de la collectivité', content: '**logo_coll**'}
    ]
});

tinymce.init({
    selector:'#sesile_mainbundle_collectivite_message'
});