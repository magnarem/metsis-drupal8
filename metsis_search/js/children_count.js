(function ($, Drupal) {
//  Drupal.behaviors.metsis_search = {
//    attach: function (context, settings) {
      // I am doing a find() but you can do a once() or whatever you like :-)
      //var metaIds = document.querySelectorAll('.views-field-id');
      //alert(metaIds);
  //    $(document, context).once('metsis_search').each(function() {
  $( document ).ready(function() {

        $(".metaids").each(function() {
          var reg = /(\<!--.*?\-->)/g;
          var string = $(this).html();
          var metaid = string.replace(reg,"").trim();
          //console.log(string);
          var myurl = '/metsis/elements/count?metadata_identifier='+metaid;
          $.ajax({
            url: myurl,
            success : function(response) {
            //  Drupal.behaviors.metsis_search = {
            //    attach: function (context, settings) {
                  var data = $.parseJSON(response);
                  var count = parseInt(data.count);
                  var divid = '#'+metaid;
                  console.log(divid);
                  if(count > 0) {
                    var markup = '<a href="/metsis/elements?metadata_identifier=#{metaid}"/>Child data..[#{data.count}]</a>';
                    console.log(markup);
                    $(divid,context).html(markup);
                  }
            //    }
            //  };

              },
            error: function (request, status, error) {
                console.log("Error");
              }
            });

          });
      //  var doc = document.getElementsByClassName('views-field views-field-id');
      //  var notes = doc.getElementsByClassName('field-content visually-hidden metaids');
      //  console.log(notes);
    //  });
//    }
  //};
});
//});

})(jQuery, Drupal);
