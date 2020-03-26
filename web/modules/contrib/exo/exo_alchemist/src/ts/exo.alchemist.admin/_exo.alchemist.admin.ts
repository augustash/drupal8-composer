class ExoAlchemistAdmin extends ExoDataManager<ExoAlchemistField> {
  protected label:string = 'ExoAlchemistAdmin';
  protected doDebug:boolean = false;
  protected ranOnce:boolean = false;
  protected $mask:JQuery;
  protected $overlay:JQuery;
  protected overlayVisible:boolean = false;
  protected overlayCount:number = 0;
  protected overlayTimer:number = null;
  protected componentManager:ExoAlchemistComponent;
  protected fieldOpsManager:ExoAlchemistFieldOps;
  protected fieldBreadcrumbsManager:ExoAlchemistFieldBreadcrumbs;
  protected highlightClasses:string = null;
  protected blaClasses:any = null;
  public opClasses:string = 'exo-alchemist-op exo-alchemist-op-reset';
  public readonly events = {
    overlayHide: new ExoEvent<ExoAlchemistAdmin>(),
    overlayHidden: new ExoEvent<ExoAlchemistAdmin>()
  };

  /**
   * Initial setup.
   * @param context
   */
  public setup():void {
    super.build();
    this.buildOverlay();

    // Initialize managers.
    this.componentManager = new ExoAlchemistComponent(this);
    this.fieldOpsManager = new ExoAlchemistFieldOps(this);
    this.fieldBreadcrumbsManager = new ExoAlchemistFieldBreadcrumbs(this);

    // We bind here so that managers have access to eachother.
    this.componentManager.bind();
    this.fieldOpsManager.bind();
    this.fieldBreadcrumbsManager.bind();

    Drupal.Exo.$exoBody.on('click.exo.achemist', e => {
      if (!$(e.target).closest('.exo-modal').length) {
        this.hideOverlay();
      }
    });

    Drupal.Exo.$document.on('keyup.exo.alchemist', e => {
      // When escape key is pressed.
      if (this.overlayVisible && e.keyCode === 27) {
        this.hideOverlay();
      }
    });

    Drupal.Exo.$window.on('exo-modal:onClosing.alchemist', e => {
      this.hideOverlay();
    });
  }

  /**
   * Drupal Attach event.
   * @param context
   */
  public attach(context:HTMLElement):Promise<boolean> {
    this.ready = false;
    if (this.ranOnce === false) {
      this.ranOnce = true;
      this.setup();
    }
    return new Promise((resolve, reject) => {
      $('#layout-builder').once('exo.alchemist').each(() => {
        // Hide field overlay whenever the layout builder is refreshed.
        this.hideOverlay();
      });

      const id = $('[data-layout-builder-target-highlight-id]').first().attr('data-layout-builder-target-highlight-id');
      if (id) {
        const $focus = $('[data-layout-builder-highlight-id="' + id + '"]').once('exo.alchemist.highlight');
        if ($focus.length) {
          this.showOverlay($focus);
        }
      }

      // Build out fields.
      if (typeof drupalSettings.exoAlchemist.fields !== 'undefined') {
        $('.exo-component-field-edit', context).once('exo.alchemist').each((index, element) => {
          const fieldData = JSON.parse(element.getAttribute('data-exo-alchemist-field'));
          if (typeof fieldData.fieldName !== 'undefined') {
            jQuery.extend(fieldData, drupalSettings.exoAlchemist.fields[fieldData.fieldName]);
            const id = Drupal.Exo.guid();
            fieldData.element = element;
            this.buildInstance(id, fieldData);
            this.ready = true;
            resolve(true);
            this.debug('info', 'Attach: Finish', status);
          }
        });
      }

      this.componentManager.attach(context);
    });
  }

  /**
   * Create instance.
   */
  protected createInstance(id:string, data:any) {
    return new ExoAlchemistField(id, this);
  }

  protected buildOverlay() {
    this.$mask = $('<div class="exo-alchemist-mask" />').appendTo(Drupal.Exo.$exoContent);
    this.$overlay = $('<div class="exo-alchemist-overlay exo-font exo-reset" />').on('mouseenter', e => {
      this.componentManager.lockFocus();
    }).on('mouseleave', e => {
      this.componentManager.unlockFocus();
    }).appendTo(Drupal.Exo.$exoContent);
  }

  public lockOverlay() {
    this.overlayCount++;
  }

  public unlockOverlay() {
    this.overlayCount--;
  }

  public showOverlay($element:JQuery, op?:string, zindex?:number) {
    return new Promise((resolve, reject) => {
      // Always allow pointer events initially.
      this.allowOverlayPointerEvents();
      this.$overlay.off(Drupal.Exo.transitionEvent);
      this.overlayVisible = true;
      zindex = zindex || 100;

      this.sizeOverlay($element);
      this.$mask.css('zIndex', zindex - 1);
      this.$overlay.css('zIndex', zindex);

      const opPrev = this.$overlay.data('op');
      if (opPrev) {
        this.$overlay.removeClass('exo-overlay-op-' + opPrev);
      }
      if (op) {
        this.$overlay.data('op', op);
        this.$overlay.addClass('exo-overlay-op-' + op);
      }
      Drupal.Exo.$exoBody.addClass('exo-component-focus');

      const updateTimer = () => {
        this.sizeOverlay($element);
        this.overlayTimer = setTimeout(updateTimer, 300);
      }
      clearTimeout(this.overlayTimer);
      updateTimer();

      resolve();
    });
  }

  public sizeOverlay($element) {
    if (this.overlayVisible === true) {
      const outerWidth = $element.outerWidth(true);
      const outerHeight = $element.outerHeight(true);
      const offsets = $element.offset();
      const top = offsets.top - displace.offsets.top - $element.css('marginTop').replace('px', '');
      const bottom = top + outerHeight;
      const left = offsets.left - displace.offsets.left;
      const right = left + outerWidth;
      this.$mask.css({
        top: '0px',
        right: '0px',
        bottom: '0px',
        left: '0px',
        width: '100%',
        height: '100%',
        opacity: 1,
        visibility: 'visible',
        clipPath: 'polygon(0% 0%, 0% 100%, ' + left + 'px 100%, ' + left + 'px ' + top + 'px, ' + right + 'px ' + top + 'px, ' + right + 'px ' + bottom + 'px, ' + left + 'px ' + bottom + 'px, ' + left + 'px 100%, 100% 100%, 100% 0%)',
      });
      this.$overlay.css({
        top: top + 'px',
        left: left + 'px',
        width: outerWidth + 'px',
        height: outerHeight + 'px',
        opacity: 1,
        visibility: 'visible',
      });
    }
  }

  public hideOverlay() {
    return new Promise((resolve, reject) => {
      if (this.overlayCount <= 0 && this.overlayVisible === true) {
        clearTimeout(this.overlayTimer);
        this.overlayVisible = false;
        this.$overlay.one(Drupal.Exo.transitionEvent, e => {
          this.$mask.removeAttr('style');
          this.$overlay.removeAttr('style');
          this.event('overlayHidden').trigger(this);
          resolve();
        });
        this.$mask.css({
          opacity: 0,
          visibility: 'hidden',
        });
        this.$overlay.css({
          opacity: 0,
          visibility: 'hidden',
        });
        const op = this.$overlay.data('op');
        if (op) {
          this.$overlay.removeClass('exo-overlay-op-' + op);
        }
        this.event('overlayHide').trigger(this);
      }
      else {
        resolve();
      }
    });
  }

  /**
   * Set the mask to allow click-through.
   */
  public allowOverlayPointerEvents() {
    this.$mask.removeClass('restrict');
  }

  /**
   * Set the mask to intercept all click events.
   */
  public restrictOverlayPointerEvents() {
    this.$mask.addClass('restrict');
  }

  public getEntityType() {
    return drupalSettings.exoAlchemist.entityType;
  }

  public getStorageType() {
    return drupalSettings.exoAlchemist.storageType;
  }

  public getStorageId() {
    return drupalSettings.exoAlchemist.storageId;
  }

  public getSectionDelta() {
    return drupalSettings.exoAlchemist.sectionDelta;
  }

  public getSectionRegion() {
    return drupalSettings.exoAlchemist.sectionRegion;
  }

  public getComponentTotal() {
    return drupalSettings.exoAlchemist.componentTotal;
  }

  public getOverlay():JQuery {
    return this.$overlay;
  }

  public getFieldOpsManager():ExoAlchemistFieldOps {
    return this.fieldOpsManager;
  }

  public getComponentManager():ExoAlchemistComponent {
    return this.componentManager;
  }

  /**
   * Get an event.
   */
  public event(type:string):ExoEvent<any> {
    if (typeof this.events[type] !== 'undefined') {
      return this.events[type].expose();
    }
    return null;
  }

}

Drupal.ExoAlchemistAdmin = Drupal.ExoAlchemistAdmin ? Drupal.ExoAlchemistAdmin : new ExoAlchemistAdmin();
