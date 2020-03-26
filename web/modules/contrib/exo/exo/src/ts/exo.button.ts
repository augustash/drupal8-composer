/**
 * @file
 * Select as links javascript.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.exoButton = {
    attach: function (context) {
      $('.exo-button-trigger', context).once('exo-button').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.exo-button').find('input[type="submit"]').trigger('mousedown').trigger('mouseup').trigger('click');
      });
    }
  };

}(jQuery, Drupal));
