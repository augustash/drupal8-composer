class ExoAlchemistField extends ExoData {
  protected label:string = 'ExoAlchemistField';
  protected doDebug:boolean = false;
  protected admin:ExoAlchemistAdmin;
  protected $element:JQuery;
  protected focused:boolean = false;
  protected clickTimer:number = 0;
  protected clickCount:number = 0;
  protected defaults:ExoSettingsGroupInterface = {
    element: HTMLElement,
    label: '',
    cardinality: 1,
    required: false,
    delta: 0,
    total: 0,
    entity: '',
    bundle: '',
    path: '',
  }

  constructor(id:string, admin:ExoAlchemistAdmin) {
    super(id);
    this.admin = admin;
  }

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      super.build(data).then(data => {
        this.$element = $(data.element);
        this.$element.data('alchemist-field-id', this.getId());
        this.bind();
        resolve(data);
      });
    });
  }

  protected bind() {
    this.$element.on('click', e => {
      e.preventDefault();
      e.stopPropagation();
      this.clickCount++;
      clearTimeout(this.clickTimer);
      this.clickTimer = setTimeout(() => {
        if (this.clickCount > 1) {
          this.admin.getFieldOpsManager().focus(this, false);
          this.admin.getFieldOpsManager().triggerOp('update');
        }
        else {
          this.admin.getFieldOpsManager().focus(this);
        }
        this.clickCount = 0;
      }, 300);
    });
  }

  public accessOp(op:string) {
    switch (op) {
      case 'prev':
        return this.getCardinality() !== 1 && this.getDelta() !== 0;
      case 'next':
        return this.getCardinality() !== 1 && this.getTotal() !== this.getDelta() + 1;
      case 'clone':
        return this.getCardinality() !== 1 && this.getTotal() !== this.getCardinality();
      case 'delete':
        return this.isRequired() === false;
    }
    return true;
  }

  public triggerOp(op:string) {
    const dialogOptions:any = {};
    let url = null;
    switch (op) {
      case 'update':
      case 'clone':
      case 'prev':
      case 'next':
      case 'delete':
        url = Drupal.url('layout_builder/field/' + op + '/' + this.admin.getStorageType() + '/' + this.admin.getStorageId() + '/' + this.admin.getSectionDelta() + '/' + this.admin.getSectionRegion() + '/' + this.getSectionComponent() + '/' + this.getPath() + '?destination=' + Drupal.url(drupalSettings.path.currentPath));
        break;
    }

    if (op === 'update') {
      dialogOptions.exo_modal = {};
      dialogOptions.exo_modal.overlayColor = 'transparent';
      dialogOptions.exo_modal.icon = 'icon-regular-edit';
      dialogOptions.exo_modal.subtitle = 'Make changes to the component field.';
    }

    if (url) {
      Drupal.ajax({
        // progress: { type: 'fullscreen' },
        url: url,
        dialog: dialogOptions,
        dialogType: 'dialog',
        dialogRenderer: 'off_canvas',
      }).execute().done((commands:any, status:any, ajax:any) => {
        this.debug('info', 'Ajax request executed successfully.');
      });
    }
    else {
      this.debug('info', 'URL was not build for operation.');
    }
  }

  public getElement() {
    return this.$element;
  }

  public getLabel() {
    return this.get('label');
  }

  public getCardinality() {
    return this.get('cardinality');
  }

  public isRequired() {
    return this.get('required') === true;
  }

  public getSectionComponent() {
    return this.$element.closest('[data-exo-alchemist-section]').data('exo-alchemist-section');
  }

  public getDelta() {
    return this.get('delta');
  }

  public getTotal() {
    return this.get('total');
  }

  public getPath() {
    return this.get('path');
  }
}
