class ExoAlchemistComponent {
  protected admin:ExoAlchemistAdmin;
  protected $panel:JQuery;
  protected $ops:JQuery;
  protected $active:JQuery = null;
  protected focusCount:number = 0;
  protected ops:ExoSettingsGroupInterface;
  protected opsActive:boolean = false;
  protected activeTimer:number;
  protected clickTimer:number = 0;
  protected clickCount:number = 0;
  public readonly events = {
    allowFocus: new ExoEvent<ExoSettingsGroupInterface>()
  };

  constructor(admin:ExoAlchemistAdmin) {
    this.admin = admin;
    this.buildPanel();
    this.buildOps();
  }

  /**
   * Called by admin after class has been constructed.
   */
  public bind() {

    Drupal.Exo.event('reveal').on('alchemist', e => {
      // Set focus on any component that is hovered.
      $('.exo-alchemist-component-edit:hover').each((index, element) => {
        $(element).trigger('mouseenter');
      });
    });

    this.admin.event('overlayHide').on('exo.alchemist.component', e => {
      this.hideOps();
      // this.blur();
    });

    Drupal.Exo.$window.on('exo-modal:onClosed.alchemist.component', e => {
      setTimeout(() => {
        this.blur();
        // Set focus on any component that is hovered.
        $('.exo-alchemist-component-edit:hover').each((index, element) => {
          this.focus($(element));
        });
      });
    });

    this.admin.getFieldOpsManager().event('allowFocus').on('exo.alchemist.field.ops', focus => {
      if (this.opsActive === true) {
        focus.allow = false;
      }
    });
  }

  /**
   * Drupal Attach event.
   * @param context
   */
  public attach(context:HTMLElement) {

    $('#layout-builder').once('exo.alchemist.component').each(() => {
      this.refresh();
    });

    $('.exo-alchemist-component-edit', context).once('exo.alchemist').on('mouseenter', e => {
      this.lockFocus();
      if (this.opsActive === false) {
        clearTimeout(this.activeTimer);
        this.activeTimer = setTimeout(() => {
          this.focus($(e.currentTarget));
        }, 200);
      }
    }).on('mouseleave', e => {
      this.unlockFocus();
      if (this.opsActive === false) {
        clearTimeout(this.activeTimer);
        this.activeTimer = setTimeout(() => {
          this.blur();
        }, 200);
      }
    }).on('click', e => {
      if (this.admin.getFieldOpsManager().getActive() === null) {
        e.preventDefault();
        e.stopPropagation();
        this.clickCount++;
        clearTimeout(this.clickTimer);
        this.clickTimer = setTimeout(() => {
          if (this.clickCount > 1) {
            if (this.accessOp('appearance') && this.$active) {
              // console.log(displace.calculateOffset('top'));
              // console.log(displace.offsets);
              // $('html, body').animate({ scrollTop: this.$active.offset().top - displace.offsets.top }, 'slow', () => {
                this.admin.showOverlay(this.$active, 'component');
              // });
              this.triggerOp('appearance');
            }
          }
          this.clickCount = 0;
        }, 300);
      }
    });
  }

  /**
   * Called when layout-builder HTML has been rebuilt.
   */
  public refresh() {
    this.blur();
    // Set focus on any component that is hovered.
    $('.exo-alchemist-component-edit:hover').each((index, element) => {
      $(element).trigger('mouseenter');
    });
  }

  /**
   * Build component panel.
   */
  protected buildPanel() {
    this.$panel = $('<div class="exo-alchemist-component-panel" />').appendTo(Drupal.Exo.$exoContent).on('mouseenter', e => {
      this.lockFocus();
    }).on('mouseleave', e => {
      this.unlockFocus();
      if (this.opsActive === false) {
        clearTimeout(this.activeTimer);
        this.activeTimer = setTimeout(() => {
          this.hideOps();
          this.blur();
        }, 200);
      }
    });
    $('<a href="#" title="Component Options" class="exo-alchemist-op exo-alchemist-component-panel-toggle"></a>').appendTo(this.$panel).on('click', e => {
      e.preventDefault();
      e.stopPropagation();
      if (this.opsActive === false) {
        this.showOps();
      }
      else {
        this.hideOps();
      }
    });

    const onScroll = _.debounce(() => {
      this.positionPanel();
    }, 100);
    Drupal.Exo.$window.on('scroll.exo.alchemist', e => {
      onScroll();
    });

    Drupal.Exo.event('reveal').on('alchemist', e => {
      onScroll();
    });
  }

  /**
   * Position component panel.
   */
  public positionPanel() {
    return new Promise((resolve, reject) => {
      if (this.$active !== null && typeof displace !== 'undefined') {
        const scrollTop = Drupal.Exo.$window.scrollTop() + displace.offsets.top;
        let top = (this.$active.offset().top - displace.offsets.top);
        if (scrollTop > top) {
          top = scrollTop;
        }
        this.$panel.css({
          top: (top) + 'px',
          left: (this.$active.offset().left + this.$active.outerWidth() - this.$panel.outerWidth() - displace.offsets.left) + 'px',
        });
        this.showPanel();
        resolve();
      }
      else {
        resolve();
      }
    });
  }

  /**
   * Show component panel.
   */
  public showPanel() {
    if (this.$active !== null) {
      this.$panel.css({
        opacity: 1,
        visibility: 'visible',
      });
    }
  }

  /**
   * Hide component panel.
   */
  public hidePanel() {
    if (this.$active !== null) {
      this.$panel.css({
        opacity: 0,
        visibility: 'hidden',
      });
    }
  }

  /**
   * Build component ops.
   */
  protected buildOps() {
    const opClasses = this.admin.opClasses;
    this.$ops = $('<div class="exo-alchemist-component-ops" />').on('mouseenter', e => {
      this.lockFocus();
    }).on('mouseleave', e => {
      this.unlockFocus();
    }).appendTo(this.$panel);

    this.ops = {
      appearance: $('<a href="#" title="Appearance" class="' + opClasses + ' exo-alchemist-component-op-appearance hide"><span>Appearance</span></a>').data('op', 'appearance').appendTo(this.$ops),
      up: $('<a href="#" title="Move Up" class="' + opClasses + ' exo-alchemist-component-op-up hide"><span>Move Up</span></a>').data('op', 'up').appendTo(this.$ops),
      down: $('<a href="#" title="Move Down" class="' + opClasses + ' exo-alchemist-component-op-down hide"><span>Move Down</span></a>').data('op', 'down').appendTo(this.$ops),
      restore: $('<a href="#" title="Restore Empty Fields" class="' + opClasses + ' exo-alchemist-component-op-restore hide"><span>Restore</span></a>').data('op', 'restore').appendTo(this.$ops),
      remove: $('<a href="#" title="Remove" class="' + opClasses + ' exo-alchemist-component-op-remove hide"><span>Remove</span></a>').data('op', 'remove').appendTo(this.$ops),
    };

    this.$ops.find('a').on('click', e => {
      e.preventDefault();
      e.stopPropagation();
      this.triggerOp($(e.currentTarget).data('op'));
    });
  };

  /**
   * Set focus state of a component.
   */
  public focus($component:JQuery) {
    let focus = {
      allow: true
    };
    this.event('allowFocus').trigger(focus);
    if (focus.allow === true) {
      if (this.$active !== null && this.$active !== $component) {
        this.$active.removeClass('focus');
      }
      this.opsActive = false;
      this.$panel.off(Drupal.Exo.transitionEvent);
      this.$active = $component;
      this.$active.addClass('focus');
      this.positionPanel().then(() => {
        setTimeout(() => {
          this.$panel.addClass('animate');
        }, 50);
      });
    }
  }

  /**
   * Blur a component.
   */
  public blur() {
    if (this.focusCount <= 0) {
      this.focusCount = 0;
      this.$panel.one(Drupal.Exo.transitionEvent, e => {
        this.$panel.removeAttr('style').removeClass('animate');
        this.$ops.find('a').addClass('hide');
      });
      if (this.$active !== null) {
        this.$active.removeClass('focus');
      }
      this.hidePanel();
      this.$active = null;
    }
  }

  public lockFocus() {
    this.focusCount++;
  }

  public unlockFocus() {
    this.focusCount--;
  }

  public accessOp(op:string) {
    const delta = this.$active.data('exo-alchemist-delta');
    switch (op) {
      case 'up':
        return delta !== 0;
      case 'down':
        return this.admin.getComponentTotal() > delta + 1;
    }
    return true;
  }

  public showOps() {
    this.$panel.addClass('focus');
    this.opsActive = true;
    this.admin.showOverlay(this.$active, 'component').then(() => {
      this.admin.restrictOverlayPointerEvents();
      for (const op in this.ops) {
        if (this.ops.hasOwnProperty(op)) {
          const $link:JQuery = this.ops[op];
          if (this.accessOp(op) === true) {
            $link.removeClass('hide');
          }
          else {
            $link.addClass('hide');
          }
        }
      }
      this.$ops.removeClass('hide');
    });
  }

  public hideOps() {
    this.opsActive = false;
    this.$panel.removeClass('focus');
  }

  public triggerOp(op:string) {
    if (this.$active) {
      const section = this.$active.data('exo-alchemist-section');
      const delta = this.$active.data('exo-alchemist-delta');
      let dialogRenderer = 'off_canvas';
      const dialogOptions:any = {};
      let afterDelta;
      let url = null;
      switch (op) {
        case 'up':
        case 'down':
          afterDelta = op == 'up' ? delta - 2 : delta + 1;
          if (afterDelta >= 0) {
            const $componentBefore = $('.exo-alchemist-component-edit[data-exo-alchemist-delta="' + (afterDelta) + '"');
            const sectionBefore = $componentBefore.data('exo-alchemist-section');
            url = 'layout_builder/move/block/' + this.admin.getStorageType() + '/' + this.admin.getStorageId() + '/' + this.admin.getSectionDelta() + '/' + this.admin.getSectionDelta() + '/' + this.admin.getSectionRegion() + '/' + section + '/' + sectionBefore;
          }
          else {
            url = 'layout_builder/move/block/' + this.admin.getStorageType() + '/' + this.admin.getStorageId() + '/' + this.admin.getSectionDelta() + '/' + this.admin.getSectionDelta() + '/' + this.admin.getSectionRegion() + '/' + section;
          }
          break;
        case 'restore':
        case 'remove':
          dialogRenderer = null;
          dialogOptions.exo_modal = {};
          dialogOptions.exo_modal.icon = 'regular-question-circle';
          dialogOptions.exo_modal.subtitle = 'Are you sure you want to proceed? This action cannot be undone.';
          url = 'layout_builder/' + op + '/block/' + this.admin.getStorageType() + '/' + this.admin.getStorageId() + '/' + this.admin.getSectionDelta() + '/' + this.admin.getSectionRegion() + '/' + section;
          break;
        case 'appearance':
          dialogOptions.exo_modal = {};
          dialogOptions.exo_modal.icon = 'regular-pencil-paintbrush';
          dialogOptions.exo_modal.subtitle = 'Configure component appearance';
          dialogOptions.exo_modal.overlayColor = 'transparent';
          url = 'layout_builder/' + op + '/block/' + this.admin.getStorageType() + '/' + this.admin.getStorageId() + '/' + this.admin.getSectionDelta() + '/' + this.admin.getSectionRegion() + '/' + section;
          break;
      }
      if (url) {
        Drupal.ajax({
          url: Drupal.url(url + '?destination=' + Drupal.url(drupalSettings.path.currentPath)),
          dialog: dialogOptions,
          dialogType: 'dialog',
          dialogRenderer: dialogRenderer,
        }).execute().done((commands:any, status:any, ajax:any) => {
          this.admin.debug('info', 'Ajax request executed successfully.');
        });
      }
      else {
        this.admin.debug('info', 'URL was not build for operation.');
      }
      this.hideOps();
    }
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

  /**
   * Get the active component.
   */
  public getActive():JQuery {
    return this.$active;
  }

}
