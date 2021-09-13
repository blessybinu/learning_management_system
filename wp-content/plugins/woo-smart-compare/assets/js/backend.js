'use strict';

(function($) {
  $(document).ready(function() {
    $('.woosc_color_picker').wpColorPicker();

    $('.woosc-fields-item').arrangeable({
      dragSelector: '.label',
    });

    $('.woosc-attributes-item').arrangeable({
      dragSelector: '.label',
    });
  });
})(jQuery);