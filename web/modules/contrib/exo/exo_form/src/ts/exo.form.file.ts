(function ($, Drupal) {

  class ExoFormFile {
    protected $element:JQuery;
    protected $field:JQuery;

    constructor($element:JQuery) {
      this.$element = $element;
      this.$field = this.$element.find('input[type="file"]');
      this.bind();
    }

    protected bind() {
      this.$field.on('change.exo.form.file', () => {
        this.onChange.call(this);
      });
    }

    public onChange(e: JQueryEventObject) {
      if (this.$field.val() != '') {
        // if (!this.$field.closest('.exo-form-file-unmanaged-js').length) {
        //   this.$field.closest('.exo-form-file-item-js').removeClass('ready');
        // }
        var $fileName = this.$field.val().toString().replace(/.*(\/|\\)/, '');
        this.$field.closest('.exo-form-file-input').attr('data-text', $fileName);
      }
    }

  }

  /**
   * Toolbar build behavior.
   */
  Drupal.behaviors.exoFormFile = {
    attach: function (context) {
      $(context).find('.exo-form-file-js').once('exo.form.file').each((index, element) => {
        new ExoFormFile($(element));
      });
    }
  }

})(jQuery, Drupal);
