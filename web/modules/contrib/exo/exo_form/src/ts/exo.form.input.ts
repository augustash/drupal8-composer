(function ($, Drupal) {

  class ExoFormInput {
    protected $element:JQuery;
    protected $field:JQuery;
    protected field:HTMLInputElement;
    protected $error:JQuery;

    constructor($element:JQuery) {
      this.$element = $element;
      this.$error = this.$element.find('.field-error');
      this.$field = this.$element.find('.exo-form-input-item-js');
      this.field = this.$field[0] as HTMLInputElement;
      this.$field.each(() => {
        const $prefix = this.$element.find('.field-prefix');
        const $suffix = this.$element.find('.field-suffix');
        const $suffixDescription = $suffix.find('.description');
        const $input = this.$element.find('.field-input');
        if ($suffix.length) {
          $suffix.after('<div class="exo-form-input-line" />');
        }
        else if ($prefix.length) {
          $input.after('<div class="exo-form-input-line" />');
        }
        else {
          this.$field.after('<div class="exo-form-input-line" />');
        }
        if ($prefix.length) {
          $prefix.on('click.exo.form.input', e => {
            this.$field.focus();
          });
        }
        if ($suffix.length) {
          $suffix.on('click.exo.form.input', e => {
            this.$field.focus();
          });
        }
        if ($suffixDescription.length) {
          $suffixDescription.appendTo(this.$element);
        }
      });
      if (this.hasError()) {
        this.$element.addClass('invalid');
      }

      this.bind();

      if (this.hasValue() || this.isAutofocus() || this.hasPlaceholder() || this.hasBadInput()) {
        this.$element.addClass('active');
      }
      setTimeout(() => {
        this.$element.addClass('ready');
      });
    }

    public destory() {
      this.unbind();
      this.$element.removeData();
    }

    protected bind() {
      this.$field.on('change.exo.form.input', () => {
        this.onChange.call(this);
      });
      this.$field.on('focus.exo.form.input', () => {
        this.onFocus.call(this);
      });
      this.$field.on('blur.exo.form.input', () => {
        this.onBlur.call(this);
      });
    }

    protected unbind() {
      this.$field.off('.exo.form.input');
    }

    public onChange(e:JQueryEventObject) {
      if (this.hasValue() || this.hasPlaceholder()) {
        this.$element.addClass('active');
      }
      this.validate();
    }

    public onFocus(e:JQueryEventObject) {
      if (!this.isReadonly()) {
        this.$element.addClass('active focused');
      }
    }

    public onBlur(e:JQueryEventObject) {
      var classes = 'focused';
      if ((!this.hasValue() || (!this.hasValue() && this.isValid())) && !this.hasPlaceholder()) {
        classes += ' active';
      }
      this.$element.removeClass(classes);
      this.validate();
    }

    public validate() {
      this.$element.removeClass('valid invalid').removeAttr('data-error');
      if (this.isValid()) {
        if (this.hasValue()) {
          this.$element.addClass('valid');
        }
      }
      else {
        this.$element.addClass('invalid').attr('data-error', this.field.validationMessage);
      }
    }

    public hasValue() {
      var value = this.$field.val();
      return value !== '' && value !== '- Any -';
    }

    public hasPlaceholder() {
      var placeholder = this.$field.attr('placeholder');
      return typeof placeholder !== 'undefined' && placeholder.length > 0;
    }

    public hasError() {
      return this.$field.hasClass('error');
    }

    public hasBadInput() {
      return this.field.validity.badInput === true;
    }

    public isValid() {
      return this.field.validity.valid === true;
    }

    public isAutofocus() {
      var autofocus = this.$field.attr('autofocus');
      return typeof autofocus !== 'undefined';
    }

    public isReadonly() {
      var readonly = this.$field.attr('readonly');
      return typeof readonly !== 'undefined';
    }
  }

  /**
   * Toolbar build behavior.
   */
  Drupal.behaviors.exoFormInput = {
    attach: function(context) {
      $(context).find('.form-item.exo-form-input-js').once('exo.form.input').each((index, element) => {
        new ExoFormInput($(element));
      });
    }
  }

})(jQuery, Drupal);
