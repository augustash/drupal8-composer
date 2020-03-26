/**
 * @file
 * Global eXo Image javascript.
 */

TSinclude('./exo.image/_exo.image.ts')

(function (Drupal, drupalSettings) {

  Drupal.behaviors.exoImage = {
    attach: function(context) {
      Drupal.ExoImage.attach(context);
    }
  }

}(Drupal, drupalSettings));
