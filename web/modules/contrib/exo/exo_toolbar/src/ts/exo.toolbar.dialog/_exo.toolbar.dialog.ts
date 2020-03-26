class ExoToolbarDialog {
  protected label:string = 'ExoToolbarDialog';
  protected doDebug:boolean = false;
  protected items:ExoCollection<ExoToolbarDialogItem>;
  public dialogItemContentClass:string = 'exo-toolbar-dialog-content';

  constructor() {
    this.items = new ExoCollection<ExoToolbarDialogItem>();
  }

  public attach(toolbar:ExoToolbar) {
    const regions = toolbar.getRegions();
    // setTimeout(() => {
    //   this.debug('log', 'Attach Items', toolbar.getItems().getAll());
    // }, 100);
    toolbar.getItems().each((item:ExoToolbarItem) => {
      const dialogType = item.get('dialog_type');
      if (dialogType && !this.items.has(item.getId())) {
        this.debug('log', 'Attach', item);
        this.items.add(item.getId(), new ExoToolbarDialogItem(this, toolbar, item));
      }
    });
  }

  /**
   * Get a specific item by id.
   *
   * @param itemId
   *   The item id.
   */
  public getItem(itemId:string):ExoToolbarDialogItem {
    let item = null;
    this.items.each((dialogItem:ExoToolbarDialogItem) => {
      if (dialogItem.getItemId() === itemId) {
        item = dialogItem;
      }
    });
    return item;
  }

  public getShown():ExoCollection<ExoToolbarDialogItem> {
    const collection = new ExoCollection<ExoToolbarDialogItem>();
    this.items.each((item:ExoToolbarDialogItem, key) => {
      if (item.isShown()) {
        collection.add(key, item);
      }
    });
    return collection;
  }

  public hideAll():void {
    this.getShown().each((item:ExoToolbarDialogItem) => {
      item.hide();
    });
  }

  /**
   * Log debug message.
   */
  public debug(type:string, ...args) {
    if (this.doDebug === true) {
      Drupal.Exo.debug(type, this.label, ...args);
    }
  }
}

Drupal.ExoToolbarDialog = new ExoToolbarDialog();
Drupal.ExoToolbarDialogTypes = {};
