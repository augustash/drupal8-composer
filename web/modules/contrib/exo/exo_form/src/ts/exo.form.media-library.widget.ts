(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.ExoFormMediaLibraryWidgetSortable = {
    attach: function attach(context) {
      // Media library currently gives no way to alter this element.
      $('#media-library-wrapper').once('exo.form.media-library').each((index, element) => {
        if ($(element).find('.media-library-menu').length) {
          $(element).addClass('has-media-library-menu')
        }
      });
      $('#media-library-wrapper .media-library-menu li.active').removeClass();
      $('#media-library-wrapper .media-library-menu a.active').parent().addClass('active');
    }
  };
})(jQuery, Drupal, drupalSettings);
