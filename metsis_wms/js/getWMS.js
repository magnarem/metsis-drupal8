
(function ($) {
    //
    $(document).ready(function () {

        var wmsURL = $("#wmsURL").html();
        wmsURL = wmsURL + "?SERVICE=WMS&REQUEST=GetCapabilities";
        console.log(wmsURL);
        //alert(wmsURL);
        var wmsClient = new wmsc({"wmsUrl": wmsURL}).init();
    });
    //
})(jQuery);
