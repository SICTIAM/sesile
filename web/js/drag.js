/**
 * Created by j.mercier on 08/11/2015.
 */
(function($) {

    $.fn.drag = function(action,options){

        options = $.extend({
                cHeight:"594px",
                cWidth:"420px",
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
                dText: "Drag Me",
                posX:100,
                posY:100

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

            $('.draggable').draggable({
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

            $('.draggable').css({
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