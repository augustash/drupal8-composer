(function ($, _, Drupal, drupalSettings, displace) {

  class ExoFixed extends ExoData {
    protected $wrapper:JQuery;
    protected $element:JQuery;
    protected offset:{top: number; left: number;};
    protected floatOffset:number;
    protected floatStart:number = 0;
    protected floatEnd:number = 0;
    protected themeStart:number = 0;
    protected themeEnd:number = 0;
    protected width:number;
    protected height:number;
    protected fixed:boolean = false;
    protected themed:boolean = false;
    protected lastScrollTop:number = 0;
    protected lastDirection:string;
    protected type:string;

    constructor(id:string, $wrapper:JQuery) {
      super(id);
      this.$wrapper = $wrapper;
    }

    public build(data):Promise<ExoSettingsGroupInterface> {
      return new Promise((resolve, reject) => {
        super.build(data).then(data => {
          if (data !== null) {
            this.type = Drupal.Exo.isMobile() ? 'scroll' : this.get('type');
            this.lastDirection = this.type === 'scroll' ? 'up' : 'down';
            this.$element = this.$wrapper.find('.exo-fixed-element');
            this.bind();

            // When we start display from mid page we do not want any animations
            // happening.
            this.$wrapper.addClass('exo-fixed-no-animations');
            this.resize();
            this.onScroll();
            setTimeout(() => {
              this.$wrapper.removeClass('exo-fixed-no-animations');
            }, 10);
          }
          resolve(data);
        }, reject);
      });
    }

    protected bind() {
      // Call on scroll.
      const onScroll = _.throttle(() => {
        this.onScroll();
      }, 10);
      Drupal.Exo.$window.on('scroll.exo.fixed', e => {
        onScroll();
      });

      // Let Drupal handling resizing event.
      if (!Drupal.Exo.isMobile()) {
        Drupal.Exo.addOnResize('exo.fixed.' + this.getId(), () => {
          this.$wrapper.addClass('exo-fixed-no-animations');
          this.resize();
          this.onScroll();
          setTimeout(() => {
            this.$wrapper.removeClass('exo-fixed-no-animations');
          }, 10);
        });
      }
    }

    protected resize() {
      this.reset();
      this.calcSize();
      this.setSize();
    }

    protected reset() {
      this.fixed = false;
      this.themed = false;
      this.$wrapper.removeAttr('style');
      this.$element.removeAttr('style');
      this.$element.removeClass('exo-fixed-float exo-fixed-hide exo-fixed-theme');
    }

    protected calcSize() {
      this.offset = this.$element.offset();
      this.floatOffset = 0;
      $('.exo-fixed').each((index, element) => {
        if (element === this.$wrapper.get(0)) {
          return;
        }
        if ($(element).offset().top < this.offset.top) {
          this.floatOffset += $(element).height();
        }
      });
      this.width = Math.min(this.$element.outerWidth(), Drupal.Exo.$window.width());
      this.height = this.type === 'sticky' ? this.$element.parent().outerHeight() : this.$element.outerHeight();
      this.floatStart = Math.round(this.offset.top - this.floatOffset - displace.offsets.top);
      this.floatStart = this.floatStart >= 0 ? this.floatStart : 0;
      // Settings to -1 means it will continue to be floated. This will only
      // apply to items that are flush to the top.
      this.floatEnd = this.floatStart === 0 ? -1 : this.floatStart;
      this.themeStart = Math.round(this.floatStart + this.height);
      this.themeEnd = Math.round(this.floatEnd + this.height);
      if (this.type === 'scroll') {
        this.floatStart = this.floatEnd + this.height;
        this.themeStart = this.floatStart;
        this.themeEnd = this.floatStart + this.height;
      }
    }

    protected setSize() {
      this.$wrapper.css({width: this.width, height: this.height});
    }

    protected onScroll() {
      var scrollTop = Math.max(Drupal.Exo.$window.scrollTop(), 0);
      var direction = scrollTop > this.lastScrollTop ? 'down' : 'up';
      if (Math.abs(this.lastScrollTop - scrollTop) > 50) {
        this.lastDirection = direction;
        this.lastScrollTop = scrollTop;
      }

      if (this.themed === false && direction === 'down' && scrollTop >= this.themeStart) {
        this.themed = true;
        this.$element.addClass('exo-fixed-theme');
      }
      else if (this.themed === true && direction === 'up' && scrollTop <= this.themeEnd) {
        this.themed = false;
        this.$element.removeClass('exo-fixed-theme');
      }

      if (this.type === 'scroll') {
        if (this.lastDirection === 'down') {
          this.$element.addClass('exo-fixed-hide');
        }
        else {
          this.$element.removeClass('exo-fixed-no-animations exo-fixed-hide');
        }
      }

      if (this.lastDirection === 'down' && scrollTop > this.floatStart) {
        if (this.fixed === false) {
          this.doFloat();
        }
      }
      else if (this.lastDirection === 'up' && this.fixed === true && scrollTop <= this.floatEnd) {
        this.unFloat();
      }
    }

    protected doFloat() {
      this.fixed = true;
      if (this.type === 'scroll') {
        this.$element.addClass('exo-fixed-no-animations exo-fixed-hide');
      }
      if (this.type === 'sticky') {
        this.$element.css({
          position: 'sticky',
          top: this.floatOffset + displace.offsets.top,
        });
      }
      else {
        this.$element.css({
          position: 'fixed',
          marginLeft: (this.offset.left - displace.offsets.left),
          marginRight: (this.offset.left - displace.offsets.right),
          maxWidth: this.width,
          top: this.floatOffset + displace.offsets.top,
          left: displace.offsets.left,
          right: displace.offsets.right
        });
      }
      this.$element.addClass('exo-fixed-float');
      // this.$element.attr('data-offset-top', '');
      // displace.calculateOffset('top');
    }

    protected unFloat() {
      this.reset();
      this.setSize();
      this.$element.removeClass('exo-fixed-float');
      // this.$element.removeAttr('data-offset-top');
      // displace.calculateOffset('top');
    }
  }

  /**
   * Fixed build behavior.
   */
  Drupal.behaviors.exoFixed = {
    ready: false,

    attach: function(context) {
      if (typeof drupalSettings.exoFixed !== 'undefined' && typeof drupalSettings.exoFixed.elements !== 'undefined') {
        let self = this;
        if (self.ready === false) {
          Drupal.Exo.event('ready').on('exo.fixed', function () {
            self.ready = true;
            self.build();
          });
        }
        else {
          self.build();
        }
      }
    },

    build: function () {
      const data = [];
      const sortByWeight = function(a, b) {
        var top1 = a.top;
        var top2 = b.top;
        return ((top1 < top2) ? -1 : ((top1 > top2) ? 1 : 0));
      }
      for (const elementId in drupalSettings.exoFixed.elements) {
        if (drupalSettings.exoFixed.elements.hasOwnProperty(elementId)) {
          const settings = drupalSettings.exoFixed.elements[elementId];
          if (settings.hasOwnProperty('selector')) {
            let $element = $(settings.selector).first().once('exo.fixed');
            if ($element.length) {
              data.push({
                id: elementId,
                $element: $element,
                settings: settings,
                top: $element.offset().top,
              });
            }
          }
        }
      }
      if (data.length) {
        data.sort(sortByWeight);
        data.forEach((element) => {
          element.$element.imagesLoaded(() => {
            new ExoFixed(element.id, element.$element).build(element.settings);
          });
        });
      }
    }
  }

})(jQuery, _, Drupal, drupalSettings, Drupal.displace);
