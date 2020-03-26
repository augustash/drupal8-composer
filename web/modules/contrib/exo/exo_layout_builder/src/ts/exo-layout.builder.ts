(function ($, Drupal, displace) {

  'use strict';

  Drupal.behaviors.exoLayoutBuilder = {
    attach: function (context) {
      $(context).find('.exo-layout-builder').once('exo-layout-builder').each((index, element) => {
        const $form = $(element);
        $(document).on('drupalViewportOffsetChange.exo-layout-builder', e => {
          $form.find('.exo-layout-builder-top').css({paddingTop: displace.offsets.top});
        });
      });
      if ($('.exo-content .messages.warning').length > 1) {
        $('.exo-content .messages.warning').eq(1).hide();
      }
    }
  };

})(jQuery, Drupal, Drupal.displace);
