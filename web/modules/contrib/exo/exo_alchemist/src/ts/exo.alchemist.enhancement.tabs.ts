(function ($, Drupal, drupalSettings) {

  class ExoAlchemistEnhancementTabs {

    protected $wrapper:JQuery;
    protected $triggers:JQuery;
    protected $contents:JQuery;
    protected id:string = '';

    constructor($wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.id = $wrapper.data('ee--tabs-id');
      this.$triggers = $wrapper.find('.ee--tabs-trigger[data-ee--tabs-id="' + this.id + '"]');
      this.$contents = $wrapper.find('.ee--tabs-content[data-ee--tabs-id="' + this.id + '"]');
      this.$contents.hide();
      if (this.isLayoutBuilder()) {
        Drupal.ExoAlchemistAdmin.lockNestedFields(this.$triggers);
        $(document).off('exoComponentFieldEditActive.exo.alchemist.enhancement.tabs').on('exoComponentFieldEditActive.exo.alchemist.enhancement.tabs', (e, element) => {
          const $element = $(element);
          const $content = $element.closest('.ee--tabs-content');
          if ($content.length) {
            const id = $content.data('ee--tab-id');
            const $trigger = this.$triggers.filter('[data-ee--tab-id="' + id + '"]');
            this.show($trigger);
            Drupal.ExoAlchemistAdmin.sizeFieldOverlay($element);
            Drupal.ExoAlchemistAdmin.sizeTarget($element);
          }
        });
      }
      this.show(this.$triggers.first());
      this.$triggers.on('click.exo.alchemist.enhancement.tabs', e => {
        e.preventDefault();
        this.show($(e.currentTarget));
      });
    }

    public show($trigger:JQuery):void {
      const id = $trigger.data('ee--tab-id');
      this.$triggers.removeClass('active');
      $trigger.addClass('active');
      this.$contents.removeClass('active').hide();
      this.$contents.filter('[data-ee--tab-id="' + id + '"]').addClass('active').show();
      if (this.isLayoutBuilder()) {
        Drupal.ExoAlchemistAdmin.lockNestedFields(this.$triggers);
        Drupal.ExoAlchemistAdmin.unlockNestedFields($trigger);
      }
    }

    protected isLayoutBuilder() {
      return Drupal.ExoAlchemistAdmin && Drupal.ExoAlchemistAdmin.isLayoutBuilder();
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementTabs = {
    attach: function(context) {
      $('.ee--tabs-wrapper').once('exo.alchemist.enhancement').each(function () {
        new ExoAlchemistEnhancementTabs($(this));
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
