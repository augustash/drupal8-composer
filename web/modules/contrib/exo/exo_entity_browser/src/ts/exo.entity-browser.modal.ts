/**
 * @file exo.modal.entity-browser.ts
 *
 * Defines the behavior of the entity browser's eXo modal display.
 */

(function ($, _, Drupal, drupalSettings) {

  'use strict';

  Drupal.entityBrowserExoModal = {};

  if (Drupal.AjaxCommands) {
    Drupal.AjaxCommands.prototype.select_entities = function (ajax, response, status) {
      var uuid = drupalSettings.entity_browser.exo_modal.uuid;
      $(':input[data-uuid="' + uuid + '"]').trigger('entities-selected', [uuid, response.entities])
        .removeClass('entity-browser-processed').unbind('entities-selected');
    };
  }

  /**
   * Registers behaviours related to exo_modal display.
   */
  Drupal.behaviors.entityBrowserExoModal = {
    attach: function (context) {
      if (drupalSettings.entity_browser) {
        _.each(drupalSettings.entity_browser.exo_modal, function (instance:any) {
          _.each(instance.js_callbacks, function (callback:any) {
            // Get the callback.
            callback = callback.split('.');
            var fn = window;

            for (var j = 0; j < callback.length; j++) {
              fn = fn[callback[j]];
            }

            if (typeof fn === 'function') {
              $(':input[data-uuid="' + instance.uuid + '"]').not('.entity-browser-processed')
                .bind('entities-selected', fn).addClass('entity-browser-processed');
            }
          });
          if (instance.auto_open) {
            $('input[data-uuid="' + instance.uuid + '"]').click();
          }
        });
      }
    }
  };

}(jQuery, _, Drupal, drupalSettings));
