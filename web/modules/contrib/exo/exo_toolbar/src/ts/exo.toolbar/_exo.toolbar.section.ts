class ExoToolbarSection extends ExoDataToolbar {
  protected label:string = 'Toolbar Section';
  protected doDebug:boolean = false;
  protected region:string;
  protected sort:string;

  public getBaseId() {
    return this.get('id');
  }

  public getSelector():string {
    let selector = `#exo-toolbar-section-${this.getToolbarId()}-${this.getRegionId()}-${this.getBaseId()}`;
    return selector.replace(/_/g, '-').replace(/:/g, '');
  }

  public getRegionId():string {
    return this.get('region');
  }

  public getRegion():ExoToolbarRegion {
    return this.getToolbar().getRegion(this.getRegionId());
  }

  public getItems():ExoDataCollection<ExoToolbarItem> {
    return this.getToolbar().getItemsBySection(this.getId());
  }

  public orderItems():this {
    const items = this.getItems();
    const $elements = items.elements();
    $elements.each((key, element) => {
      const $element = $(element);
      const itemId = $element.data('exo-item-id');
      const item = items.getById(itemId);
      if (item) {
        if (item.allowSort()) {
          item.setWeight(key);
        }
        else if (this.getSort() === 'asc') {
          // When we are asc sorting, we want to move non-sortable items to end.
          $element.appendTo(this.getElement());
        }
      }
    });
    return this;
  }

  public getSort():string {
    return this.get('sort');
  }

}
