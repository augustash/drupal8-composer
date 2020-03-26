/**
 * @file
 * Select-All Button functionality.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.exoFormViewsBulkOperations = {
    attach: function (context, settings) {
      $('.vbo-view-form').once('exo.form.vbo-init').each(function () {
        const $vboForm = $(this);
        const $primarySelectAll = $('.vbo-select-all', $vboForm);
        const $tableSelectAll = $('th.select-all > input[type="checkbox"]', $vboForm);
        if ($primarySelectAll.length) {
          Drupal.behaviors.exoFormCheckbox.wrap($primarySelectAll);
          $primarySelectAll.on('change', function (event) {
            $vboForm.find('.views-field-views-bulk-operations-bulk-form input[type="checkbox"]').trigger('change');
          });
        }
        if ($tableSelectAll.length) {
          Drupal.behaviors.exoFormCheckbox.wrap($tableSelectAll);
        }
      });
    }
  };

})(jQuery, Drupal);
