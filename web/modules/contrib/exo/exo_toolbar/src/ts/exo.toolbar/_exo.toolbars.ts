class ExoToolbars extends ExoDataManager<ExoToolbar> {
  protected label:string = 'ExoToolbars';
  protected doDebug:boolean = false;
  protected settingsGroup:string = 'exoToolbar';
  protected instanceSettingsGroup:string = 'toolbars';
  protected instanceClass:ExoSettingInstance = ExoToolbar;

  /**
   * Drupal Attach event.
   * @param context
   */
  public attach(context:HTMLElement):Promise<boolean> {
    return new Promise((resolve, reject) => {
      super.attach(context).then(status => {
        if (status === true) {
          this.resize();
        }
        resolve(status);
      });
    });
  }

  /**
   * Returns true if in edit mode.
   */
  public isAdminMode():boolean {
    return drupalSettings.exoToolbar.isAdminMode === true;
  }

  public getDisplacement():Promise<ExoDisplaceOffsetsInterface> {
    return new Promise((resolve, reject) => {
      this.getInstances().each((toolbar:ExoToolbar) => {
        toolbar.getDisplacement().then(offsets => {
          resolve(offsets);
        });
      });
    });
  }

  public resize() {
    this.getInstances().each((toolbar:ExoToolbar) => {
      toolbar.resize();
    });
  }

}

Drupal.ExoToolbar = new ExoToolbars();
