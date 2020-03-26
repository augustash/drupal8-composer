(function ($, Drupal) {

  /**
   * Modal build behavior.
   */
  Drupal.behaviors.exoOembedClick = {
    attach: function(context) {
      $('.exo-oembed-click', context).once('exo.oembed').on('click', e => {
        e.preventDefault();
        var $link = $(e.currentTarget);
        var $iframe = '<iframe class="exo-oembed-content exo-oembed-video" src="' + $link.attr('href') + '" allow="autoplay; fullscreen" frameborder="0" scrolling="false" allowtransparency="true"></iframe>';
        $link.replaceWith($iframe);
      });
    }
  }

})(jQuery, Drupal);
