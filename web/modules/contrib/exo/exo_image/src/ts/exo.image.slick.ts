/**
 * @file
 * Provides slick fixes when using drimage.
 */

(function ($, document) {

  'use strict';

  Drupal.behaviors.exoImageSlick = {

    attach: function (context) {
      if (Drupal.drimage) {
        $('.slick', context).once('exo.image.slick').on('init', function (event, slick) {
          // Make sure drimages that are within slick slider will size when built.
          Drupal.drimage.init(event.target);
        });
      }
    }
  };

}(jQuery, document));
