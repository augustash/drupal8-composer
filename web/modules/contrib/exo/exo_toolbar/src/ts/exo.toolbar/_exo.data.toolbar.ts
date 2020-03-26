/**
 * Manager for data plugins.
 */
abstract class ExoDataToolbar extends ExoData {
  protected exoToolbar:ExoToolbar;
  protected $element:JQuery;

  constructor(id:string, exoToolbar:ExoToolbar) {
    super(id);
    this.exoToolbar = exoToolbar;
  }

  public getToolbar():ExoToolbar {
    return this.exoToolbar;
  }

  public getToolbarId():string {
    return this.getToolbar().getId();
  }

  public getElement():JQuery {
    if (!this.$element) {
      this.$element = jQuery(this.getSelector());
    }
    return this.$element;
  }

  abstract getSelector():string;
}
