/**
 * @file
 * Replaces the home link in toolbar with a back to site link.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var pathInfo = drupalSettings.path;
  var exoToolbarAdminEscape = sessionStorage.getItem('exoToolbarAdminEscape');
  var windowLocation = window.location;

  // Saves the last non-administrative page in the browser to be able to link
  // back to it when browsing administrative pages. If there is a destination
  // parameter there is not need to save the current path because the page is
  // loaded within an existing "workflow".
  if (!pathInfo.currentPathIsAdmin && !/destination=/.test(windowLocation.search)) {
    sessionStorage.setItem('exoToolbarAdminEscape', windowLocation.href);
  }

  /**
   * Replaces the "Home" link with "Back to site" link.
   *
   * Back to site link points to the last non-administrative page the user
   * visited within the same browser tab.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the replacement functionality to the toolbar-escape-admin element.
   */
  Drupal.behaviors.exoToolbarAdminEscape = {
    attach: function () {
      var $element = $('a.exo-toolbar-admin-escape').once('exo.toolbar.admin-escape');
      if ($element.length && pathInfo.currentPathIsAdmin) {
        if (exoToolbarAdminEscape !== null) {
          $element.attr('href', exoToolbarAdminEscape);
        }
        else {
          $element.find('.exo-toolbar-element-title').text(Drupal.t('Home'));
        }
        $element.removeClass('exo-toolbar-element-hidden');
      }
    }
  };

})(jQuery, Drupal, drupalSettings);
