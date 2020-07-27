(function ($, Drupal) {

  class ExoAlchemistEnhancementRotator {

    protected $wrapper:JQuery;
    protected $items:JQuery;
    protected $current:JQuery;
    protected speed:number = 5000;
    protected interval:number;

    constructor($wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.$items = $wrapper.find('.exo-enhancement--rotator-item');
      if (this.$items.length > 1) {
        this.$current = this.$items.first();
        this.$items.hide();
        this.$current.show();
        this.interval = setInterval(() => {
          this.cycle();
        }, this.$wrapper.data('rotator-speed') || this.speed);
      }
    }

    public getNext():JQuery {
      let $next = this.$current.next();
      if ($next.length === 0) {
        $next = this.$items.first();
      }
      return $next;
    }

    public cycle():void {
      const $next = this.getNext();
      $next.css('z-index', 1).show();
      this.$current.css('z-index', 2).fadeOut(1000, 'swing');
      this.$current = $next;
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementRotator = {
    attach: function(context) {
      $('.exo-enhancement--rotator-wrapper').once('exo.alchemist.enhancement').each(function () {
        new ExoAlchemistEnhancementRotator($(this));
      });
    }
  }

})(jQuery, Drupal);
