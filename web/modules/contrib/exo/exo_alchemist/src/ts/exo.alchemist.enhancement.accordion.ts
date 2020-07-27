(function ($, Drupal, drupalSettings) {

  class ExoAlchemistEnhancementAccordion {

    protected $wrapper:JQuery;
    protected $items:JQuery;
    protected $triggers:JQuery;
    protected $contents:JQuery;
    protected $current:JQuery;
    protected speed:number = 5000;
    protected interval:number;

    constructor($wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.$items = $wrapper.find('.exo-enhancement--accordion-item');
      this.$triggers = $wrapper.find('.exo-enhancement--accordion-trigger');
      this.$contents = $wrapper.find('.exo-enhancement--accordion-content');
      this.$contents.hide();
      if (this.isLayoutBuilder()) {
        Drupal.ExoAlchemistAdmin.lockNestedFields(this.$items);
        $(document).off('exoComponentFieldEditActive.exo.alchemist.enhancement.accordion').on('exoComponentFieldEditActive.exo.alchemist.enhancement.accordion', (e, element) => {
          const $element = $(element);
          if ($element.hasClass('exo-enhancement--accordion-item')) {
            this.show($element, false);
            Drupal.ExoAlchemistAdmin.sizeFieldOverlay($element);
            Drupal.ExoAlchemistAdmin.sizeTarget($element);
          }
        });
        // const $active = this.$items.find('.exo-component-field-edit-active');
        // if ($active.length) {
        //   this.show($active, false);
        // }
        // else {
        //   this.show(this.$triggers.first(), false);
        // }
      }
      this.show(this.$triggers.first(), false);
      this.$triggers.on('click.exo.alchemist.enhancement.accordion', e => {
        e.preventDefault();
        this.show($(e.target));
      });
    }

    public show($trigger:JQuery, animate?:boolean):void {
      animate = typeof animate !== 'undefined' ? animate : true;
      const $item = $trigger.closest('.exo-enhancement--accordion-item');
      const $contents = $item.find('.exo-enhancement--accordion-content');
      if ($contents.length) {
        const current = $item.hasClass('show');
        const $shown = this.$items.filter('.show');
        const $shownContent = $shown.find('.exo-enhancement--accordion-content');
        if (this.isLayoutBuilder()) {
          if (current) {
            return;
          }
          Drupal.ExoAlchemistAdmin.lockNestedFields($shown);
        }
        $shown.removeClass('show');
        if (animate) {
          $shownContent.slideToggle(350, 'swing');
        }
        else {
          $shownContent.hide();
        }
        if (!current) {
          $item.addClass('show');
          if (animate) {
            $contents.slideToggle(350, 'swing', () => {
              if (this.isLayoutBuilder()) {
                Drupal.ExoAlchemistAdmin.unlockNestedFields($item);
              }
            });
          }
          else {
            $contents.show();
            if (this.isLayoutBuilder()) {
              Drupal.ExoAlchemistAdmin.unlockNestedFields($item);
            }
          }
        }
      }
    }

    protected isLayoutBuilder() {
      return Drupal.ExoAlchemistAdmin && Drupal.ExoAlchemistAdmin.isLayoutBuilder();
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementAccordion = {
    attach: function(context) {
      $('.exo-enhancement--accordion-wrapper').once('exo.alchemist.enhancement').each(function () {
        new ExoAlchemistEnhancementAccordion($(this));
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
