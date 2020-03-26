/**
 * @file
 * Extends the Drupal AJAX functionality to integrate the aside API.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Command to inser a modal.
   *
   * @param {Drupal.Ajax} ajax
   *   The Drupal Ajax object.
   * @param {object} response
   *   Object holding the server response.
   * @param {number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.exoModalInsert = function (ajax, response, status) {
    response.selector = response.selector || 'body';
    response.method = 'append';
    Drupal.AjaxCommands.prototype.insert(ajax, response);
  };

  /**
   * Command to replace content for a modal.
   *
   * @param {Drupal.Ajax} ajax
   *   The Drupal Ajax object.
   * @param {object} response
   *   Object holding the server response.
   * @param {number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.exoModalContent = function (ajax, response, status) {
    if (typeof ajax.exoModal !== 'undefined') {
      response.selector = $(ajax.exoModal.getSelectorAsId() + ' .exo-modal-content').first();
      if (response.selector.length) {
        response.method = 'html';
        Drupal.AjaxCommands.prototype.insert(ajax, response);
        ajax.exoModal.rebuildContent().open();
      }
    }
  };

})(jQuery, Drupal);
