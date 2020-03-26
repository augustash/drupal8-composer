/**
 * @file
 * Drupal Embed module.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Ajax 'embed_insert' command: insert the rendered embedded item.
   *
   * The regular Drupal.ajax.commands.insert() command cannot target elements
   * within iframes. This is a skimmed down equivalent that works no matter
   * whether the CKEditor is in iframe or div area mode.
   *
   * @param {Drupal.Ajax} ajax
   *   An Ajax object.
   * @param {object} response
   *   The Ajax response.
   * @param {string} response.data
   *    The Ajax response's content.
   * @param {number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.embed_insert = function (ajax, response, status) {
    var $target = $(ajax.element);
    // No need to detach behaviors here, the widget is created fresh each time.
    $target.html(response.data);
    // var $child = $target.children().first();
    // // Apply child entity classes to parent wrapper so styling can happen.
    // $target.addClass($child.attr('class'));
    // $child.removeAttr('class');
    Drupal.runEmbedBehaviors('attach', $target.get(0), response.settings || ajax.settings);
  };

})(jQuery, Drupal);
