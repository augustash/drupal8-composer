class ExoToolbarRegion extends ExoDataToolbar {
  protected label:string = 'Toolbar Region';
  protected doDebug:boolean = false;
  protected is_hidden:boolean = false;
  protected is_expanded:boolean = false;
  protected initialSize:number = null;
  protected readonly events = {
    show: new ExoEvent<ExoToolbarRegion>(),
    shown: new ExoEvent<ExoToolbarRegion>(),
    hide: new ExoEvent<ExoToolbarRegion>(),
    hidden: new ExoEvent<ExoToolbarRegion>(),
    expand: new ExoEvent<ExoToolbarRegion>(),
    expanded: new ExoEvent<ExoToolbarRegion>(),
    contract: new ExoEvent<ExoToolbarRegion>(),
    contracted: new ExoEvent<ExoToolbarRegion>()
  };
  protected positionLocked:boolean = false;
  protected zIndexOffset:number = 0;
  protected zIndexIsLocked:boolean = false;
  protected readonly zindexDepth:ExoSettingsGroupInterface = {
    DEFAULT: 100,
    HOVER: 110,
    PARENT: 115,
    FOCUSED: 120,
  }

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      super.build(data).then(data =>{
        // Set necessary defaults.
        this.is_hidden = this.get('hidden');
        this.is_expanded = this.get('expanded');
        if (this.isHidden()) {
          this.hide(false, true);
        }
        // Setup binding.
        this.bind();
        resolve(data);
      }, reject);
    });
  }

  protected bind() {
    this.getElement().off('mouseenter.exo.toolbar.region').on('mouseenter.exo.toolbar.region', (e) => {
      this.getToolbar().zIndexRegionEdge(this.getEdge(), 'HOVER');
    }).off('mouseleave.exo.toolbar.region').on('mouseleave.exo.toolbar.region', (e) => {
      this.getToolbar().zIndexRegionEdge(this.getEdge());
    });

    if (this.getAlignment() === 'vertical' && !Drupal.ExoToolbar.isAdminMode()) {
      let timer;
      this.getElement().off('mouseenter.exo.toolbar.region.vertical').on('mouseenter.exo.toolbar.region.vertical', (e) => {
        e.preventDefault();
        timer = setTimeout(() => {
          this.expand();
        }, 200);
      }).off('mouseleave.exo.toolbar.region.vertical').on('mouseleave.exo.toolbar.region.vertical', (e) => {
        e.preventDefault();
        clearTimeout(timer);
      });
    }
  }

  public getSelector():string {
    let selector = `#exo-toolbar-region-${this.getToolbarId()}-${this.getId()}`;
    return selector.replace(/_/g, '-').replace(/:/g, '');
  }

  public getEdge():string {
    return this.get('edge');
  }

  public getAlignment():string {
    return this.get('alignment');
  }

  public hasActiveItems():Promise<boolean> {
    return new Promise((resolve, reject) => {
      this.getToolbar().getItemsByRegion(this.getId()).each((item:ExoToolbarItem) => {
        if (item.getElement().hasClass(this.getToolbar().itemActiveClass)) {
          resolve(true);
        }
      });
      resolve(false);
    });
  }

  public getSize():number {
    let size;
    switch (this.getAlignment()) {
      case 'horizontal':
        size = this.getElement().outerHeight();
        break;
      case 'vertical':
        size = this.getElement().outerWidth();
        break;
    }
    if (this.initialSize === null) {
      this.initialSize = size;
    }
    return size;
  }

  public getInitialSize():number {
    // We check again if the size is 0 due to progressive loading of markup.
    if (this.initialSize === null || this.initialSize === 0) {
      this.initialSize = this.getSize();
    }
    return this.initialSize;
  }

  public getOffset():number {
    switch (this.getEdge()) {
      case 'top':
        return this.getElement().offset().top - $(window).scrollTop();
      case 'left':
        return this.getElement().offset().left - $(window).scrollLeft();
      case 'right':
        return $(window).width() - ((this.getElement().offset().left - $(window).scrollLeft()) + this.getSize());
      case 'bottom':
        return $(window).height() - ((this.getElement().offset().top - $(window).scrollTop()) + this.getSize());
    }
  }

  public getEdgeOffsetsByEdge():Array<string> {
    return this.getToolbar().getEdgeOffsetsByEdge(this.getEdge());
  }

  public getEdgeOffsetsByAlignment():Array<string> {
    return this.getToolbar().getEdgeOffsetsByAlignment(this.getAlignment());
  }

  protected getTransitionCss():string {
    const transition = [];
    ['left', 'top', 'right', 'bottom', 'width', 'height'].forEach(prop => {
      transition.push(prop + ' ' + Drupal.Exo.speed + 'ms');
    });
    return transition.join(',');
  }

  public show(animate?:boolean, force?:boolean):Promise<boolean> {
    return new Promise((resolve, reject) => {
      if (force === true || (this.is_hidden === true)) {
        this.getToolbar().setTransitionLock(true);

        // Fire events.
        this.event('show').trigger(this);
        this.getToolbar().event('regionShow').trigger(this);

        this.getElement().off(Drupal.Exo.whichTransitionEvent());
        let css = {
          visibility: 'visible',
          opacity: 1,
          transition: 'none',
        };

        const offset = this.getToolbar().getRegionSizeByEdge(this.getEdge());
        if (animate === true) {
          this.getElement().one(Drupal.Exo.transitionEvent, e => {
            this.shown();
            resolve();
          });
          css[this.getEdge()] = offset + (this.getSize() * -1);
          setTimeout(() => {
            css[this.getEdge()] = offset;
            css['transition'] = this.getTransitionCss();
            this.getElement().css(css);
          }, 40);
        }
        else {
          this.shown();
          css[this.getEdge()] = offset;
          this.getElement().css(css);
          resolve();
        }
      }
    });
  }

  protected shown():this {
    this.is_hidden = false;
    this.getToolbar().setTransitionLock(false);
    this.event('shown').trigger(this);
    return this;
  }

  public hide(animate?:boolean, force?:boolean):Promise<boolean> {
    return new Promise((resolve, reject) => {
      if (force === true || (this.is_hidden === false)) {
        this.getToolbar().setTransitionLock(true);
        this.contract().then(success => {

          // Fire events.
          this.event('hide').trigger(this);
          this.getToolbar().event('regionHide').trigger(this);

          let css = {};
          let finalCss = {
            visibility: 'hidden',
            opacity: 0,
            transition: 'none',
            zIndex: 1,
          };
          if (animate === true) {
            this.getElement().one(Drupal.Exo.transitionEvent, e => {
              this.hidden();
              this.getElement().css(finalCss);
              resolve(true);
            });
            css['visibility'] = 'visible';
            css['opacity'] = 1;
            css['transition'] = this.getTransitionCss();
          }
          else {
            this.hidden();
            css = finalCss;
            resolve(true);
          }
          css[this.getEdge()] = this.getSize() * -1;
          this.getElement().css(css);
        });
      }
      else {
        resolve(false);
      }
    });
  }

  protected hidden():this {
    this.is_hidden = true;
    this.getToolbar().setTransitionLock(false);
    this.event('hidden').trigger(this);
    return this;
  }

  public zIndexSet(key?:string):this {
    if (this.zIndexIsLocked === false) {
      this.getElement().css('z-index', this.zIndexGet(key));
    }
    return this;
  }

  public zIndexGet(key?:string):number {
    let zIndex = key && typeof this.zindexDepth[key] !== 'undefined' ? this.zindexDepth[key] : this.zindexDepth.DEFAULT;
    return parseInt(zIndex) + this.zIndexOffset;
  }

  public zIndexSetOffset(offset:number):this {
    this.zIndexOffset = offset;
    return this;
  }

  public zIndexLock(lock:boolean):this {
    this.zIndexIsLocked = lock === true;
    return this;
  }

  public positionLock(lock:boolean):this {
    this.positionLocked = lock === true;
    return this;
  }

  public isPositionLocked():boolean {
    return this.positionLocked;
  }

  public isHiddenByDefault():boolean {
    return !Drupal.ExoToolbar.isAdminMode() && this.get('hidden') === true;
  }

  public isHidden():boolean {
    return !Drupal.ExoToolbar.isAdminMode() && this.is_hidden === true;
  }

  public isExpandedByDefault():boolean {
    return this.get('expanded') === true;
  }

  public isExpanded():boolean {
    return this.is_expanded === true;
  }

  public expand(force?:boolean):Promise<boolean> {
    return new Promise((resolve, reject) => {
      if (force === true || (this.is_expanded === false)) {
        if (!this.isExpandedByDefault() && this.getAlignment() === 'vertical') {
          this.getToolbar().setTransitionLock(true);
          this.is_expanded = true;

          // Fire events.
          this.event('expand').trigger(this);
          this.getToolbar().event('regionExpand').trigger(this);

          this.getElement().one(Drupal.Exo.transitionEvent, e => {
            this.getToolbar().setTransitionLock(false);
            this.event('expanded').trigger(this);
            resolve(true);
          })
          this.getElement().addClass(this.getToolbar().regionExpandedClass);
          if (!this.isHiddenByDefault()) {
            Drupal.Exo.showShadow({
              opacity: 0.3,
              onClick: e => {
                this.contract();
              }
            });
          }
        }
        else {
          resolve(false);
        }
      }
      else {
        resolve(false);
      }
    });
  }

  public contract(force?:boolean):Promise<boolean> {
    return new Promise((resolve, reject) => {
      if (force === true || (this.is_expanded === true)) {
        if (!this.isExpandedByDefault() && this.getAlignment() === 'vertical') {
          this.getToolbar().setTransitionLock(true);
          // this.getToolbar().zindexRegionEdge(this.getEdge());
          this.is_expanded = false;

          // When hidden by default, we let the hide() handle animations.
          if (this.isHiddenByDefault()) {
            resolve(true);
          }

          // Fire events.
          this.event('contract').trigger(this);
          this.getToolbar().event('regionContract').trigger(this);

          this.getElement().one(Drupal.Exo.transitionEvent, e => {
            this.getToolbar().setTransitionLock(false);
            this.event('contracted').trigger(this);
            if (!this.isHiddenByDefault()) {
              resolve(true);
            }
          })
          this.getElement().removeClass(this.getToolbar().regionExpandedClass);
          if (!this.isHiddenByDefault()) {
            Drupal.Exo.hideShadow();
          }
        }
        else {
          resolve(false);
        }
      }
      else {
        resolve(false);
      }
    });
  }

}
