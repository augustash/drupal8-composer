

class ExoToolbarToggle {
  protected toolbar:ExoToolbar;
  protected items:ExoCollection<ExoToolbarToggleItem>;
  private readonly events = {
    show: new ExoEvent<ExoToolbarToggleItem>(),
    hide: new ExoEvent<ExoToolbarToggleItem>()
  };

  constructor() {
    this.items = new ExoCollection<ExoToolbarToggleItem>();
  }

  public attach(toolbar:ExoToolbar) {
    this.toolbar = toolbar;
    const regions = toolbar.getRegions();
    toolbar.getItems().each((item:ExoToolbarItem) => {
      const toggle = item.get('toggle');
      if (toggle && !this.items.has(item.getId())) {
        if (regions.getNonEmpty().has(toggle.region) || toggle.ajax === 1) {
          if (!this.items.getById(item.getId())) {
            this.items.add(item.getId(), new ExoToolbarToggleItem(this, toolbar, item));
          }
        }
        else {
          // The region does not exist, so we remove the element.
          item.getElement().remove();
        }
      }
    });
  }

  public getShown():ExoCollection<ExoToolbarToggleItem> {
    const collection = new ExoCollection<ExoToolbarToggleItem>();
    this.items.each((item:ExoToolbarToggleItem, key) => {
      if (item.isShown()) {
        collection.add(key, item);
      }
    });
    return collection;
  }

  public getShownByEdge(edge:string):ExoCollection<ExoToolbarToggleItem> {
    const collection = new ExoCollection<ExoToolbarToggleItem>();
    this.getShown().each((item:ExoToolbarToggleItem, key) => {
      if (item.getRegion().getEdge() === edge) {
        collection.add(key, item);
      }
    });
    return collection;
  }

  public getShownByRegionId(regionId:string):ExoCollection<ExoToolbarToggleItem> {
    const collection = new ExoCollection<ExoToolbarToggleItem>();
    this.getShown().each((item:ExoToolbarToggleItem, key) => {
      if (item.getRegion().getId() === regionId) {
        collection.add(key, item);
      }
    });
    return collection;
  }

  public hide(itemId:string):this {
    const item = this.getShown().getById(itemId);
    if (item) {
      item.hide();
    }
    return this;
  }

  public hideByRegionId(regionId:string):this {
    this.getShown().each((item:ExoToolbarToggleItem) => {
      if (item.getRegion().getId() === regionId) {
        item.hide();
      }
    });
    return this;
  }

  public hideAll():this {
    this.getShown().each((item:ExoToolbarToggleItem) => {
      item.hide();
    });
    return this;
  }

  public hideAllExceptEdge(edge?:string):this {
    // Hide valid shown regions.
    this.getShown().each((item:ExoToolbarToggleItem) => {
      if (item.getRegion().getEdge() !== edge) {
        item.hide();
      }
    });
    return this;
  }

  public hideAllAfterRegion(regionId?:string):this {
    // Hide valid shown regions that are after the provided region.
    const region = this.toolbar.getRegion(regionId);
    const offset = region.getOffset();
    this.getShownByEdge(region.getEdge()).each((item:ExoToolbarToggleItem) => {
      if (item.getRegion().getOffset() > offset) {
        item.hide();
      }
    });
    return this;
  }

  public event(type:string):ExoEvent<any> {
    if (typeof this.events[type] !== 'undefined') {
      return this.events[type].expose();
    }
    return null;
  }
}

Drupal.ExoToolbarToggle = new ExoToolbarToggle();
