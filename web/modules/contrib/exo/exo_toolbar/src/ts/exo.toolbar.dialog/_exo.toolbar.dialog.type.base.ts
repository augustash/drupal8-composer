abstract class ExoToolbarDialogTypeBase implements ExoToolbarDialogTypeInterface {
  protected exoToolbarDialogItem:ExoToolbarDialogItem;
  protected exoToolbarItem:ExoToolbarItem;
  protected rebuild:boolean = false;
  protected shadow:boolean = false;
  protected settings:any;

  constructor(exoToolbarDialogItem:ExoToolbarDialogItem) {
    this.exoToolbarDialogItem = exoToolbarDialogItem;
  }

  public build(ajax, response, status):Promise<void> {
    return new Promise((resolve, reject) => {
      this.exoToolbarItem = this.exoToolbarDialogItem.getItem();
      this.settings = response.settings || ajax.settings || drupalSettings;
      resolve();
    });
  }

  public show():this {
    this.exoToolbarItem.getElement().addClass('exo-toolbar-has-dialog-type-' + this.exoToolbarDialogItem.getType().replace(/_/g, '-'));
    return this;
  }

  public hide():this {
    this.exoToolbarItem.getElement().removeClass('exo-toolbar-has-dialog-type-' + this.exoToolbarDialogItem.getType().replace(/_/g, '-'));
    return this;
  }

  public shouldRebuild():boolean {
    return this.rebuild;
  }

  public useShadow():boolean {
    return this.shadow;
  }
}
