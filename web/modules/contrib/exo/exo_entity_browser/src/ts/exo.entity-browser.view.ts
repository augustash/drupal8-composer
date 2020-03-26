/**
 * @file
 * Defines the behavior of the media entity browser view.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var exoEntityBrowserSelected = [];

  /**
   * Attaches the behavior of the media entity browser view.
   */
  Drupal.behaviors.exoEntityBrowserView = {
    attach: function (context, settings) {
      var $form = $('.entity-browser-form');
      $form.addClass('exo-entity-browser-view exo-no-animations');
      var uuid = $form.attr('data-entity-browser-uuid');

      // We need some magic to help simplify the media upload page.
      let $mediaEditForm = $('#ief-dropzone-upload');
      if ($mediaEditForm.length) {
        if ($mediaEditForm.children().length) {
          $form.find('.js-form-type-dropzonejs').hide();
          $('#edit-actions').show();
        }
        else {
          $mediaEditForm.hide();
          $('#edit-actions').hide();
        }

        if (!Drupal.views) {
          return;
        }
      }

      var views_instance = Drupal.views.instances[_.keys(Drupal.views.instances)[0]];
      if (uuid && views_instance.$view.once('exo.entity-browser.view')) {
        var cardinality = parent && parent.drupalSettings && parent.drupalSettings.entity_browser && parent.drupalSettings.entity_browser[uuid] ? parent.drupalSettings.entity_browser[uuid].cardinality : -1;
        var autoselect = drupalSettings.entity_browser_widget && drupalSettings.entity_browser_widget.auto_select;

        // If "auto_select" functionality is enabled, then selection column is
        // hidden and click on row will actually add element into selection
        // display over javascript event. Currently only multistep display
        // supports that functionality.
        if (autoselect) {
          var selection_cells = views_instance.$view.find('.views-field-entity-browser-select').show().parent().once('unbind-register-row-click');
          selection_cells.off('click').once('register-row-click');

          var $actions = $form.find('.entities-list-actions');
          if (!$actions.length) {
            $actions = $('<div class="entities-list-actions" />').appendTo($form);
          }
          var $buttons = $form.find('.entity-browser-use-selected, .entity-browser-show-selection').once('exo.entity-browser.view')
          if ($buttons.length) {
            $actions.empty();
            $buttons.appendTo($actions);
          }
        }

        // Disable all currently selected entities.
        if (parent && parent.drupalSettings && parent.drupalSettings.entity_browser && parent.drupalSettings.entity_browser[uuid]) {
          if (parent.drupalSettings.entity_browser[uuid].entities) {
            parent.drupalSettings.entity_browser[uuid].entities.forEach(function (item) {
              var $input = views_instance.$view.find('input[value="' + item + '"]');
              if ($input.length) {
                $input.prop('disabled', true);
                $input.closest('.views-field-entity-browser-select').parent().addClass('disabled checked');
              }
            });
          }
        }

        // Check for existing selections and set them as checked.
        exoEntityBrowserSelected.forEach(function (item) {
          var $input = views_instance.$view.find('input[value="' + item + '"]');
          if ($input.length) {
            $input.closest('.views-field-entity-browser-select').parent().addClass('checked');
            $input.prop('checked', true);
          }
        });

        views_instance.$view.find('.views-field-entity-browser-select').parent().once('exo.entity-browser.view').each(function () {
          var $row = $(this);
          var $column = $row.find('.views-field-entity-browser-select');
          $('<div class="exo-entity-browser-check"><svg class="exo-entity-browser-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="exo-entity-browser-checkmark--circle" cx="26" cy="26" r="25" fill="none"/><path class="exo-entity-browser-checkmark--check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg></div>').appendTo($column);
          if ($column.find('input').prop('checked')) {
            $row.addClass('checked');
          }
        })
        .on('click.exo.entity-browser.view', function (e) {
          e.preventDefault();
          var selector = '.views-field-entity-browser-select input';
          var $row = $(this);
          var $input = $row.find(selector);
          var checked = !$input.prop('checked');
          $input.prop('checked', checked);
          if (checked) {
            e.preventDefault();
            if (cardinality > 0) {
              var $rowChecked = $('tr.checked');
              if ($rowChecked.length >= cardinality) {
                $rowChecked.first().removeClass('checked').addClass('unchecked');
                $rowChecked.first().find(selector).prop('disabled', false).prop('checked', false);
              }
            }
            if (autoselect) {
              $input.prop('disabled', true);
            }
            $row.removeClass('unchecked').addClass('checked');
            exoEntityBrowserSelected.push($input.val());
          }
          else {
            if (autoselect) {
              $input.prop('disabled', false);
            }
            $row.removeClass('checked').addClass('unchecked');
            exoEntityBrowserSelected = exoEntityBrowserSelected.filter(item => {
              return item !== $input.val();
            });
          }

          // If "auto_select" functionality is enabled, then selection column is
          // hidden and click on row will actually add element into selection
          // display over javascript event. Currently only multistep display
          // supports that functionality.
          if (autoselect) {
            if (checked) {
              $row.parents('form')
                .find('.entities-list')
                .trigger('add-entities', [[$input.val()]]);
            }
            else {
              // Contrib does not correctly handle removal of entities set to
              // display as views. This is an ugly workaround.
              var parts = String($input.val()).split(':');
              $row.parents('form').find('.entities-list').find('[data-entity-id="' + parts[1] + '"] .entity-browser-remove-selected-entity').trigger('click');
            }
          }
        });

        // If a user removed a selected entity from the entity list, we need to
        // make sure we uncheck the item in the list.
        $form.find('.entities-list .entity-browser-remove-selected-entity').once('exo.entity-browser.view').on('click.exo.entity-browser.view', function (e) {
          var entityId = $(this).attr('data-remove-entity').replace(/^\D+/g, '');
          var $input = views_instance.$view.find('input[value$="' + entityId + '"]');
          if ($input.length) {
            $input.prop('checked', false);
            $input.closest('.views-field-entity-browser-select').parent().removeClass('checked');
            exoEntityBrowserSelected = exoEntityBrowserSelected.filter(item => {
              return item !== $input.val();
            });
          }
        });

        setTimeout(() => {
          $form.removeClass('exo-no-animations');
        }, 300);

        if (views_instance.$view.hasClass('exo-entity-browser-grid')) {
          this.attachGrid(views_instance);
        }
      }
    },

    attachGrid: function (views_instance) {
      views_instance.$view.find('.views-row').once('exo-entity-browser-view').each(function () {
        var $row = $(this);
        var $container = $('<div class="exo-entity-browser-info" />').appendTo($row);
        $('.views-field', $row).each(function (e) {
          if (!$(this).find('img, input').length) {
            $(this).appendTo($container);
          }
        });
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
