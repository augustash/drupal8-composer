/**
 * @file
 * Defines the behavior for entities list.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attaches the behavior of the media entity browser view.
   */
  Drupal.behaviors.exoEntityBrowserEntitiesList = {

    attach: function (context, settings) {
      // Due to styling -- we need show hide this element.
      $(context).find('.entities-list').each(function () {
        if ($(this).children().length) {
          $(this).show();
        }
        else {
          $(this).hide();
        }
      });
    }

  };

}(jQuery, Drupal));
