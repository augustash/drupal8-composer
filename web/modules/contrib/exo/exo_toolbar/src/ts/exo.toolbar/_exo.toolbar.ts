class ExoToolbar extends ExoData {
  protected label:string = 'Toolbar';
  protected doDebug:boolean = false;
  protected data:any;
  protected id:string;
  protected regions:ExoToolbarRegions = {};
  protected sections:ExoToolbarSections = {};
  protected items:ExoToolbarItems = {};
  protected regionZIndexLock = {};
  public transitionLock:boolean = false;
  public regionEmptyClass:string = 'exo-toolbar-region-empty';
  public regionLastClass:string = 'exo-toolbar-region-last';
  public regionFocusClass:string = 'exo-toolbar-region-focus';
  public regionExpandedClass:string = 'exo-toolbar-region-expanded';
  public itemActiveClass:string = 'exo-toolbar-item-active';
  public asideClass:string = 'exo-toolbar-item-aside';
  public asideDisableClass:string = 'exo-toolbar-aside-disable';
  protected readonly events = {
    regionShow: new ExoEvent<ExoToolbarRegion>(),
    regionHide: new ExoEvent<ExoToolbarRegion>(),
    regionExpand: new ExoEvent<ExoToolbarRegion>(),
    regionContract: new ExoEvent<ExoToolbarRegion>()
  };

  constructor(toolbarId:string) {
    super(toolbarId);
    Drupal.Exo.getBodyElement().addClass('has-exo-toolbar');
  }

  public build(data:any):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      super.build(data).then(data => {
        if (data !== null) {
          this.debug('log', 'Build: Act', '[' + this.id + ']', data);
          const promises = [];
          if (data.regions) {
            // this.debug('log', 'Build Regions', '[' + this.id + ']', data.regions);
            promises.push(this._build('regions', data.regions, ExoToolbarRegion));
          }
          if (data.sections) {
            // this.debug('log', 'Build Sections', '[' + this.id + ']', data.sections);
            promises.push(this._build('sections', data.sections, ExoToolbarSection));
          }
          if (data.items) {
            // this.debug('log', 'Build Items', '[' + this.id + ']', data.items);
            promises.push(this._build('items', data.items, ExoToolbarItem));
          }

          // Resolve once all images have been loaded.
          this.getElement().imagesLoaded(() => {
            // We add a timeout to make sure the build has been able to complete.
            Promise.all(promises).then(values => {
              resolve(data);
            });
          });
        }
        else {
          resolve(data);
        }
      }, reject);

    });
  }

  /**
   * Build out regions, sections and items and sort them by their weight.
   */
  protected _build(type:string, data:any, classType:any):Promise<boolean> {
    return new Promise((resolve, reject) => {
      const promises = [];
      _.each(data, (item, id) => {
        if (!this[type][id]) {
          this[type][id] = new classType(id, this);
          promises.push(this[type][id].build(data[id]));
        }
      });
      this[type] = this._sortKeysBy(this[type], (value:ExoDataToolbar, key) => {
        return value.getWeight();
      });
      Promise.all(promises).then(values => {
        resolve(true);
      });
    });
  }

  public resize() {
    this.debug('log', 'Resize');
    this.hideRegions();
    this.zIndexRegions();
    this.positionRegions();
    this.interlockRegions();
    this.positionAsides();
    this.positionDividers();
  }

  /**
   * Get the toolbar id.
   */
  public getId():string {
    return this.id;
  }

  /**
   * Get the toolbar element.
   */
  public getElement():JQuery {
    return $('#exo-toolbar-' + this.id);
  }

  public getRegions():ExoToolbarRegionCollection<ExoToolbarRegion> {
    return new ExoToolbarRegionCollection<ExoToolbarRegion>(this.regions);
  }

  public getRegion(regionId:string):ExoToolbarRegion {
    return this.getRegions().getById(regionId);
  }

  public getSections():ExoDataCollection<ExoToolbarSection> {
    return new ExoDataCollection<ExoToolbarSection>(this.sections);
  }

  public getSection(sectionId:string):ExoToolbarSection {
    return this.getSections().getById(sectionId);
  }

  public getItems():ExoDataCollection<ExoToolbarItem> {
    return new ExoDataCollection<ExoToolbarItem>(this.items);
  }

  public getItemsByRegion(regionId:string):ExoDataCollection<ExoToolbarItem> {
    const collection = new ExoDataCollection<ExoToolbarItem>();
    this.getItems().each((item:ExoToolbarItem) => {
      if (item.getRegionId() === regionId) {
        collection.add(item);
      }
    });
    return collection;
  }

  public getItemsBySection(sectionId:string):ExoDataCollection<ExoToolbarItem> {
    const collection = new ExoDataCollection<ExoToolbarItem>();
    this.getItems().each((item:ExoToolbarItem) => {
      if (item.getSectionUniqueId() === sectionId) {
        collection.add(item);
      }
    });
    return collection;
  }

  public getItem(itemId:string):ExoToolbarItem {
    return this.getItems().getById(itemId);
  }

  public getRegionSizeByEdge(edge:string):number {
    let size = 0;
    this.getRegions().getVisible().getByEdge(edge).each((region:ExoToolbarRegion) => {
      size += region.getSize();
    });
    return size;
  }

  public getRegionEdges(onlyVisible?:boolean):Array<string> {
    let results = [];
    let regions = this.getRegions();
    if (onlyVisible) {
      regions = regions.getVisible();
    }
    regions.each((region:ExoToolbarRegion) => {
      if (!_.contains(results, region.getEdge())) {
        results.push(region.getEdge());
      }
    });
    return results;
  }

  public getEdgeOffsetsByEdge(edge:string):Array<string> {
    switch (edge) {
      case 'top':
      case 'bottom':
        return ['left', 'right'];
      case 'left':
      case 'right':
        return ['top', 'bottom'];
    }
  }

  public getEdgeOffsetsByAlignment(alignment:string):Array<string> {
    switch (alignment) {
      case 'horizontal':
        return ['left', 'right'];
      case 'vertical':
        return ['top', 'bottom'];
    }
  }

  public getRegionElements():JQuery {
    let selectors = [];
    this.getRegions().each((region:ExoToolbarRegion) => {
      selectors.push(region.getSelector());
    });
    return $(selectors.join(', '));
  }

  /**
   * Hide empty regions.
   */
  public hideRegions():this {
    const regions = this.getRegions();
    regions.getNonEmpty().elements().removeClass(this.regionEmptyClass);
    regions.getEmpty().elements().addClass(this.regionEmptyClass);
    // This is now handled in the region itself.
    // regions.getHidden().each((region:ExoToolbarRegion) => {
    //   region.hide(false, true);
    // });
    return this;
  }

  /**
   * Set z-index positions for all regions.
   */
  public zIndexRegions() {
    this.getRegions().getEdges().forEach(edge => {
      this.zIndexRegionEdge(edge);
    });
    return this;
  }

  /**
   * Reset all z-index positions for all regions.
   */
  public zIndexRegionsReset() {
    this.getRegions().getEdges().forEach(edge => {
      this.getRegions().getByEdge(edge).each((region:ExoToolbarRegion) => {
        region.zIndexLock(false);
      });
      this.zIndexRegionEdge(edge);
    });
    return this;
  }

  /**
   * Set z-index positioning so that regions overlay lesser regions.
   */
  public zIndexRegionEdge(edge:string, key?:string):this {
    const regions = this.getRegions().getByEdge(edge);
    let count = regions.count();
    regions.each((region:ExoToolbarRegion) => {
      region.zIndexSetOffset(count);
      region.zIndexSet(key);
      count--;
    });
    return this;
  }

  /**
   * Lock z-index for edge.
   */
  public zIndexRegionEdgeLock(edge:string, lock:boolean):this {
    this.getRegions().getByEdge(edge).each((region:ExoToolbarRegion) => {
      region.zIndexLock(lock);
    });
    return this;
  }

  public zIndexRegionIsLocked(regionId:string):boolean {
    return typeof this.regionZIndexLock[regionId] !== 'undefined' && this.regionZIndexLock[regionId] === true;
  }

  public zIndexRegionLock(regionId:string):this {
    this.regionZIndexLock[regionId] = true;
    return this;
  }

  public zIndexRegionUnlock(regionId:string):this {
    this.regionZIndexLock[regionId] = false;
    return this;
  }

  public positionRegions() {
    this.getRegions().getEdges().forEach(edge => {
      const collection = this.getRegions().getVisibleByDefault().getByEdge(edge);
      let offset = 0;
      collection.elements().removeClass(this.regionLastClass);
      let initialSize = 0;
      collection.each((region:ExoToolbarRegion) => {
        const $element = region.getElement();
        $element.css(edge, offset);
        initialSize += region.getInitialSize();
        $element.attr('data-offset-' + edge, initialSize);
        offset += region.getSize();
        Drupal.Exo.getBodyElement().addClass('has-exo-toolbar-region-' + edge);
      });
      const last = collection.getLast();
      if (last) {
        last.getElement().addClass(this.regionLastClass);
      }
    });
    setTimeout(() => {
      displace(true);
    });
    return this;
  }

  public positionDividers():this {
    const edges = this.getRegions().getVisibleByDefault().getEdges();

    // Temp cache edge sizes as they are used multiple times in this method.
    const edgeSizes = {};
    edges.forEach(edge => {
      edgeSizes[edge] = this.getRegionSizeByEdge(edge);
    });

    this.getRegions().each(region => {
      const $element = region.getElement();
      const $divider = $('.exo-toolbar-region-divider', $element);
      const edge = region.getEdge();
      $divider.removeAttr('style');
      const css = {};
      this.getEdgeOffsetsByEdge(edge).forEach(offsetEdge => {
        let offset = 20;
        // if (!region.isHidden() && typeof edgeSizes[offsetEdge] !== 'undefined') {
        //   offset += edgeSizes[offsetEdge] - parseFloat($element.css(offsetEdge));
        // }
        css[offsetEdge] = offset;
      });
      $divider.css(css);
    });
    return this;
  }

  /**
   * Interlock regions so that they align in a staggered manner.
   *
   * Vertical regions nest below horizontal regions by default.
   */
  public interlockRegions(alignmentPriority?:string):this {
    alignmentPriority = alignmentPriority === 'vertical' ? 'vertical' : 'horizontal';
    this.getRegions().getEdges().forEach(edge => {
      let count:number = 0;
      this.getRegions().getByEdge(edge).each((region) => {
        if (region.isPositionLocked()) {
          return;
        }
        let delta = count;
        if (region.getAlignment() === alignmentPriority) {
          delta -= 1;
        }
        region.getEdgeOffsetsByAlignment().forEach(offsetEdge => {
          // Only account for visible/non-empty regions.
          const edgeCollection = this.getRegions().getVisibleByDefault().getByEdge(offsetEdge);
          let offsetRegion = null;
          let step = region.isHidden() ? edgeCollection.count() - 1 : delta;
          do {
            offsetRegion = edgeCollection.getByDelta(step);
            step -= 1;
          } while (offsetRegion === null && step >= 0);
          if (offsetRegion) {
            const css = {};
            css[offsetEdge] = offsetRegion.getSize() + offsetRegion.getOffset();
            region.getElement().css(css);
          }
        });

        count++;
      });
    });
    return this;
  }

  public positionAsides():this {
    this.getItems().each(item => {
      item.getElement().off('mouseenter.exo.toolbar.aside').on('mouseenter.exo.toolbar.aside', (e) => {
        item.positionAside();
        item.positionLabels();
        item.getElement().addClass('is-hover');
      }).off('mouseleave.exo.toolbar.aside').on('mouseleave.exo.toolbar.aside', (e) => {
        item.getElement().removeClass('is-hover');
      }).off('mouseover.exo.toolbar.aside').on('mouseover.exo.toolbar.aside', (e) => {
        const $focusElement = $(e.target).closest('.exo-toolbar-element');
        const $focusTip = item.getElement().find('#' + $focusElement.attr('id') + '-label');
        item.getElement().find('.exo-toolbar-item-aside-label').removeClass('is-current');
        if ($focusTip.length) {
          $focusTip.addClass('is-current');
        }
      });
    });
    return this;
  }

  public disableAsides():this {
    $('body').addClass(this.asideDisableClass);
    return this;
  }

  public enableAsides():this {
    $('body').removeClass(this.asideDisableClass);
    return this;
  }

  public getDisplacement():Promise<ExoDisplaceOffsetsInterface> {
    const promises = [];
    const offsets:ExoDisplaceOffsetsInterface = {
      top: 0,
      bottom: 0,
      left: 0,
      right: 0,
    };
    return new Promise((resolve, reject) => {
      this.getRegions().each((region:ExoToolbarRegion) => {
        promises.push(region.hasActiveItems().then(hasActive => {
          const size = region.getSize() + region.getOffset();
          if (hasActive && size > offsets[region.getEdge()]) {
            offsets[region.getEdge()] = size;
          }
        }));
      });
      Promise.all(promises).then(values => {
        for (const edge in offsets) {
          if (offsets.hasOwnProperty(edge) && offsets[edge] === 0) {
            offsets[edge] = displace.offsets[edge];
          }
        }
        resolve(offsets);
      });
    });
  }

  /**
   * Lock/unlock transitions.
   */
  public setTransitionLock(lock:boolean) {
    this.transitionLock = lock === true;
  }

  /**
   * Check if transitions are locked.
   */
  public isTransitionLocked() {
    return this.transitionLock === true;
  }

  /**
   * Underscore sort object keys.
   *
   * Like _.sortBy(), but on keys instead of values, returning an object, not an
   * array. Defaults to alphanumeric sort.
   */
  protected _sortKeysBy(obj:any, comparator) {
    var keys = _.sortBy(_.keys(obj), function (key) {
      return comparator ? comparator(obj[key], key) : key;
    });

    return _.object(keys, _.map(keys, function (key) {
      return obj[key];
    }));
  }
}
