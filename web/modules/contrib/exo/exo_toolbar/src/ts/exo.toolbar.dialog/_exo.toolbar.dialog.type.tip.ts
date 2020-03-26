class ExoToolbarDialogTypeTip extends ExoToolbarDialogTypeBase {
  protected $element:JQuery;
  protected shadow:boolean = true;

  public build(ajax, response, status):Promise<void> {
    return new Promise((resolve, reject) => {
      super.build(ajax, response, status).then(() => {
        if (this.$element) {
          Drupal.detachBehaviors(this.$element.get(0), this.settings);
          this.$element.remove();
        }
        this.$element = $(response.data);
        this.$element.addClass(Drupal.ExoToolbarDialog.dialogItemContentClass);
        this.$element.appendTo(this.exoToolbarItem.getElement().find('.exo-toolbar-item-aside'));
        Drupal.attachBehaviors(this.$element.get(0), this.settings);
        this.exoToolbarItem.positionAside();
        resolve();
      });
    });
  }

  public show():this {
    super.show();
    if (typeof this.settings.animate_in !== 'undefined') {
      this.$element.addClass('exo-animate-' + this.settings.animate_in).show();
      this.$element.one(Drupal.Exo.animationEvent, e => {
        this.$element.removeClass('exo-animate-' + this.settings.animate_in).show();
      });
    }
    else {
      this.$element.show();
    }

    $(document).on('click.exo.toolbar.dialog.type.tip', e => {
      if (!$(e.target).closest(this.exoToolbarDialogItem.getItem().getElement()).length) {
        this.exoToolbarDialogItem.hide();
      }
    });

    // Hide if any region is toggled.
    if (Drupal.ExoToolbarToggle) {
      Drupal.ExoToolbarToggle.event('show').on('dialog.type.tip', (toggle) => {
        this.exoToolbarDialogItem.hide();
      });
    }
    return this;
  }

  public hide():this {
    super.hide();
    if (typeof this.settings.animate_out !== 'undefined') {
      this.$element.addClass('exo-animate-' + this.settings.animate_out);
      this.$element.one(Drupal.Exo.animationEvent, e => {
        this.$element.removeClass('exo-animate-' + this.settings.animate_out).hide();
      });
    }
    else {
      this.$element.hide();
    }

    $(document).off('click.exo.toolbar.dialog.type.tip');
    if (Drupal.ExoToolbarToggle) {
      Drupal.ExoToolbarToggle.event('show').off('dialog.type.tip');
    }
    return this;
  }
}

Drupal.ExoToolbarDialogTypes.tip = ExoToolbarDialogTypeTip;
