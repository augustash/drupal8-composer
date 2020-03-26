(function ($, Drupal, displace) {

  /**
   * Autogrow.
   */
  Drupal.behaviors.exoFormAutogrow = {
    attach: function(context) {
      const $elements = $(context).find('textarea[data-autogrow]').once('exo.form.autogrow');
      if ($elements.length) {
        Drupal.Exo.event('ready').on('exo.form.autogrow', function () {
          $elements.each(function () {
            const $element = $(this);
            const maxHeight = $element.data('autogrow-max');
            if (maxHeight) {
              $element.css('max-height', maxHeight);
            }
          });
          autosize($elements);
        });
      }
    }
  }

})(jQuery, Drupal, Drupal.displace);
