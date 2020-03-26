class ExoModalToolbarDialogTypeModal extends ExoToolbarDialogTypeBase {
  protected exoModal:ExoModal;
  protected $element:JQuery;

  public build(ajax, response, status):Promise<void> {
    return new Promise((resolve, reject) => {
      super.build(ajax, response, status).then(() => {
        if (this.$element) {
          Drupal.detachBehaviors(this.$element.get(0), this.settings);
          this.$element.remove();
        }
        this.$element = $(response.data);
        this.$element.addClass(Drupal.ExoToolbarDialog.dialogItemContentClass);
        this.$element.appendTo('body');
        Drupal.attachBehaviors(this.$element.get(0), this.settings);

        Drupal.ExoModal.isReady().then(instances => {
          this.exoModal = Drupal.ExoModal.getInstance(this.settings.exo_modal_id);
          resolve();
        });
      });
    });
  }

  public show():this {
    super.show();
    this.exoToolbarItem.disableAside();
    this.exoModal.event('closing').on('exo.modal.dialog.type', (modal:ExoModal) => {
      this.exoToolbarDialogItem.hide();
    });
    setTimeout(() => {
      Drupal.ExoModal.getWrapper().css('z-index', this.exoToolbarItem.getRegion().zIndexGet('HOVER'));
      this.exoModal.open();
    });
    return this;
  }

  public hide():this {
    super.hide();
    this.exoToolbarItem.enableAside();
    this.exoModal.event('closing').off('exo.modal.dialog.type');
    this.exoModal.event('closed').on('exo.modal.dialog.type', (modal:ExoModal) => {
      Drupal.ExoModal.getWrapper().css('z-index');
      this.exoModal.event('closed').off('exo.modal.dialog.type');
    });
    this.exoModal.close();
    return this;
  }
}

Drupal.ExoToolbarDialogTypes.modal = ExoModalToolbarDialogTypeModal;
