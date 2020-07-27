class ExoModals extends ExoDataManager<ExoModal> {
  protected label:string = 'Modals';
  protected doDebug:boolean = false;
  protected settingsGroup:string = 'exoModal';
  protected instanceSettingsGroup:string = 'modals';
  protected instanceClass:ExoSettingInstance = ExoModal;
  protected $wrapper: JQuery;
  public globalWrapper:string = '#exo-modals';
  protected readonly events = {
    opening: new ExoEvent<ExoModal>(),
    opened: new ExoEvent<ExoModal>(),
    closing: new ExoEvent<ExoModal>(),
    closed: new ExoEvent<ExoModal>()
  };

  constructor() {
    super();
    this.$wrapper = $(this.globalWrapper);
    this.buildBindings();
  }

  /**
   * Add instance.
   */
  protected addInstance(id:string, instance:ExoModal) {
    instance.event('opening').on('modals', (modal:ExoModal) => {
      this.event('opening').trigger(modal);
      if (modal.get('appendTo') === this.globalWrapper) {
        this.$wrapper.addClass('exo-modals-active').attr('aria-hidden', 'false');
      }
    });
    instance.event('opened').on('modals', (modal:ExoModal) => {
      this.event('opened').trigger(modal);
    });
    instance.event('closing').on('modals', (modal:ExoModal) => {
      this.event('closing').trigger(modal);
    });
    instance.event('closed').on('modals', (modal:ExoModal) => {
      this.event('closed').trigger(modal);
      if (this.getVisible(true).count() === 0) {
        this.$wrapper.removeClass('exo-modals-active').attr('aria-hidden', 'true');
      }
    });
    super.addInstance(id, instance);
  }

  /**
   * Build the bindings.
   */
  protected buildBindings() {
    Drupal.Exo.$document.off('keydown.exo.modal').on('keydown.exo.modal', (e:JQueryEventObject) => {
      this.getVisible().each((modal:ExoModal) => {
        const $element = $(e.target);
        if (modal.getElement().find(e.target).length) {
          if (e.keyCode === 13 && modal.getElement().find('form').length) {
            if ($element.has('[data-autocomplete-path]').length || $element.hasClass('ui-autocomplete-input')) {
              return;
            }
            // This may cause issues as it is just clicking the first button it
            // finds.
            modal.getElement().find('form .form-submit:first').mousedown();
          }
          // Close when the Escape key is pressed
          else if (e.keyCode === 27 && modal.get('closeOnEscape')) {
            modal.close();
          }
        }
      });
    });

    // Next and prev for grouped modals.
    Drupal.Exo.$document.off('keyup.exo.modal').on('keyup.exo.modal', e => {
      const target = e.target;
      if (!e.ctrlKey && !e.metaKey && !e.altKey && target['tagName'].toUpperCase() !== 'INPUT' && target['tagName'].toUpperCase() != 'TEXTAREA') {
        this.getVisible().each((modal:ExoModal) => {
          if (modal.get('group')) {
            const groupModals = Drupal.ExoModal.getGrouped(modal.get('group'));
            if (groupModals.count() > 1) {
              if (e.keyCode === 37) { // left
                modal.prev(e);
              } else if (e.keyCode === 39) { // right
                modal.next(e);
              }
            }
          }
        });
      }
    });

    Drupal.ExoDisplace.event('changed').on('exo.modal', (offsets:ExoDisplaceOffsetsInterface) => {
      this.resizeWrapper();
    });
  }

  public closeAll() {
    this.getVisible().each((modal:ExoModal) => {
      modal.close();
    });
  }

  public close(modalId) {
    const instance = this.getInstance(modalId);
    if (instance) {
      instance.close();
    }
    else {
      this.debug('error', 'Count not find modal to close', modalId);
    }
  }

  /**
   * Get visible modals.
   *
   * @param boolean containedOnly
   *   Get visible modals that are contained within the main wrapper area.
   */
  public getVisible(containedOnly?:boolean):ExoDataCollection<ExoModal> {
    const collection = new ExoDataCollection<ExoModal>();
    containedOnly = containedOnly === true;
    this.getInstances().each((modal:ExoModal) => {
      if (modal.isOpen()) {
        if (containedOnly === false || (containedOnly === true && modal.get('appendTo') === this.globalWrapper)) {
          collection.add(modal);
        }
      }
    });
    return collection;
  }

  /**
   * Get the focused visible modal.
   *
   * @param boolean containedOnly
   *   Get visible modals that are contained within the main wrapper area.
   */
  public getVisibleFocus(containedOnly?:boolean):ExoModal {
    const collection = new ExoDataCollection<ExoModal>();
    let index = 0;
    let focused = null;
    this.getVisible(containedOnly).each((modal:ExoModal) => {
      let modalIndex = modal.get('zindex');
      if (modalIndex >= index) {
        index = modalIndex;
        focused = modal;
      }
    });
    return focused;
  }

  /**
   * Get modal by id.
   */
  public getById(id:string):Promise<ExoModal> {
    return new Promise((resolve, reject) => {
      this.getInstances().each((modal:ExoModal) => {
        if (modal.getId() === id) {
          resolve(modal);
        }
      });
    });
  }

  /**
   * Get grouped modals.
   */
  public getGrouped(groupId:string):ExoDataCollection<ExoModal> {
    const collection = new ExoDataCollection<ExoModal>();
    if (groupId !== '') {
      this.getInstances().each((modal:ExoModal) => {
        if (modal.get('group') === groupId) {
          collection.add(modal);
        }
      });
    }
    return collection;
  }

  /**
   * Get modals wrapper.
   */
  public getWrapper():JQuery {
    return this.$wrapper;
  }

  /**
   * Resize content area.
   */
  public resizeWrapper() {
    this.getWrapper().css({
      top: Drupal.ExoDisplace.offsets.top,
      bottom: Drupal.ExoDisplace.offsets.bottom,
      left: Drupal.ExoDisplace.offsets.left,
      right: Drupal.ExoDisplace.offsets.right,
    });
  }

  public event(type:string):ExoEvent<any> {
    if (typeof this.events[type] !== 'undefined') {
      return this.events[type].expose();
    }
    return null;
  }
}

Drupal.ExoModal = new ExoModals();
