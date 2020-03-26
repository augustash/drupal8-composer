/**
 * @file exo.entity-browser.modal.selection.js
 *
 * Propagates selected entities from exo_modal display.
 */

document.getElementsByTagName('BODY')[0]['style'].display = "none";

(function (drupalSettings) {

  'use strict';

  // We need to access parent window, get it's jquery and find correct exo_modal
  // element to trigger event on.
  parent.jQuery(parent.document)
    .find(':input[data-uuid*=' + drupalSettings.entity_browser.exo_modal.uuid + ']')
    .trigger('entities-selected', [drupalSettings.entity_browser.exo_modal.uuid, drupalSettings.entity_browser.exo_modal.entities])
    .unbind('entities-selected').show();

  // This is a silly solution, but works fo now. We should close the exo_modal
  // via ajax commands.
  parent.jQuery(parent.document).find('.exo-modal-button-close').trigger('mousedown').trigger('mouseup').trigger('click');

}(drupalSettings));
