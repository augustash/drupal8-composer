class ExoAlchemistFieldBreadcrumbs {
  protected admin:ExoAlchemistAdmin;
  protected $fieldBreadcrumbs:JQuery;
  public readonly events = {};

  constructor(admin:ExoAlchemistAdmin) {
    this.admin = admin;
    this.build();
  }

  /**
   * Called by admin after class has been constructed.
   */
  public bind() {
    this.admin.getFieldOpsManager().event('showOps').on('exo.alchemist.field.breadcrumbs', e => {
      this.rebuild();
    });
  }

  /**
   * Build field operations.
   */
  protected build() {
    this.$fieldBreadcrumbs = $('<ul class="exo-alchemist-breadcrumbs" />').appendTo(this.admin.getOverlay());
  }

  /**
   * Build field operations.
   */
  protected rebuild() {
    this.$fieldBreadcrumbs.children().remove();
    const field:ExoAlchemistField = this.admin.getFieldOpsManager().getActive();
    if (field) {
      const $componentFields = this.admin.getComponentManager().getActive().find('.exo-component-field-edit').not(field.getElement().find('.exo-component-field-edit'));
      const $fields = $componentFields.overlaps(field.getElement()).add(field.getElement());
      $('<li class="exo-alchemist-breadcrumb-label">Nested Elements:</li>').appendTo(this.$fieldBreadcrumbs);
      $fields.each((index, element) => {
        const $element = $(element);
        const fieldId = $element.data('alchemist-field-id');
        const field = this.admin.getInstance(fieldId);
        $('<li class="exo-alchemist-breadcrumb-field"><a>' + field.getLabel() + '</a></li>').on('click', e => {
          e.preventDefault();
          e.stopPropagation();
          this.admin.getFieldOpsManager().focus(field);
        }).appendTo(this.$fieldBreadcrumbs);
      });
    }
  }

}
