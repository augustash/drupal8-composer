(function ($, _, Drupal, drupalSettings) {

  class ExoToolbarSort {
    protected toolbar:ExoToolbar;
    protected $sortables:JQuery;
    protected $sections:JQuery;

    public attach(toolbar:ExoToolbar) {
      this.toolbar = toolbar;
      this.$sortables = this.getSortables(toolbar);
      this.$sections = toolbar.getSections().elements();

      let section = this.toolbar.getSection('left:top');

      this.$sortables.addClass('exo-toolbar-sortable');
      this.$sections.sortable({
        items: '.exo-toolbar-sortable',
        connectWith: '.exo-toolbar-section',
        placeholder: 'exo-toolbar-sort-placeholder',
        forcePlaceholderSize: true,
        tolerance: 'pointer',
        helper: 'clone',
        forceHelperSize: true,
        appendTo: $('body'),
        opacity: 0.9,
        scroll: false,
        cursor: 'move',
        start: (event, ui) => this.onSortableStart(event, ui),
        stop:  (event, ui) => this.onSortableStop(event, ui),
        update: (event, ui) => this.onSortableUpdate(event, ui)
      });
      this.$sortables.disableSelection();
    }

    public getSortables(toolbar:ExoToolbar):JQuery {
      let selectors = [];
      toolbar.getItems().each((item:ExoToolbarItem) => {
        if (item.allowSort()) {
          selectors.push(item.getSelector());
        }
      });
      return $(selectors.join(', '));
    }

    protected onSortableStart(event, ui) {
      // Use create button as basis for min-width/min-height of section.
      this.$sections.each((index, element) => {
        const $create = $(element).find('.exo-toolbar-create');
        $(element).css({
          minHeight: $create.outerHeight(),
          minWidth: $create.outerWidth(),
        });
      });
      this.toolbar.getElement().addClass('exo-toolbar-sorting');
      this.toolbar.disableAsides();
    }

    protected onSortableStop(event, ui) {
      this.$sections.css({minHeight: '', minWidth: ''});
      this.toolbar.getElement().removeClass('exo-toolbar-sorting');
      this.toolbar.enableAsides();
      this.toolbar.positionAsides();
    }

    protected onSortableUpdate(event, ui) {
      const targetSectionId = $(event.target).data('exo-section-id');
      const item = this.toolbar.getItem(ui.item.data('exo-item-id'));
      const section = this.toolbar.getSection(ui.item.closest('.exo-toolbar-section').data('exo-section-id'));
      // This is called twice. Once for the section being left, and once for
      // the section being moved to.
      if (item && section && section.getId() === targetSectionId) {
        // Update item section and region.
        item.setSectionId(section.getBaseId());
        item.setRegionId(section.getRegionId());
        section.orderItems();
        // Save updates to all items within the section.
        const items = section.getItems();
        $.ajax({
          url: Drupal.url('api/exo/toolbar/items/update'),
          type: 'POST',
          data: JSON.stringify(items.getData()),
          dataType: 'json',
          success: function (results) {
            // Success
          }
        });
      }
    }

  }

  const exoToolbarSort = new ExoToolbarSort();

  Drupal.behaviors.exoToolbarSort = {
    attach: function(context) {
      if (Drupal.ExoToolbar.isAdminMode()) {
        Drupal.ExoToolbar.isReady().then(instances => {
          instances.each((toolbar:ExoToolbar) => {
            exoToolbarSort.attach(toolbar);
          });
        });
      }
    }
  }

})(jQuery, _, Drupal, drupalSettings);
