(function ($, Drupal) {

  /**
   * Modal build behavior.
   */
  Drupal.behaviors.exoSplash = {
    doResolve: null,
    debug: false,

    init: function(context) {
      return new Promise((resolve, reject) => {
        this.doResolve = resolve;
      });
    },

    resolve: function () {
      if (typeof this.doResolve == 'function') {
        this.doResolve();
      }
    },

    attach: function(context) {
      const $splash = $('#exo-splash.active', context).once('exo.splash');
      if ($splash.length) {
        this.debug = $splash.hasClass('debug');
        $splash.each((index, element) => {
          if (window.sessionStorage) {
            const $element = $(element);
            $element.imagesLoaded(e => {
              const info = $element.data('exo-splash');
              const method = Drupal.Exo.toCamel('do-' + info);
              if (typeof this[method] === 'function') {
                this[method]($element);
              }
              else {
                this.resolve();
              }
            });
          }
        });
      }
      else {
        this.resolve();
      }
    },

    doSlideUpLeft: function($element:JQuery) {
      let $wrapper = $('.region.header, .region.region-header').first();
      const $branding = $('.block.branding:visible', $wrapper).first();
      if ($branding.length) {
        $wrapper = $branding;
      }

      const top = Math.round($wrapper.offset().top);
      const left = Math.round($wrapper.offset().left);
      const right = Math.round(Drupal.Exo.$window.width() - ($wrapper.outerWidth() + left));
      const bottom = Math.round(Drupal.Exo.$window.height() - ($wrapper.outerHeight() + top));
      const callback = (step, info) => {
        $wrapper.attr('data-exo-splash-step', step);
        if (step === 0) {
          $wrapper.removeClass('exo-splash-animate');
        }
      }
      const sequence = [];
      sequence.push({
        css: {
          top: top + 'px',
          bottom: bottom + 'px',
          paddingTop: $wrapper.css('padding-top'),
          paddingBottom: $wrapper.css('padding-bottom'),
        },
        callback: callback,
        resolve: true,
      });
      sequence.push({
        css: {
          left: left + 'px',
          right: right + 'px',
        },
        callback: callback,
      });
      sequence.push({
        css: {
          opacity: 0,
        },
        callback: callback,
      });
      $wrapper.addClass('exo-splash-animate');
      this.doAnimation(sequence, $element, callback);
    },

    doAnimation: function(sequence:Array<any>, $element:JQuery, callback?:Function) {
      const step = sequence.length;
      const info = sequence.shift();
      if (typeof info !== 'undefined') {
        if (info.resolve) {
          this.resolve();
        }
        if (info.callback) {
          info.callback(step, info);
        }
        $element.attr('data-exo-splash-step', step);
        const animate = () => {
          $element.css(info.css);
          $element.on(Drupal.Exo.transitionEvent + '.exo.splash', e => {
            if ($element.get(0) === e.currentTarget) {
              $element.off(Drupal.Exo.transitionEvent + '.exo.splash');
              setTimeout(() => {
                this.doAnimation(sequence, $element, callback);
              });
            }
          });
        }
        if (this.debug) {
          Drupal.Exo.debug('log', 'Splash: Upcoming Sequence', info);
          $element.on('click', e => {
            e.preventDefault();
            animate();
          });
        }
        else {
          animate();
        }
      }
      else {
        const finish = () => {
          if (callback) {
            callback(step, null);
          }
          $element.hide();
        }
        if (this.debug) {
          $element.on('click', e => {
            e.preventDefault();
            finish();
          });
        }
        else {
          finish();
        }
      }
    }
  }

  Drupal.Exo.addRevealWait(Drupal.behaviors.exoSplash.init(document.body));

})(jQuery, Drupal);
