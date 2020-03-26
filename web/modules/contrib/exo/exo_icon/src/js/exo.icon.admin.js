/**
 * @file
 * Module page behaviors.
 */

(function ($, Drupal, debounce) {

  'use strict';

  /**
   * Filters the eXo icon list by a text input search string.
   *
   * Text search input: input.exo-icon-filter-text
   * Target exo-icon: input.exo-icon-filter-text[data-exo-icon]
   * Source text: .exo-icon-filter-text.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.exoIconAdmin = {
    attach: function (context, settings) {
      var $input = $('input.exo-icon-filter-text').once('exo.icon');
      var $icons = $($input.attr('data-list'));
      var $columns;
      var searching = false;

      function filterList(e) {
        var query = $(e.target).val();
        // Case insensitive expression to find query at the beginning of a word.
        var re = new RegExp('\\b' + query, 'i');

        $columns.show();

        function showModuleRow(index, row) {
          var $row = $(row);
          var $sources = $row.find('.exo-icon-filter-text');
          var textMatch = $sources.text().search(re) !== -1;
          $row.closest('li').toggle(textMatch);
        }

        // Filter if the length of the query is at least 2 characters.
        if (query.length >= 2) {
          searching = true;
          $columns.each(showModuleRow);
        }
        else if (searching) {
          searching = false;
          $columns.show();
        }
      }

      function preventEnterKey(event) {
        if (event.which === 13) {
          event.preventDefault();
          event.stopPropagation();
        }
      }

      if ($input.length && $icons.length) {
        $columns = $icons.find('li');

        $input.on({
          keyup: debounce(filterList, 200),
          keydown: preventEnterKey
        });
      }
    }
  };

}(jQuery, Drupal, Drupal.debounce));
