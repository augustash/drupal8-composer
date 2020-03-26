/**
 * @file exo.entity-browser.iframe.selection.js
 *
 * Propagates selected entities from exo_iframe display.
 */

document.getElementsByTagName('BODY')[0]['style'].display = "none";

(function (drupalSettings) {

  'use strict';

  // We need to access parent window, get it's jquery and find correct iFrame
  // element to trigger event on.
  parent.jQuery(parent.document)
    .find('iframe[data-uuid*=' + drupalSettings.entity_browser.exo_iframe.uuid + ']').hide().prev().hide()
    .parent().find('a[data-uuid*=' + drupalSettings.entity_browser.exo_iframe.uuid + ']')
    .trigger('entities-selected', [drupalSettings.entity_browser.exo_iframe.uuid, drupalSettings.entity_browser.exo_iframe.entities])
    .unbind('entities-selected').show();

}(drupalSettings));
