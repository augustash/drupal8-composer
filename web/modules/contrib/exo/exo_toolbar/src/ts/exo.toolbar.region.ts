/**
 * @file
 * Extends the Drupal AJAX functionality to integrate the region API.
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
  Drupal.AjaxCommands.prototype.exoToolbarRegion = function (ajax:any , response:any, status:any) {
    Drupal.AjaxCommands.prototype.insert(ajax, response);
  };

})(jQuery, Drupal);
