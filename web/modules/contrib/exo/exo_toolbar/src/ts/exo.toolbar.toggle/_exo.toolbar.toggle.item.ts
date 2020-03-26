class ExoToolbarToggleItem {
  protected toggleController:ExoToolbarToggle;
  protected toggleOptions:ExoSettingsGroupInterface;
  protected toolbar:ExoToolbar;
  protected item:ExoToolbarItem;
  protected region:ExoToolbarRegion;
  protected ajax:boolean;
  protected event:string;
  protected shown:boolean = false;

  constructor(toggle:ExoToolbarToggle, toolbar:ExoToolbar, item:ExoToolbarItem) {
    this.toggleOptions = item.get('toggle');
    this.toggleController = toggle;
    this.toolbar = toolbar;
    this.item = item;
    this.region = toolbar.getRegion(this.toggleOptions.region);
    this.ajax = this.toggleOptions.ajax === 1;
    this.event = this.ajax ? 'click' : this.toggleOptions.event;
    this.buildBindings();
    if (!this.ajax) {
      this.buildEvents();
    }
  }

  protected buildBindings() {
    let lock = false;
    if (this.event === 'hover') {
      let timer;
      this.item.getElement().on('mouseenter.exoToolbarToggle', e => {
        e.preventDefault();
        // Lock for 1000ms to avoid unintentional clicking.
        lock = true;
        setTimeout(() => {
          lock = false;
        }, 1000);
        timer = setTimeout(() => {
          this.show();
        }, 250);
      }).on('mouseleave.exoToolbarToggle', e => {
        clearTimeout(timer);
      });
    }
    this.item.getElement().on('click.exoToolbarToggle', e => {
      e.preventDefault();
      if (lock === false) {
        if (this.ajax && !this.region) {
          Drupal.ajax({
            url: '/api/exo/toolbar/region/' + this.toggleOptions.region.replace('item:', '')
          }).execute().done((commands:any, status:any, ajax:any) => {
            Drupal.ExoToolbar.isReady().then(() => {
              this.region = this.toolbar.getRegion(this.toggleOptions.region);
              if (this.region) {
                this.buildEvents();
                this.toggle();
              }
            });
          });
        }
        else {
          this.toggle();
        }
      }
    });
  }

  protected buildEvents() {
    // Watch for contract on region and hide.
    if (this.region.getAlignment() === 'vertical') {
      this.region.event('expand').on('toggle.' + this.item.getId(), (region) => {
        this.toggleController.hideAllExceptEdge(this.region.getEdge());
      })
      this.region.event('contracted').on('toggle.' + this.item.getId(), (region) => {
        this.hide();
      });
    }
  }

  public isShown():boolean {
    return this.shown === true;
  }

  public toggle() {
    if (this.isShown()) {
      this.hide();
    }
    else {
      this.show();
    }
  }

  protected isNested():boolean {
    return this.item.getRegion().getEdge() === this.region.getEdge();
  }

  protected isSameAlignment():boolean {
    return this.item.getRegion().getAlignment() === this.region.getAlignment();
  }

  public show() {
    return new Promise((resolve, reject) => {
      if (!this.isShown()) {
        this.region.positionLock(true);
        // Allow nested toggle.
        if (this.isNested()) {
          let css = {};
          let cancel = false;
          const shown = this.toggleController.getShownByEdge(this.region.getEdge());
          // If an item is requested at the same level as another item, close it.
          shown.each((toggleItem:ExoToolbarToggleItem) => {
            if (this.item.getRegion().getId() === toggleItem.getItem().getRegion().getId()) {
              cancel = true;
              toggleItem.hide().then(() => {
                this.show().then((status) => {
                  resolve(status);
                });
              });
            }
          });
          if (cancel) {
            return;
          }
          // // Make sure region containing the trigger is 'higher' than the
          // // triggerable region so that dialogs will show over top.
          // this.item.getRegion().zIndexSet('PARENT');
          // this.toolbar.zIndexRegionEdgeLock(this.item.getRegion().getEdge(), true);

          this.toolbar.getEdgeOffsetsByEdge(this.region.getEdge()).forEach(offsetEdge => {
            css[offsetEdge] = this.item.getRegion().getElement().css(offsetEdge);
          });
          this.region.getElement().css(css);
          this.item.getRegion().getElement().removeClass(this.toolbar.regionLastClass);
          // @TODO Consider moving to modal module and activating via an event.
          if (Drupal.ExoModal) {
            Drupal.ExoModal.closeAll();
          }
        }
        else {
          // When a region is toggled from another region that is not toggleable
          // we want to adjust the offset of the item so that it is flush with
          // the toggle's edge.
          if (!this.item.getRegion().get('toggleable')) {
            if (!this.isSameAlignment()) {
              let css = {};
              css[this.item.getRegion().getEdge()] = this.item.getRegion().getSize() + this.item.getRegion().getOffset();
              this.region.getElement().css(css);
              // Make sure region containing the trigger is 'higher' than the
              // triggerable region so that dialogs will show over top.
              this.item.getRegion().zIndexSet('PARENT');
              this.toolbar.zIndexRegionEdgeLock(this.item.getRegion().getEdge(), true);
            }
            else {
              let css = {};
              this.toolbar.getEdgeOffsetsByEdge(this.region.getEdge()).forEach(offsetEdge => {
                css[offsetEdge] = this.toolbar.getRegionSizeByEdge(offsetEdge);
              });
              this.region.getElement().css(css);
            }
          }
          this.toggleController.hideAll();
        }

        this.toolbar.zIndexRegionEdgeLock(this.region.getEdge(), false)
          .zIndexRegionEdge(this.region.getEdge(), 'FOCUSED')
          .zIndexRegionEdgeLock(this.region.getEdge(), true);

        this.toggleController.event('show').trigger(this);
        this.shown = true;
        this.item.getElement().addClass(this.toolbar.itemActiveClass);
        this.region.show(true, true).then((status) => {
          resolve(status);
        });
        this.region.getElement().addClass(this.toolbar.regionLastClass);
        // this.toolbar.zIndexRegionEdge(this.region.getEdge(), 'FOCUSED')
        //   .zIndexRegionEdgeLock(this.region.getEdge(), true);
        Drupal.Exo.showShadow({
          opacity: 0.3,
          onClick: e => {
            this.hide();
          }
        });
      }
      else {
        resolve(true);
      }
    });
  }

  public hide():Promise<boolean> {
    return new Promise((resolve, reject) => {
      if (this.isShown()) {
        this.shown = false;
        if (this.isNested()) {
          this.item.getRegion().getElement().addClass(this.toolbar.regionLastClass);
        }
        else {
          this.toggleController.hideAll();
        }
        this.toggleController.event('hide').trigger(this);
        this.region.hide(true).then((status) => {
          // Reset trigger region z-index.
          if (!this.item.getRegion().get('toggleable')) {
            if (!this.isSameAlignment()) {
              this.toolbar.zIndexRegionEdgeLock(this.item.getRegion().getEdge(), false).zIndexRegionEdge(this.item.getRegion().getEdge());
            }
          }
          this.region.positionLock(false);
          resolve(status);
        });
        this.item.getElement().removeClass(this.toolbar.itemActiveClass);
        this.region.getElement().removeClass(this.toolbar.regionLastClass);
        if (!this.region.isExpandedByDefault()) {
          this.region.getElement().removeClass(this.toolbar.regionExpandedClass)
        }
        Drupal.Exo.hideShadow();
      }
      else {
        this.region.positionLock(false);
        resolve(true);
      }
    });
  }

  public getRegion():ExoToolbarRegion {
    return this.region;
  }

  public getItem():ExoToolbarItem {
    return this.item;
  }

}
