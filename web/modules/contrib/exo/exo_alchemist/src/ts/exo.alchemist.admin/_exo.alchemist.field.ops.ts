class ExoAlchemistFieldOps {
  protected admin:ExoAlchemistAdmin;
  protected $fieldOps:JQuery;
  protected fieldOps:ExoSettingsGroupInterface;
  protected activeField:ExoAlchemistField = null;
  public readonly events = {
    allowFocus: new ExoEvent<ExoSettingsGroupInterface>(),
    showOps: new ExoEvent<ExoAlchemistFieldOps>()
  };

  constructor(admin:ExoAlchemistAdmin) {
    this.admin = admin;
    this.build();
  }

  /**
   * Called by admin after class has been constructed.
   */
  public bind() {
    this.admin.event('overlayHide').on('exo.alchemist.field.ops', e => {
      if (this.activeField !== null) {
        this.admin.getComponentManager().unlockFocus();
        this.admin.getComponentManager().showPanel();
      }
      // Reset all active fields.
      this.activeField = null;
    });

    this.admin.event('overlayHidden').on('exo.alchemist.field.ops', e => {
      this.$fieldOps.find('.exo-alchemist-op-reset').addClass('hide');
    });

    this.admin.getComponentManager().event('allowFocus').on('exo.alchemist.field.ops', focus => {
      // If we have an active field, disallow changing component focus.
      if (this.activeField !== null) {
        focus.allow = false;
      }
    });
  }

  /**
   * Build field operations.
   */
  protected build() {
    const opClasses = this.admin.opClasses;
    this.$fieldOps = $('<div class="exo-alchemist-field-ops" />').appendTo(this.admin.getOverlay());
    this.fieldOps = {
      prev: $('<a href="#" title="Sort Previous" class="' + opClasses + ' exo-alchemist-field-op-prev hide"></a>').data('op', 'prev').appendTo(this.$fieldOps),
      update: $('<a href="#" title="Edit" class="' + opClasses + ' exo-alchemist-field-op-update hide"></a>').data('op', 'update').appendTo(this.$fieldOps),
      delete: $('<a href="#" title="Delete" class="' + opClasses + ' exo-alchemist-field-op-delete hide"></a>').data('op', 'delete').appendTo(this.$fieldOps),
      clone: $('<a href="#" title="Clone" class="' + opClasses + ' exo-alchemist-field-op-clone hide"></a>').data('op', 'clone').appendTo(this.$fieldOps),
      next: $('<a href="#" title="Sort Next" class="' + opClasses + ' exo-alchemist-field-op-next hide"></a>').data('op', 'next').appendTo(this.$fieldOps),
    };

    this.$fieldOps.find('a').on('click', e => {
      e.preventDefault();
      e.stopPropagation();
      this.triggerOp($(e.currentTarget).data('op'));
    });
  }

  public focus(field:ExoAlchemistField, showOps?:boolean) {
    let focus = {
      allow: true
    };
    this.event('allowFocus').trigger(focus);
    if (focus.allow === true) {
    // if (field.getElement().closest('.focus').length) {
      showOps = typeof showOps !== 'undefined' ? showOps : true;
      this.admin.getComponentManager().lockFocus();
      this.admin.getComponentManager().hidePanel();
      this.activeField = field;
      this.admin.showOverlay(field.getElement(), 'field', 110).then(() => {
        if (showOps === true) {
          this.showOps(field);
        }
      });
    }
  }

  public showOps(field:ExoAlchemistField) {
    for (const op in this.fieldOps) {
      if (this.fieldOps.hasOwnProperty(op)) {
        const $link:JQuery = this.fieldOps[op];
        if (field.accessOp(op) === true) {
          $link.removeClass('hide');
        }
        else {
          $link.addClass('hide');
        }
      }
    }
    this.$fieldOps.removeClass('hide');
    this.event('showOps').trigger(this);
  }

  public triggerOp(op:string) {
    if (this.activeField) {
      this.activeField.triggerOp(op);
      this.$fieldOps.addClass('hide');
    }
  }

  public getActive() {
    return this.activeField;
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
