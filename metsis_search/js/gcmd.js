(function ($, Drupal) {
    Drupal.behaviors.gcmdKeywords = {
      attach: function (context, settings) {
          $('#gcmdblock',context).once('gcmdKeywords').each(function() {

//            $('#gcmd_l1').each(function() {
              $('.facets-soft-limit-link').css('display', 'none');
              $('ul.facet-active a').css('display', 'none');
              $('ul.facet-active a.is-active').css('display', 'block');
//            });
/*
            $('#gcmd_l2').each(function() {
              $('.facets-soft-limit-link').css('display', 'none');
              $('ul.facet-active a').css('display', 'none');
              $('ul.facet-active a.is-active').css('display', 'block');
            });
*/          });
        },
      };
    })(jQuery, Drupal);
