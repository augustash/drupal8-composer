class ExoMenus extends ExoDataManager<ExoMenu> {
  protected label:string = 'Menus';
  protected doDebug:boolean = false;
  protected settingsGroup:string = 'exoMenu';
  protected instanceSettingsGroup:string = 'menus';
  protected instanceClass:ExoSettingInstance = ExoMenu;

  /**
   * Calling will call all menus init operations.
   */
  public refresh(menuId?:string) {
    this.getInstances().each((menu:ExoMenu) => {
      menu.refresh();
    });
  }
}

Drupal.ExoMenu = new ExoMenus();
Drupal.ExoMenuStyles = {};
