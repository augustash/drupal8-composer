/**
 * @file
 * Range slider behavior.
 */
(function ($, Drupal) {

  'use strict';

  /**
   * Process ranges_slider elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.rangeSlider = {
    sliders: {},

    attach: function attach(context, settings) {
      if (settings.exo && settings.exo.exoRadiosSlider) {
        for (const id in settings.exo.exoRadiosSlider) {
          if (settings.exo.exoRadiosSlider.hasOwnProperty(id)) {
            const config = settings.exo.exoRadiosSlider[id];
            const options = config.options;
            $('#exo-radios-slider-' + id).once('exo.element').each((index, element) => {
              const $input = $(element).find('select');
              const inputVal = $input.val();
              $input.hide();
              if (inputVal) {
                const inputKey = Object.keys(options).find(key => options[key].key === inputVal);
                if (typeof inputKey !== 'undefined') {
                  config.start = inputKey;
                }
              }
              const sliderContainer = document.getElementById('exo-radios-slider-slide-' + id);
              const sliderOptions = {
                start: config.start,
                step: 1,
                range: {
                  'min': 0,
                  'max': Object.keys(options).length - 1,
                }
              };
              if (config.tooltips === true) {
                sliderOptions['tooltips'] = {
                  to (value: number) {
                    return options[Object.keys(options)[Math.round(value)]]['value'];
                  },
                };
              }
              if (config.pips === true) {
                sliderOptions['pips'] = {
                  mode: 'steps',
                  density: Object.keys(options).length - 1,
                  format: {
                    to (value: number) {
                      return options[Object.keys(options)[Math.round(value)]]['value'];
                    },
                  }
                };
              }
              this.sliders[id] = noUiSlider.create(sliderContainer, sliderOptions);
              this.sliders[id].on('update', (values, handle) => {
                const value = options[Object.keys(options)[Math.round(values[handle])]]['key'];
                if (String(value) !== String($input.val())) {
                  $input.val(value).trigger('change');
                }
              });
            });
          }
        }
      }
    },

    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        for (const id in this.sliders) {
          if (this.sliders.hasOwnProperty(id)) {
            const slider = this.sliders[id];
            $('#exo-radios-slider-' + id, context).findOnce('exo.element').each((index, element) => {
              slider.destroy();
              delete this.sliders[id];
            });
          }
        }
      }
    }
  };

})(jQuery, Drupal);
