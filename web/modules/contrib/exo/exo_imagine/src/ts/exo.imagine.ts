/**
 * @file
 * Global eXo Image javascript.
 */

(function ($, Drupal, drupalSettings) {

  class ExoImagine {

    protected settings = {
      webp: 1,
      animate: 1,
      blur: 1,
      bg: 0,
      visible: 1
    };
    protected $element:JQuery;
    protected $image:JQuery;
    protected $imageSources:JQuery;
    protected $previewPicture:JQuery;

    constructor(element, globalSettings:any) {
      this.$element = $(element);
      this.$image = this.$element.find('.exo-imagine-image');
      this.$imageSources = this.$element.find('.exo-imagine-image-picture source');
      this.$previewPicture = this.$element.find('.exo-imagine-preview-picture');
      const instanceSettings = JSON.parse(this.$element.attr('data-exo-imagine'));
      if (typeof globalSettings.defaults == 'object') {
        $.extend(this.settings, globalSettings.defaults);
      }
      if (typeof instanceSettings == 'object') {
        $.extend(this.settings, instanceSettings);
      }

      if (this.settings.visible) {
        Drupal.Exo.trackElementPosition(this.$element.get(0), $element => {
          Drupal.Exo.untrackElementPosition($element[0]);
          this.render();
        });
      }
      else {
        this.render();
      }
    }

    public render() {
      // Watch for load.
      this.$image.one('load', e => {
        this.$element.addClass('exo-imagine-loaded');
        if (this.settings.animate) {
          this.$previewPicture.one(Drupal.Exo.transitionEvent, e => {
            this.$previewPicture.remove();
          });
          this.$element.addClass('exo-imagine-animate');
        }
        else {
          this.$previewPicture.remove();
        }
      });

      // Swap in srcset.
      this.$imageSources.each((index, element) => {
        const $source = $(element);
        $source.attr('srcset', $source.data('srcset')).removeAttr('data-srcset');
      });
    }
  }

  Drupal.behaviors.exoImagine = {
    instances: [],
    supportsWebP: null,

    attach: function(context) {
      if (typeof drupalSettings.exoImagine !== 'undefined') {
        $('.exo-imagine').once('exo.imagine').each((index, element) => {
          this.instances.push(new ExoImagine(element, drupalSettings.exoImagine));
        });
        // Drupal.ExoImagine.attach(context, drupalSettings.exoImagine);
      }
    }
  }

}(jQuery, Drupal, drupalSettings));
