/**
 * @file
 * Select as links javascript.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.exoElementOptions = {
    attach: function (context) {
      $(context).find('div.exo-element-options').once('exo.element.options').each((index, element) => {
        const $wrapper = $(element);
        if ($wrapper.hasClass('exo-form-radios-js') || $wrapper.hasClass('exo-form-checkboxes-js')) {
          // Ignore if handled by exo-form.
          return;
        }
        const $items = $wrapper.find('.js-form-item');
        $items.each((index, element) => {
          const $item = $(element);
          const isRadio = $item.hasClass('js-form-type-radio');
          const $button = $item.find('label.option');
          const $input = $item.find('input');
          let active = $input.prop('checked');
          if (active) {
            $item.addClass('active');
          }

          $input.on('change.exo.element.options', (e) => {
            if (isRadio) {
              $items.removeClass('active');
              $item.addClass('active');
            }
            else {
              if ($input.prop('checked')) {
                $item.addClass('active');
              }
              else {
                $item.removeClass('active');
              }
            }
          }).on('focus.exo.element.options', (e) => {
            $item.addClass('focused');
          }).on('blur.exo.element.options', (e) => {
            $item.removeClass('focused');
          });
        });
      });
    }
  };

}(jQuery, Drupal));
