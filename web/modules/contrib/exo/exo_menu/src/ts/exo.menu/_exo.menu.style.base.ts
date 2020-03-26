abstract class ExoMenuStyleBase implements ExoMenuStyleInterface {
  protected exoMenu:ExoMenu;
  protected defaults:ExoSettingsGroupInterface = {};
  protected $element:JQuery;

  constructor(exoMenu:ExoMenu) {
    this.exoMenu = exoMenu;
    this.$element = jQuery(exoMenu.getSelector());
  }

  public getDefaults():ExoSettingsGroupInterface {
    return this.defaults;
  }

  /**
   * Called when building.
   */
  public build() {}

  /**
   * Called when a refresh is requested.
   */
  public refresh() {}

  public get(key):any {
    return this.exoMenu.get(key);
  }

  public getElement():JQuery {
    return this.$element;
  }
}
