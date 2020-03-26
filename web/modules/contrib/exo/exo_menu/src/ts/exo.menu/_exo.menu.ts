class ExoMenu extends ExoData {
  protected label:string = 'Menu';
  protected doDebug:boolean = false;
  protected handler:ExoMenuStyleInterface = null;

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      super.build(data).then(data => {
        if (data !== null) {
          if (typeof Drupal.ExoMenuStyles[this.getStyle()] !== 'undefined') {
            if (this.handler === null) {
              this.handler = new Drupal.ExoMenuStyles[this.getStyle()](this);
            }
            this.data = _.extend({}, this.handler.getDefaults(), this.getStyleDefaults(), this.data);
            if (this.handler.getElement().once('exo.menu').length) {
              this.handler.build();
            }
          }
        }
        resolve(data);
      }, reject);
    });
  }

  public refresh() {
    if (this.handler) {
      this.handler.refresh();
    }
  }

  public getStyleDefaults() {
    const defaults = Drupal.ExoMenu.getSettingsGroup('defaults');
    return typeof defaults[this.getStyle()] !== 'undefined' ? defaults[this.getStyle()] : {};
  }

  public getStyle() {
    return this.get('style');
  }

  public getSelector() {
    return this.get('selector');
  }
}
