class ExoToolbarRegionCollection<T> extends ExoDataCollection<T> {

  public getNonEmpty():ExoToolbarRegionCollection<ExoToolbarRegion> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegion>();
    this.each((region:ExoToolbarRegion) => {
      if (region.getElement().find('.exo-toolbar-element:not(.exo-toolbar-element-dependent)').length) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getEmpty():ExoToolbarRegionCollection<ExoToolbarRegion> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegion>();
    this.each((region:ExoToolbarRegion) => {
      if (!region.getElement().find('.exo-toolbar-element:not(.exo-toolbar-element-dependent)').length) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getVisible():ExoToolbarRegionCollection<ExoToolbarRegion> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegion>();
    this.getNonEmpty().each((region:ExoToolbarRegion) => {
      if (!region.isHidden()) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getHidden():ExoToolbarRegionCollection<ExoToolbarRegion> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegion>();
    this.each((region:ExoToolbarRegion) => {
      if (region.isHidden()) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getVisibleByDefault():ExoToolbarRegionCollection<ExoToolbarRegion> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegion>();
    this.each((region:ExoToolbarRegion) => {
      if (!region.isHiddenByDefault()) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getHiddenByDefault():ExoToolbarRegionCollection<ExoToolbarRegion> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegion>();
    this.each((region:ExoToolbarRegion) => {
      if (region.isHiddenByDefault()) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getByEdge(edge:string):ExoToolbarRegionCollection<ExoToolbarRegion> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegion>();
    this.each((region:ExoToolbarRegion) => {
      if (region.getEdge() === edge) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getByAlignment(alignment:string):ExoToolbarRegionCollection<ExoToolbarRegions> {
    const collection = new ExoToolbarRegionCollection<ExoToolbarRegions>();
    this.each((region:ExoToolbarRegion) => {
      if (region.getAlignment() === alignment) {
        collection.add(region);
      }
    });
    return collection;
  }

  public getEdges():Array<string> {
    let results = [];
    this.each((region:ExoToolbarRegion) => {
      if (!_.contains(results, region.getEdge())) {
        results.push(region.getEdge());
      }
    });
    return results;
  }
}
