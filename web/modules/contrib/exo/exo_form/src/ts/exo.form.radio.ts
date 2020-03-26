(function ($, Drupal) {

  class ExoFormRadio {
    protected $element:JQuery;
    protected $field:JQuery;

    constructor($element:JQuery) {
      this.$element = $element;
      this.$field = this.$element.find('input:first');
      const $label = this.$element.find('label');
      if (!$label.length) {
        this.$field.parent().append('<label for="' + this.$field.attr('id') + '" class="option"><div class="exo-ripple"></div></label>');
      }
      else if (!$label.hasClass('option')) {
        $label.addClass('option');
      }
      if (this.$field.prop('checked')) {
        this.$element.addClass('active');
      }
      this.bind();
      setTimeout(() => {
        this.$element.addClass('ready');
      });
    }

    public destory() {
      this.unbind();
      this.$element.removeData();
    }

    protected bind() {
      this.$field.on('change.exo.form.radio', () => {
        this.onChange.call(this);
      }).on('focus.exo.form.radio', () => {
        this.$element.addClass('focused');
      }).on('blur.exo.form.radio', () => {
        this.$element.removeClass('focused');
      });
    }

    protected unbind() {
      this.$field.off('.exo.form.radio');
    }

    public onChange(e:JQueryEventObject) {
      // .form-wrapper is used as sometimes radios are wrapped in other
      // elements.
      this.$element.closest('.exo-form-radios, .form-wrapper').find('.exo-form-radio.active').removeClass('active');
      if (this.$field.prop('checked')) {
        this.$element.addClass('active');
      }
    }
  }

  /**
   * Toolbar build behavior.
   */
  Drupal.behaviors.exoFormRadio = {
    attach: function(context) {
      $(context).find('.exo-form-radio-js').once('exo.form.radio').each((index, element) => {
        new ExoFormRadio($(element));
      });
    }
  }

})(jQuery, Drupal);
