(
    function($) {

      $(document).ready(function() {

        var $tabs              = $('.insight-cmb2-tabs'),
            CookiesInsightCore = Cookies.noConflict(),
            postID             = $('#post_ID').val(),
            cookieName         = 'ic_metabox_tab_' + postID;

        if (!$tabs.length) {
          return;
        }

        // init jQuery UI Tabs
        $tabs.tabs();

        // check cookies
        if (id = CookiesInsightCore.get(cookieName)) {
          $tabs.find('a.ui-tabs-anchor#' + id).trigger('click');
        }

        // Set cookies
        $tabs.find('a.ui-tabs-anchor').on('click', function() {

          var id = $(this).attr('id');
          CookiesInsightCore.set(cookieName, id, {path: '/', expires: 30});
        });
      });
    }
)(jQuery);

jQuery.noConflict();
