(function ($, Drupal, document, displace) {

  let exoFormSelectCurrent:ExoFormSelect = null;

  interface ExoFormSelectValue {
    value: string;
    text: string;
    selected: boolean;
    group: boolean;
  }

  class ExoFormSelect {
    public uniqueId:string;
    public $element:JQuery;
    protected $field:JQuery;
    protected $error:JQuery;
    protected $trigger:JQuery;
    protected $wrapper:JQuery<HTMLElement | Text | Comment | Document>;
    protected $label:JQuery;
    protected $caret:JQuery;
    protected $hidden:JQuery;
    protected $dropdown:JQuery;
    protected $dropdownWrapper:JQuery;
    protected $dropdownScroll:JQuery;
    protected debug:boolean = false;
    protected open:boolean = false;
    protected supported:boolean;
    protected isIos:boolean;
    protected isIpadOs:boolean;
    protected isMobile:boolean;
    protected isSafari:boolean;
    protected multiple:boolean;
    protected placeholder:string;
    protected selected:Array<ExoFormSelectValue>;

    constructor($element:JQuery) {
      this.uniqueId = Drupal.Exo.guid();
      this.isIos = Drupal.Exo.isIos();
      this.isIpadOs = Drupal.Exo.isIpadOs();
      this.isMobile = Drupal.Exo.isMobile();
      this.isSafari = Drupal.Exo.isSafari();
      this.supported = this.isSupported();
      this.$element = $element;
      this.$field = this.$element.find('select');
      this.multiple = (this.$field.attr('multiple')) ? true : false;
      this.$error = this.$element.find('.field-error');
      this.$trigger = this.$element.find('.exo-form-select-trigger').attr('id', 'exo-form-select-trigger-' + this.uniqueId).prop('disabled', this.isDisabled());
      this.$wrapper = $element.find('.exo-form-select-wrapper');
      this.$caret = this.$element.find('.exo-form-select-caret');
      this.$label = $element.closest('.exo-form-select').find('label').first();
      this.placeholder = this.$field.attr('placeholder') || (this.multiple ? 'Select Multiple' : 'Select One');
      this.$label.attr('id', 'exo-form-select-label-' + this.uniqueId);
      this.$trigger.text(this.placeholder);
      this.$hidden = this.$element.find('.exo-form-select-hidden');
      if (this.supported) {
        this.$hidden
          .attr('id', 'exo-form-select-hidden-' + this.uniqueId)
          .attr('aria-labelledby', 'exo-form-select-label-' + this.uniqueId + ' exo-form-select-trigger-' + this.uniqueId + ' exo-form-select-hidden-' + this.uniqueId);
        if (this.multiple) {
          this.$hidden.attr('aria-label', 'Select Option');
        }
        else {
          this.$hidden.attr('aria-label', 'Select Options');
        }
        if (this.isDisabled()) {
          this.$hidden.prop('disabled', true).attr('tabindex', '-1');
        }
        // Copy tabindex
        if (this.$field.attr('tabindex')) {
          this.$hidden.attr('tabindex', this.$field.attr('tabindex'));
        }
        // Safari does not focus buttons by default with tab.
        if (!this.isSafari && !this.isIpadOs) {
          this.$field.attr('tabindex', '-1');
        }
        this.$dropdownWrapper = $('#exo-form-select-dropdown-wrapper');
        if (!this.$dropdownWrapper.length) {
          this.$dropdownWrapper = $('<div id="exo-form-select-dropdown-wrapper" class="exo-form"></div>');
          Drupal.Exo.getBodyElement().append(this.$dropdownWrapper);
        }
        this.$dropdown = $('<div class="exo-form-select-dropdown exo-form-input" role="combobox" aria-owns="exo-form-select-scroll-' + this.uniqueId + '" aria-expanded="false"></div>');
        this.$dropdownScroll = $('<ul id="exo-form-select-scroll-' + this.uniqueId + '" class="exo-form-select-scroll" role="listbox" aria-labelledby="exo-form-select-label-' + this.uniqueId + '" tabindex="-1"></div>').appendTo(this.$dropdown);
        // this.$wrapper.append(this.$dropdown);
        this.$dropdownWrapper.append(this.$dropdown);
        this.$dropdown.addClass((this.multiple ? 'is-multiple' : 'is-single'));
        if (this.hasValue() === true) {
          this.$element.addClass('filled');
        }
      }
      else {
        this.$hidden.remove();
      }

      this.build();
      this.evaluate();
      this.bind();
      setTimeout(() => {
        this.$element.addClass('ready');
      });
    }

    public destroy() {
      this.unbind();
      this.$dropdown.remove();
      this.$element.removeData();
    }

    protected build() {
      this.loadOptionsFromSelect();
      this.updateTrigger();

      if (this.debug) {
        this.$field.show();
        setTimeout(() => {
          this.$trigger.trigger('tap');
        }, 500);
      }
    }

    protected evaluate() {
      // Check if field is required.
      if (this.isRequired()) {
        this.$field.removeAttr('required');
        this.$trigger.attr('required', 'required');
      }
    }

    protected bind() {
      this.$trigger.on('focus.exo.form.select', e => {
        // We blur as soon as the focus happens to avoid the cursor showing
        // momentarily within the field.
        this.$trigger.blur();
      })
      .on('click.exo.form.select', e => {
        e.preventDefault();
      })
      .on('tap.exo.form.select', e=> {
        if (this.supported) {
          this.showDropdown();
        }
        else {
          e.preventDefault();
          this.$field.show().focus().hide();
          this.$wrapper.addClass('focused');
        }
      });

      this.$field.on('state:disabled.exo.form.select', e => {
        this.evaluate();
      }).on('state:required.exo.form.select', e => {
        this.evaluate();
      }).on('state:visible.exo.form.select', e => {
        this.evaluate();
      }).on('state:collapsed.exo.form.select', e => {
        this.evaluate();
      });

      if (this.supported) {
        this.$dropdown.on('tap.exo.form.select', '.selector', e => {
          this.onItemTap(e);
        });
        this.$dropdown.on('tap.exo.form.select', '.close', e => {
          this.closeDropdown();
        });
        // Use focusin for IE support.
        this.$hidden.on('focusin.exo.form.select', e => {
          // Close existing.
          if (exoFormSelectCurrent !== null) {
            exoFormSelectCurrent.closeDropdown();
          }
          this.$wrapper.addClass('focused');
        }).on('blur.exo.form.select', e => {
          this.$wrapper.removeClass('focused');
        }).on('keydown.exo.form.select', e => {
          this.onHiddenKeydown(e);
        }).on('keyup.exo.form.select', e => {
          e.preventDefault();
        }).on('click.exo.form.select', e => {
          e.preventDefault();
          this.showDropdown();
        });
        this.$field.on('focus.exo.form.select', e => {
          if (this.isSafari) {
            this.$hidden.focus();
          }
          if (this.isIpadOs) {
            this.$hidden.trigger('click');
          }
        }).on('change.exo.form.select', e => {
          this.loadOptionsFromSelect();
          this.updateTrigger();
        });

        if (this.$field.attr('autofocus')) {
          this.showDropdown();
        }
      }
      else {
        // On unsupported devies we rely on the device select widget and need
        // to update the trigger upon change.
        this.$field.on('change.exo.form.select', e => {
          this.loadOptionsFromSelect();
          this.updateTrigger();
        }).on('focus.exo.form.select', e => {
          // iOS allows this magic.
          if (this.isIos) {
            this.$wrapper.addClass('focused');
          }
        }).on('blur.exo.form.select', e => {
          // iOS allows this magic.
          if (this.isIos) {
            this.$wrapper.removeClass('focused');
          }
        });
      }
    }

    protected unbind() {
      this.$element.off('.exo.form.select');
      this.$dropdown.off('.exo.form.select');
      this.$dropdown.find('.search-input').off('.exo.form.select');
      this.$field.off('.exo.form.select');
      $('body').off('.exo.form.select');
    }

    public onChange(e) {
    }

    public onItemTap(e) {
      var $item = $(e.currentTarget);
      var $wrapper = $item.parent();
      var option = $item.data('option');
      var action;

      if (!this.multiple) {
        $wrapper.find('.active, .selected').removeClass('active selected').removeAttr('aria-selected');
        $item.addClass('active selected').attr('aria-selected', 'true');
        this.changeSelected(option, 'add');
        return this.closeDropdown(true);
      }

      this.$dropdown.find('.selector.selected').removeClass('selected');
      if ($item.is('.active')) {
        action = 'remove';
        $item.removeClass('active');
        $item.find('input').prop('checked', false).trigger('change');
      }
      else {
        action = 'add';
        $item.addClass('active selected');
        $item.find('input').prop('checked', true).trigger('change');
      }
      return this.changeSelected(option, action);
    }

    public onSearchKeydown(e) {
      if (!this.open) {
        e.preventDefault();
        return;
      }
      var $item;

      // TAB - switch to another input.
      if (e.which === 9) {
        // Select current item.
        $item = this.$dropdown.find('.selector.selected');
        if ($item.length) {
          var option = $item.data('option');
          this.changeSelected(option, 'add');
        }

        // Focus on next visible field.
        var $inputs = this.$element.closest('form').find(':input').not('.ignore').not('[tabindex="-1"]');
        var $nextInput = null;
        var currentIndex = $inputs.index(this.$hidden);
        $inputs.each((index, element) => {
          if ($nextInput === null && index > currentIndex) {
            if ($(element).not('[tab-index="-1"]')) {
              $nextInput = $(element);
            }
          }
        });
        if ($nextInput !== null) {
          $nextInput.focus();
          e.preventDefault();
        }
        return this.closeDropdown();
      }

      // ESC - close dropdown.
      if (e.which === 27) {
        return this.closeDropdown(true);
      }

      // ENTER - select option and close when select this.$options are opened
      if (e.which === 13) {
        $item = this.$dropdown.find('.selector.selected');
        if ($item.length) {
          $item.trigger('tap');
        }
        e.preventDefault();
      }

      // ARROW DOWN or RIGHT - move to next not disabled or hidden option
      if (e.which === 40 || e.which === 39) {
        this.highlightOption(this.$dropdown.find('.selector.selected').nextAll('.selector:not(.hide):visible').first(), true, true);
        e.preventDefault();
      }

      // ARROW UP or LEFT - move to next not disabled or hidden option
      if (e.which === 38 || e.which === 37) {
        this.highlightOption(this.$dropdown.find('.selector.selected').prevAll('.selector:not(.hide):visible').first(), true, true);
        e.preventDefault();
      }
    }

    public onSearchKeyup(e) {
      if (!this.open) {
        e.preventDefault();
        return;
      }

      // When user types letters or numbers.
      if (this.isAlphaNumberic(e.which)) {
        const $item = $(e.currentTarget);
        const search = $item.val().toString().toLowerCase();
        if (search) {
          let $items = this.$dropdown.find('.selector');
          if (this.multiple) {
            $items = $items.filter(':not(.active)');
          }
          $items.each((index, element) => {
            const text = $(element).data('option').text.toLowerCase();
            if (text.indexOf(search) >= 0) {
              $(element).removeClass('hide');
            }
            else {
              $(element).addClass('hide');
            }
          });
          this.$dropdown.find('.optgroup').removeClass('hide').each((index, element) => {
            const $optgroup = $(element);
            if (!$optgroup.nextUntil('.optgroup').filter(':not(.hide)').length) {
              $optgroup.addClass('hide');
            }
          });
        }
        else {
          this.$dropdown.find('.hide').removeClass('hide');
        }
        this.highlightOption(this.$dropdown.find('.selector:not(.hide):visible').first());
      }
      e.preventDefault();
    }

    public isAlphaNumberic(key) {
      var inp = String.fromCharCode(key);
      return /[a-zA-Z0-9-_ ]/.test(inp);
    }

    public onHiddenKeydown(e) {
      if (!this.open) {

        // Left.
        if (e.which === 37 && !this.multiple) {
          var $item = this.$dropdown.find('.selector.selected').prevAll('.selector:not(.hide):visible').first();
          this.highlightOption($item, true, true);
          var option = $item.data('option');
          this.changeSelected(option, 'add');
          e.preventDefault();
          return;
        }

        // Right.
        if (e.which === 39 && !this.multiple) {
          var $item = this.$dropdown.find('.selector.selected').nextAll('.selector:not(.hide):visible').first();
          this.highlightOption($item, true, true);
          var option = $item.data('option');
          this.changeSelected(option, 'add');
          e.preventDefault();
          return;
        }

        // Is not alpha/numeric/up/down.
        if (!this.isAlphaNumberic(e.which) && e.which !== 38 && e.which !== 40) {
          e.preventDefault();
          return;
        }

        // TAB - switch to another input.
        if (e.which === 9) {
          return;
        }

        // ARROW DOWN WHEN SELECT IS CLOSED - open dropdown.
        if ((e.which === 38 || e.which === 40)) {
          e.which = 13;
          e.preventDefault();
          this.showDropdown();
          return;
        }

        // ENTER WHEN SELECT IS CLOSED - submit form.
        if (e.which === 13) {
          return;
        }

        if (e.which === 39 || e.which === 37) {
          e.preventDefault();
          return;
        }

        // Screen reader support.
        if (e.which === 17 || e.which === 18 || e.which === 32) {
          e.preventDefault();
          return;
        }

        // When user types letters.
        var nonLetters = [9, 13, 27, 37, 38, 39, 40];
        if ((nonLetters.indexOf(e.which) === -1)) {
          e.preventDefault();
          this.showDropdown();
          var code = e.which || e.keyCode;
          var character = String.fromCharCode(code).toLowerCase();
          this.$dropdown.find('.search-input').val(character);
          this.onSearchKeyup(e);
        }
        e.preventDefault();
      }
    }

    public populateDropdown() {
      this.$dropdownScroll.find('li').remove();

      if (this.$dropdown.find('.search-input').length === 0) {
        this.$dropdown
          .prepend('<div class="close" aria-label="Close">&times;</div>')
          .prepend('<div class="search"><input type="text" class="exo-form-input-item simple search-input" aria-autocomplete="list" aria-controls="exo-form-select-scroll-' + this.uniqueId + '" tabindex="-1"></input></div>')
          .find('.search-input').attr('placeholder', this.placeholder).on('keydown.exo.form.select', e => {
            this.onSearchKeydown(e);
          }).on('keyup.exo.form.select', e => {
            this.onSearchKeyup(e);
          });
      }
      this.$dropdown.find('.search-input').attr('placeholder', 'Search...');
      var options = this.getAllOptions();
      for (var i = 0; i < options.length; i++) {
        var option = options[i];
        const checkboxId = 'exo-form-option-' + this.uniqueId + '-' + i;

        var li = $('<li role="option" role="listitem" tabindex="-1"></li>');

        if (option.group) {
          li.addClass('optgroup');
          li.html('<span>' + option.text + '</span>');
        }
        else if (this.multiple) {
          // Do not show empty value.
          if (!this.isRequired() && option.value === '_none') {
            continue
          }
          li.addClass('selector exo-form-checkbox ready');
          li.html('<span><input id="' + checkboxId + '" type="checkbox" class="form-checkbox"><label for="' + checkboxId + '" class="option">' + option.text + '<div class="exo-ripple"></div></label></span>');
        }
        else {
          li.addClass('selector');
          li.html('<span>' + option.text + '</span>');
        }

        if (option.selected) {
          li.addClass('active').attr('aria-selected', 'true');
          li.find('input').prop('checked', true);
        }

        li.data('option', option);
        this.$dropdownScroll.append(li);
      }

      if (this.multiple) {
        this.$dropdownScroll.find('.form-checkbox').on('change', e => {
          this.highlightOption($(e.currentTarget).closest('.selector'), false);
          if (!this.isIos) {
            this.$dropdown.find('.search-input').focus();
          }
        });
      }

      this.highlightOption();

      Drupal.attachBehaviors(this.$dropdownScroll[0]);
    }

    public getAllOptions(field?) {
      if (!field) {
        return this.selected;
      }
      var vals = [];
      for (var i = 0; i < this.selected.length; i++) {
        vals.push(this.selected[i][field]);
      }
      return vals;
    }

    public loadOptionsFromSelect() {
      this.selected = [];
      this.$field.find('option, optgroup').each((index, element) => {
        const $item = $(element);
        var values:ExoFormSelectValue = {
          value: '',
          text: '',
          selected: false,
          group: false
        };
        if ($item.is('optgroup')) {
          values.text = $(element).attr('label');
          values.group = true;
        }
        else {
          values.value = $item.attr('value');
          values.text = $item.html();
          values.selected = $item.is(':selected');
        }
        this.selected.push(values);
      });
    }

    public updateTrigger() {
      var value = this.getSelectedOptions('value').join('');
      if (value === null || value === '' || value === '_none') {
        this.$trigger.val('');
        this.$trigger.attr('placeholder', this.htmlDecode(this.getSelectedOptions('text').join(', ')));
      }
      else {
        // This change was made because it caused reload issues within Webform
        // email handler screens. It caused the page to reload indefiniately.
        // I do not believe it is used by anything so it has been removed.
        // this.$trigger.val(this.htmlDecode(this.getSelectedOptions('text').join(', '))).trigger('change');
        this.$trigger.val(this.htmlDecode(this.getSelectedOptions('text').join(', ')));
      }
    }

    public getSelectedOptions(field:string):Array<any> {
      var vals = [];
      for (var i = 0; i < this.selected.length; i++) {
        if (this.selected[i].selected) {
          if (field) {
            vals.push(this.selected[i][field]);
          }
          else {
            vals.push(this.selected[i]);
          }
        }
      }
      return vals;
    }

    public changeSelect(option, action) {
      var found = false;
      for (var i = 0; i < this.selected.length; i++) {
        if (!this.multiple) {
          this.selected[i].selected = false;
        }
        if (this.selected[i].value === option.value) {
          found = true;
          if (action === 'add') {
            this.selected[i].selected = true;
          }
          else if (action === 'remove') {
            this.selected[i].selected = false;
          }
        }
      }

      this.updateTrigger();
      if (this.multiple) {
        this.updateSearch();
      }
      this.updateSelect((!found) ? option : null);
    }

    public updateSelect(newOption:ExoFormSelectValue) {
      if (newOption) {
        var option = $('<option></option>')
          .attr('value', newOption.value)
          .html(newOption.text);
        this.$field.append(option);
      }

      this.$field.val(this.getSelectedOptions('value'));
      this.$field.trigger('change', [true]);
      this.$field.trigger('input', [true]);
    }

    public changeSelected(option, action) {
      var found = false;
      var notEmpty = false;
      for (var i = 0; i < this.selected.length; i++) {
        if (!this.multiple) {
          this.selected[i].selected = false;
        }
        if (this.selected[i].value === option.value) {
          found = true;
          if (action === 'add') {
            this.selected[i].selected = true;
          }
          else if (action === 'remove') {
            this.selected[i].selected = false;
          }
        }
        if (this.multiple) {
          if (this.selected[i].value !== '_none' && this.selected[i].selected) {
            notEmpty = true;
          }
        }
      }

      if (this.multiple) {
        for (var i = 0; i < this.selected.length; i++) {
          if (this.selected[i].value === '_none') {
            this.selected[i].selected = !notEmpty;
          }
        }
      }

      this.updateTrigger();
      if (this.multiple) {
        this.updateSearch();
      }
      this.updateSelect((!found) ? option : null);
    }

    public updateSearch() {
      this.$dropdown.find('.search-input').attr('placeholder', this.getSelectedOptions('text').join(', '));
    }

    public highlightOption($item?:JQuery, scroll?:boolean, force?:boolean) {
      if (scroll !== false) {
        scroll = true;
      }
      $item = $item || this.$dropdownScroll.find('.selector.active:eq(0)');
      if (!$item.length && force) {
        $item = this.$dropdownScroll.find('.selector:eq(0)');
      }
      if ($item.length) {
        this.$dropdown.find('.selector.selected').removeClass('selected').removeAttr('aria-selected');
        $item.addClass('selected').attr('aria-selected', 'true');
        this.$dropdown.find('.search-input').attr('aria-activedescendant', $item.attr('id'));
        this.$hidden.attr('aria-activedescendant', $item.attr('id'));
        if (scroll) {
          this.highlightScrollTo($item);
        }
      }
    }

    public highlightScrollTo($item?:JQuery) {
      $item = $item || this.$dropdown.find('.selector.selected');
      if ($item.length) {
        var scrollTop = this.$dropdownScroll.scrollTop();
        var scrollHeight = this.$dropdownScroll.outerHeight();
        var scrollOffset = this.$dropdownScroll.offset().top;
        var itemOffset = $item.offset().top;
        var itemHeight = $item.outerHeight();
        var itemPosition = scrollTop + itemOffset - scrollOffset;

        if (itemPosition + itemHeight > scrollHeight + scrollTop) {
          this.$dropdownScroll.scrollTop(itemPosition + itemHeight - scrollHeight);
        }
        else if (itemPosition < scrollTop) {
          this.$dropdownScroll.scrollTop(itemPosition);
        }
      }
    }

    public hasValue() {
      var value = this.$field.val();
      if (value && typeof value === 'object') {
        return value.length > 0;
      }
      return value !== '' && value !== '- Any -';
    }

    public showDropdown() {
      $('body').trigger('tap');
      if (this.open) {
        return this.closeDropdown();
      }

      // Always populate the dropdown before showing.
      this.populateDropdown();
      this.open = true;
      exoFormSelectCurrent = this;

      this.$dropdownScroll.css('max-height', '');

      if (this.isMobile === true) {
        this.$dropdown.css({
          position: 'fixed',
          maxHeight: '100%',
          top: displace.offsets.top,
          left: displace.offsets.left,
          right: displace.offsets.right,
          bottom: displace.offsets.bottom,
          zIndex: 9999,
        });

        // const viewport = $('meta[name="viewport"]');
        // if (viewport.length) {
        //   const content = viewport.attr('content');
        //   viewport.data('exo-viewport', content);
        //   viewport.attr('content', content + ', maximum-scale=1');
        // }
      }
      else {
        this.positionDropdown();
      }

      Drupal.Exo.lockOverflow(this.$dropdown);
      Drupal.Exo.showShadow({
        opacity: .2,
        // onClick: e => {
        //   this.closeDropdown();
        // },
      });
      this.$element.addClass('active');
      this.$dropdown.addClass('active').find('.search-input').focus();
      this.$dropdownWrapper.attr('class', this.$element.closest('form').attr('class')).addClass('exo-form-select-dropdown-wrapper');
      if (this.isIos) {
        this.$dropdown.find('.search-input').blur();
      }
      this.highlightScrollTo();

      setTimeout(() => {
        this.$element.addClass('animate');
        this.$dropdown.addClass('animate').attr('aria-expanded', 'true');
      }, 50);
      this.windowHideDropdown();
    }

    public windowHideDropdown() {
      $('body').on('tap' + '.' + this.uniqueId, e => {
        if (!this.open) {
          return;
        }
        if ($(e.target).closest(this.$dropdown).length) {
          return;
        }
        this.closeDropdown();
      });
    }

    public positionDropdown() {
      if (this.open === true && this.isMobile !== true) {
        const windowTop = Drupal.Exo.$window.scrollTop();
        const windowHeight = Drupal.Exo.$window.height();
        const dropdownTop = this.$wrapper.offset().top;
        let dropdownHeight = this.$dropdown.outerHeight();
        const fixedHeaderHeight = ($('.exo-fixed-header .exo-fixed-region').outerHeight() || 0) + displace.offsets.top;

        this.$dropdown.removeClass('from-top from-bottom').css({
          left: this.$trigger.offset().left,
          width: this.$trigger.outerWidth(),
        });
        let direction = '';

        // Has enough space to move downward.
        if (windowHeight - fixedHeaderHeight < dropdownHeight) {
          dropdownHeight = windowHeight - fixedHeaderHeight - this.$dropdown.find('.search-input').height() - 20;
          this.$dropdownScroll.css('max-height', dropdownHeight);
          Drupal.Exo.$window.scrollTop(dropdownTop - fixedHeaderHeight - 10);
          direction = 'top';
        }
        // Does not have enough space to move upward before hitting window top.
        else if (dropdownTop + dropdownHeight > windowTop + windowHeight) {
          if (dropdownTop - dropdownHeight < windowTop + fixedHeaderHeight) {
            Drupal.Exo.$window.scrollTop(dropdownTop - fixedHeaderHeight - 10);
            direction = 'top';
          }
          else {
            direction = 'bottom';
          }
        }
        else {
          direction = 'top';
        }

        switch (direction) {
          case 'top':
            this.$dropdown.addClass('from-top').css('top', this.$trigger.offset().top);
            break;
          case 'bottom':
            this.$dropdown.addClass('from-bottom').css('bottom', windowHeight - (this.$trigger.offset().top + this.$trigger.outerHeight()));
            break;
        }
      }
    }

    public closeDropdown(focus?:boolean) {
      if (this.open === true) {
        this.open = false;
        exoFormSelectCurrent = null;
        this.$dropdown.attr('aria-expanded', 'false');
        this.$dropdown.removeClass('animate').find('.search-input').val('');
        this.$element.removeClass('animate');
        this.updateSearch();
        Drupal.Exo.hideShadow();
        $('body').off('.' + this.uniqueId);
        setTimeout(() => {
          this.$dropdownScroll.find('.hide').removeClass('hide');
          Drupal.Exo.unlockOverflow(this.$dropdown);
          if (this.hasValue() === true) {
            this.$element.addClass('filled');
          }
          else {
            this.$element.removeClass('filled');
          }
          if (this.open === false) {
            this.$element.removeClass('active');
            this.$dropdown.removeClass('active').removeAttr('style');
          }

          if (this.isMobile === true) {
            this.$dropdown.removeAttr('style');

            // const viewport = $('meta[name="viewport"]');
            // if (viewport.length) {
            //   viewport.attr('content', viewport.data('exo-viewport'));
            // }
          }
        }, 350);
        if (focus) {
          setTimeout(() => {
            this.$hidden.trigger('focus', [1]);
          });
        }
      }
    }

    public htmlDecode(value:string):string {
      return $('<div/>').html(value).text().replace(/&amp;/g, '&');
    }

    public isRequired():boolean {
      var required = this.$field.attr('required');
      return typeof required !== 'undefined';
    }

    public isDisabled():boolean {
      return this.$field.is(':disabled');
    }

    public isSupported():boolean {
      if (Drupal.Exo.isIE() === true) {
        return document.documentMode >= 8;
      }
      return this.isIos !== true;
    }
  }

  /**
   * Toolbar build behavior.
   */
  Drupal.behaviors.exoFormSelect = {
    instances: {},
    once: false,

    attach: function(context) {
      $(context).find('.form-item.exo-form-select-js').once('exo.form.select').each((index, element) => {
        const select = new ExoFormSelect($(element));
        Drupal.behaviors.exoFormSelect.instances[select.uniqueId] = select;
      });
      if (this.once === false) {
        this.once === true;
        Drupal.Exo.addOnResize('exo.form', function () {
          for (const key in Drupal.behaviors.exoFormSelect.instances) {
            if (Drupal.behaviors.exoFormSelect.instances.hasOwnProperty(key)) {
              const select = Drupal.behaviors.exoFormSelect.instances[key];
              select.positionDropdown();
            }
          }
        });
      }
    },

    detach: function (context, settings, trigger) {
      if (trigger === 'unload' && context !== document) {
        const $selects = $(context).find('.form-item.exo-form-select-js');
        for (const key in Drupal.behaviors.exoFormSelect.instances) {
          if (Drupal.behaviors.exoFormSelect.instances.hasOwnProperty(key)) {
            const select = Drupal.behaviors.exoFormSelect.instances[key];
            if (select.$element.is($selects)) {
              select.destroy();
              delete Drupal.behaviors.exoFormSelect.instances[key];
            }
          }
        }
      }
    }
  }

})(jQuery, Drupal, document, Drupal.displace);
