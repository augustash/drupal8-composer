class ExoToolbarDialogItem {
  protected dialogController:ExoToolbarDialog;
  protected toolbar:ExoToolbar;
  protected item:ExoToolbarItem;
  protected handler:ExoToolbarDialogTypeInterface;
  protected type:string;
  protected shown:boolean = false;
  protected built:boolean = false;

  constructor(dialog:ExoToolbarDialog, toolbar:ExoToolbar, item:ExoToolbarItem) {
    this.dialogController = dialog;
    this.toolbar = toolbar;
    this.item = item;
    this.type = item.get('dialog_type');

    if (typeof Drupal.ExoToolbarDialogTypes[this.type] !== 'undefined') {
      this.handler = new Drupal.ExoToolbarDialogTypes[this.type](this);
    }

    this.bindAjax();
    this.item.getElement().find('.exo-toolbar-element').on('click.exo.toolbar.dialog', e => {
      e.preventDefault();
      this.toggle();
    });
  }

  protected bindAjax() {
    const $element = this.item.getElement();
    const href = Drupal.url('api/exo/toolbar/dialog/' + this.item.getId());

    const elementSettings = {
      base: $element.attr('id'),
      element: $element[0],
      url: href,
      event: 'exo_toolbar_dialog_open'
    };

    Drupal.ajax(elementSettings);
  }

  public getType():string {
    return this.type;
  }

  public getItem():ExoToolbarItem {
    return this.item;
  }

  public getItemId():string {
    return this.item.getId();
  }

  public isShown():boolean {
    return this.shown === true;
  }

  public isBuilt():boolean {
    return this.built === true;
  }

  protected toggle() {
    if (this.isShown()) {
      this.hide();
    }
    else {
      this.show();
    }
  }

  public build(ajax, response, status):this {
    this.built = true;
    if (this.handler) {
      this.handler.build(ajax, response, status).then(() => {
        this.handler.show();
      })
    }
    return this;
  }

  public show() {
    if (!this.isShown()) {
      // Close current dialogs.
      this.dialogController.hideAll();
      // Small timeout to allow hidden events to show/hide as needed.
      setTimeout(() => {
        // Toggle regions as needed.
        if (typeof Drupal.ExoToolbarToggle !== 'undefined') {
          Drupal.ExoToolbarToggle.hideAllExceptEdge(this.item.getRegion().getEdge());
          Drupal.ExoToolbarToggle.hideAllAfterRegion(this.item.getRegion().getId());
        }
        this.shown = true;
        this.item.getElement().addClass('exo-toolbar-item-active');
        if (!this.isBuilt() || this.handler.shouldRebuild()) {
          this.item.getElement().trigger('exo_toolbar_dialog_open');
        }
        else {
          if (this.handler) {
            this.handler.show();
          }
        }
        if (this.handler && this.handler.useShadow()) {
          Drupal.Exo.showShadow({
            opacity: 0.3,
            onClick: e => {
              this.hide();
            }
          });
        }
      });
    }
  }

  public hide() {
    if (this.isShown()) {
      this.shown = false;
      this.item.getElement().removeClass('exo-toolbar-item-active');
      if (this.handler) {
        this.handler.hide();
      }
      if (this.handler && this.handler.useShadow()) {
        Drupal.Exo.hideShadow();
      }
    }
  }
}
