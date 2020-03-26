interface ExoModalProcessBarInterface {
  currentTime?:number;
  maxHideTime?:number;
  hideEta?:number;
  el?:JQuery;
  updateProgress?:Function;
}

class ExoModal extends ExoData {
  protected label:string = 'Modal';
  protected doDebug:boolean = false;
  protected defaults:ExoSettingsGroupInterface = {
    preset: null,
    title: '',
    subtitle: '',
    overlayColor: 'rgba(0, 0, 0, 0.4)',
    icon: null,
    iconText: null,
    rtl: false,
    inherit: false,
    width: 600,
    height: null,
    top: null,
    bottom: null,
    right: null,
    left: null,
    borderBottom: true,
    padding: 0,
    ajax: null,
    contentAjax: null,
    contentSelector: null,
    radius: 3,
    zindex: 999,
    iframe: false,
    iframeHeight: 400,
    iframeWidth: 800,
    iframeResponsive: false,
    iframeURL: null,
    focusInput: true,
    group: '',
    nest: false,
    loop: false,
    arrowKeys: true,
    navigateCaption: true,
    navigateArrows: true, // Boolean, 'closeToModal', 'closeScreenEdge'
    history: false,
    restoreDefaultContent: false,
    autoOpen: 0, // Boolean, Number
    bodyOverflow: true,
    fullscreen: false,
    openFullscreen: false,
    openTall: false,
    closeOnEscape: true,
    closeButton: true,
    closeInBody: false,
    smartActions: true,
    appendTo: Drupal.ExoModal.globalWrapper, // or false
    appendToOverlay: Drupal.ExoModal.globalWrapper, // or false
    appendToNavigate: Drupal.ExoModal.globalWrapper, // or false
    class: '',
    appendToClosest: false, // or false
    overlay: true,
    overlayClose: true,
    timeout: false,
    timeoutProgressbar: false,
    pauseOnHover: false,
    transitionIn: 'comingIn', // comingIn, bounceInDown, bounceInUp, fadeInDown, fadeInUp, fadeInLeft, fadeInRight, flipInX
    transitionOut: 'comingOut', // comingOut, bounceOutDown, bounceOutUp, fadeOutDown, fadeOutUp, , fadeOutLeft, fadeOutRight, flipOutX
    transitionInOverlay: 'fadeIn',
    transitionOutOverlay: 'fadeOut',
    transitionInPanel: 'fadeInLeft',
    transitionOutPanel: 'fadeOutLeft',
    onFullscreen: function () {},
    onResize: function () {},
    onOpening: function () {},
    onOpened: function () {},
    onClosing: function () {},
    onClosed: function () {},
    afterRender: function () {}
  };
  protected inherit:Array<string> = [
    'top',
    'right',
    'bottom',
    'left',
    'openTall',
    'openFullscreen'
  ];
  protected readonly events = {
    opening: new ExoEvent<ExoModal>(),
    opened: new ExoEvent<ExoModal>(),
    closing: new ExoEvent<ExoModal>(),
    closed: new ExoEvent<ExoModal>()
  };
  protected readonly states:ExoSettingsGroupInterface = {
    CLOSING: 'closing',
    CLOSED: 'closed',
    OPENING: 'opening',
    OPENED: 'opened',
    DESTROYED: 'destroyed'
  };
  protected data:any;
  protected name:string = 'exo-modal';
  protected class:string;
  protected content:string;
  protected built:boolean = false;
  protected state:string;
  protected width:number = 0;
  protected widthOffset:number = 0;
  protected timer:number = null;
  protected timerTimeout:number = null;
  protected progressBar:ExoModalProcessBarInterface = null;
  protected isPaused:boolean = false;
  protected isFullscreen:boolean = false;
  protected isCompressed:boolean = false;
  protected isTall:boolean = false;
  protected isSetHeight:boolean = false;
  protected headerHeight:number = 0;
  protected footerHeight:number = 0;
  protected modalHeight:number = 0;
  protected autoOpenProcessed:boolean = false;
  protected panelOpen:boolean = false;
  protected $trigger:JQuery;
  protected $element:JQuery;
  protected $overlay:JQuery;
  protected $navigate:JQuery;
  protected $wrap:JQuery;
  protected $header:JQuery;
  protected $footer:JQuery;
  protected $container:JQuery;
  protected $content:JQuery;
  protected $contentSelection:JQuery;
  protected $contentPlaceholder:JQuery;
  protected $sectionHeader:JQuery;
  protected $sectionFooter:JQuery;

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      if (data.preset && typeof drupalSettings.exoModal.presets === 'object' && drupalSettings.exoModal.presets[data.preset]) {
        data = jQuery.extend(true, {}, drupalSettings.exoModal.presets[data.preset], data);
      }
      data = jQuery.extend(true, {}, Drupal.ExoModal.getSettingsGroup('defaults'), data);
      super.build(data, false).then(data => {
        if (data !== null) {
          this.debug('log', 'Build: Act', '[' + this.id + ']', data);
          const $trigger = this.getTriggerElement().once('exo.modal');
          if ($trigger.length) {
            this.debug('log', 'Build: Bind Trigger', '[' + this.id + ']');
            this.bindTrigger();
          }
          const $element = this.getElement().once('exo.modal');
          if ($element.length) {
            this.debug('log', 'Build: Found Element', '[' + this.id + ']');
            this.state = this.states.CLOSED;
          }
          // We resolve true if either the trigger or the element has been found.
          // This will only occur once per trigger/element as we use .once(). We
          // do this due to ajax as the trigger may exist but the modal element
          // may not yet.
          if ($trigger.length || $element.length) {
            resolve(data);
          }
          else {
            // Resolving FALSE we leave this in drupalSettings and let it process
            // again. The reason for this is this can be called before the element
            // has actually been added to the page.
            resolve(null);
          }
        }
        else {
          resolve(data);
        }
      }, reject);
    });
  }

  public afterBuild() {
    if (this.getElement().length) {
      if (this.get('autoOpen')) {
        if (Drupal.Exo.isInitialized()) {
          this.open();
        }
        else {
          Drupal.Exo.event('ready').on('exo.modal.' + this.getId(), () => {
            Drupal.Exo.event('ready').off('exo.modal.' + this.getId());
            this.open();
          });
        }
      }
      else if (this.getElement().find('.exo-form-container-error').length) {
        this.open();
        const $messages = $('.messages.error');
        if ($messages.length && !$messages.closest('.exo-modal').length) {
          this.$content.prepend($messages);
        }
      }
    }
  }

  protected rebuildContent() {
    this.built = false;
    return this;
  }

  protected buildContent() {
    if (this.$element && this.$element.length && !this.built) {
      this.built = true;
      this.debug('log', 'Build', '[' + this.getId() + ']', this.getData());

      if (this.get('inherit') === true) {
        // Inherit will use some settings from currently active modal.
        const focusedModal = Drupal.ExoModal.getVisibleFocus();
        if (focusedModal) {
          _.each(this.inherit, name => {
            this.set(name, focusedModal.get(name));
          });
          this.set('zindex', focusedModal.get('zindex') + 1);
          this.set('overlayColor', 'transparent');
          this.set('nest', true);
        }
      }
      if (this.$element.hasClass(this.name + '-hidden')) {
        this.$element.removeClass(this.name + '-hidden');
      }
      this.class = (this.$element.attr('class') !== undefined) ? this.$element.attr('class') : '';
      this.content = this.$element.html();
      this.$overlay = $('<div class="' + this.name + '-overlay" style="background-color:' + this.get('overlayColor') + '"></div>');
      this.$navigate = $('<div class="' + this.name + '-navigate"><div class="' + this.name + '-navigate-caption">Use</div><button class="' + this.name + '-navigate-prev"></button><button class="' + this.name + '-navigate-next"></button></div>');
      this.$element.attr('aria-hidden', 'true');
      this.$element.attr('aria-labelledby', this.getId());
      this.$element.attr('role', 'dialog');
      this.$container = this.$element.find('.' + this.name + '-container');
      this.$content = this.$element.find('.' + this.name + '-content');
      this.$sectionHeader = this.$element.find('.' + this.name + '-section-header');
      this.$sectionFooter = this.$element.find('.' + this.name + '-section-footer');

      if (this.get('class') !== '') {
        this.$element.addClass(this.get('class'));
      }

      if (!this.$element.hasClass(this.name)) {
        this.$element.addClass(this.name);
      }

      if (this.get('group') !== '') {
        this.$element.attr('data-' + this.name + '-group', this.get('group'));
      }
      if (this.get('loop') === true) {
        this.$element.attr('data-' + this.name + '-loop', 'true');
      }

      if (this.get('appendTo') !== false) {
        if (this.get('appendToClosest') === true) {
          this.$element.appendTo(this.$element.closest(this.get('appendTo')));
        }
        else {
          this.$element.appendTo(this.get('appendTo'));
        }
        // Of modal is within eXo content, we need to offset it so that it
        // accounts for displacement.
        if (this.$element.closest('.exo-content').length) {
          this.$element.css({
            top: displace.offsets.top,
            bottom: displace.offsets.bottom,
            left: displace.offsets.left,
            right: displace.offsets.right,
          });
        }
      }

      if (this.get('iframe') === true) {
        this.$content.html('<iframe class="' + this.name + '-iframe"></iframe>');

        if (this.get('iframeHeight') !== null) {
          if (this.get('iframeHeight') !== null && this.get('iframeWidth') !== null && this.get('iframeResponsive') === true) {
            const paddingBottom = this.getIframeResponsivePadding();
            this.$content.find('.' + this.name + '-iframe').css('width', '100%').wrap('<div class="' + this.name + '-iframe-responsive" />');
            this.$content.find('.' + this.name + '-iframe-responsive').css('padding-bottom', paddingBottom + '%');
          }
          else {
            this.$content.find('.' + this.name + '-iframe').css('height', this.get('iframeHeight'));
          }
        }
      }

      this.$wrap = this.$element.find('.' + this.name + '-wrap');

      if (this.get('zindex') !== null) {
        this.setZindex(this.get('zindex'));
      }

      if (this.get('radius') !== '') {
        this.$element.css('border-radius', this.get('radius'));
      }

      if (this.get('padding') !== '') {
        this.$content.css('padding', this.get('padding'));
      }

      if (this.get('rtl') === true) {
        this.$element.addClass(this.name + '-rtl');
      }

      if (this.get('openFullscreen') !== false) {
        this.isFullscreen = true;
        this.$element.addClass('isFullscreen');
      }

      if (this.get('height')) {
        this.isSetHeight = true;
        this.$element.addClass('isSetHeight');
      }

      if (this.get('openTall') === true) {
        this.isTall = true;
        this.$element.addClass('isTall');
      }

      if (this.get('closeInBody') !== false) {
        const $close = $('<div class="exo-container-button ' + this.get('closeInBody') + '"><a href="javascript:void(0)" class="' + this.name + '-button ' + this.name + '-button-close" data-' + this.name + '-close></a></div>');
        this.$container.prepend($close);
        setTimeout(() => {
          this.widthOffset += $close.outerWidth();
        });
      }

      this.createHeader();
      this.createFooter();
      this.buildPanels();
      this.recalcSize();
      this.recalcVerticalPos();
      this.recalcHorizontalPos();
      this.callCallback('afterRender');
    }
  }

  protected setZindex(zindex:number) {
    zindex = zindex + (Drupal.ExoModal.getVisible(true).count() * 3);
    this.set('zindex', zindex);
    this.$overlay.css('z-index', zindex - 2);
    this.$navigate.css('z-index', zindex - 1);
    this.$element.css('z-index', zindex);
  }

  protected createHeader() {
    // This can be called multiple times and should be rebuilt each time.
    this.$element.find('.' + this.name + '-header').remove();

    this.$header = $('<div class="' + this.name + '-header"><h2 class="' + this.name + '-header-title">' + this.get('title') + '</h2><p class="' + this.name + '-header-subtitle"><span>' + this.get('subtitle') + '</span></p><div class="' + this.name + '-header-buttons"></div></div>');

    if (this.get('closeButton') === true) {
      this.$header.find('.' + this.name + '-header-buttons').append('<a href="javascript:void(0)" class="' + this.name + '-button ' + this.name + '-button-close" data-' + this.name + '-close></a>');
    }

    if (this.get('fullscreen') === true) {
      this.$header.find('.' + this.name + '-header-buttons').append('<a href="javascript:void(0)" class="' + this.name + '-button ' + this.name + '-button-fullscreen" data-' + this.name + '-fullscreen></a>');
    }

    if (this.get('timeoutProgressbar') === true) {
      this.$header.prepend('<div class="' + this.name + '-progressbar"><div></div></div>');
    }

    if (this.get('subtitle') === '') {
      this.$header.addClass(this.name + '-no-subtitle');
    }

    this.$element.css('border-bottom-width', '');
    if (this.get('title') !== "" || this.get('closeButton') === true || this.get('fullscreen') || this.get('timeoutProgressbar')) {
      if (this.get('borderBottom') === true) {
        this.$element.css('border-bottom-width', '3px');
      }
      if (this.get('icon') !== null || this.get('iconText') !== null) {
        this.$header.prepend('<i class="' + this.name + '-header-icon"></i>');
        if (this.get('icon') !== null) {
          this.$header.find('.' + this.name + '-header-icon').addClass(this.get('icon'));
        }
        if (this.get('iconText') !== null) {
          this.$header.find('.' + this.name + '-header-icon').html(this.get('iconText'));
        }
      }
      if (!this.get('closeInBody')) {
        this.$element.css('overflow', 'hidden').prepend(this.$header);
      }
    }
  }

  public createFooter() {
    // This can be called multiple times and should be rebuilt each time.
    this.$element.find('.' + this.name + '-footer').remove();

    if (this.get('smartActions') === true) {
      const $actions = this.$element.find('.form-actions');
      if ($actions.length) {
        $actions.last().addClass('exo-modal-actions');
      }
      else {
        this.$element.find('.exo-form-container-button--primary').addClass('exo-modal-actions');
      }

      this.$footer = $('<div class="' + this.name + '-footer"></div>');
      $('.exo-modal-views-view .exo-form-container-form-actions').addClass('exo-modal-actions');
      const $inputs = this.$element.find('.exo-modal-actions:last input, .exo-modal-actions button, .exo-modal-actions a');
      if ($inputs.length) {
        this.$element.append(this.$footer);
        this.$element.css('border-bottom-width', 0);
        this.set('borderBottom', false);
        if ($inputs.length) {

          const onClick = (e, $original) => {
            const text = $original.text().toLowerCase();
            if ($original.is('a')) {
              if (text === 'cancel') {
                this.close();
              }
              else {
                window.location.href = $original.attr('href');
              }
            }
            else {
              $original.trigger('mousedown').trigger('mouseup').trigger('click');
            }
          }

          $inputs.each((index, element) => {
            const $original = $(element);
            $original.clone()
              .attr('id', $original.attr('id') + '-clone')
              .attr('name', $original.attr('name') + '-clone')
              .attr('data-drupal-selector', $original.attr('data-drupal-selector') + '-clone')
              .removeAttr('class')
              .addClass('exo-modal-action' + (index === 0 ? ' primary': ''))
              .on('click', e => {
                e.preventDefault();
                const buttonDelay = $original.data('exo-modal-action-delay');
                if (buttonDelay) {
                  if (typeof this.events[buttonDelay] !== 'undefined') {
                    this.event(buttonDelay).on('exo.modal.footer.' + index, e => {
                      this.event(buttonDelay).off('exo.modal.footer.' + index);
                      onClick(e, $original);
                    });
                  }
                  else if (buttonDelay === parseInt(buttonDelay)) {
                    setTimeout(() => {
                      onClick(e, $original);
                    }, buttonDelay);
                  }
                }
                else {
                  setTimeout(() => {
                    onClick(e, $original);
                  });
                }
              }).appendTo(this.$footer);
          });
          $inputs.closest('.exo-form-element-type-actions').addClass('exo-modal-hide');
          $inputs.closest('.exo-modal-actions').addClass('exo-modal-hide');
          $inputs.closest('.exo-form-container-exo-modal-actions').addClass('exo-modal-hide');
        }
      }
    }
  }

  protected buildPanels() {
    const $panels = this.$element.find('.exo-modal-panel-trigger').once('exo.modal');
    if ($panels.length) {
      $panels.each((index, element) => {
        const $trigger = $(element);
        const id = $trigger.data('exo-modal-panel');
        const $pane = this.$element.find('.exo-modal-panel[data-exo-modal-panel=' + id + ']').hide();
        $trigger.find('.hide').hide();
        $trigger.on('click.exo.modal', e => {
          e.preventDefault();
          if ($trigger.hasClass('active')) {
            this.hidePanel($trigger);
          }
          else {
            this.hidePanels();
            this.showPanel($trigger);
          }
        });
      });
      this.event('closed').on('exo.modal.panels', e => {
        this.hidePanels(false);
      });
    }
  }

  protected showPanel($trigger:JQuery, animate?:boolean) {
    this.panelOpen = true;
    const id = $trigger.data('exo-modal-panel');
    const $pane = this.$element.find('.exo-modal-panel[data-exo-modal-panel=' + id + ']');
    $trigger.data('exo-modal-width', this.get('width'));
    const newWidth = $trigger.data('exo-modal-panel-width');
    if (newWidth) {
      this.setWidth(newWidth);
    }
    const transitionIn = this.get('transitionInPanel');
    const transitionOut = this.get('transitionOutPanel');
    if (animate !== false && transitionIn && transitionOut && Drupal.Exo.animationEvent !== undefined) {
      $pane.show().addClass('transitionIn exo-animate-' + transitionIn);
      this.$content.addClass('transitionOut exo-animate-' + transitionOut);

      $pane.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
        if ($pane.get(0) === e.currentTarget) {
          $pane.off(Drupal.Exo.animationEvent + '.exo.modal');
          $pane.removeClass('exo-animate-' + transitionIn + ' transitionIn');
          this.$content.removeClass('exo-animate-' + transitionOut + ' transitionOut');
          $pane.addClass('active');
        }
      });

      $trigger.addClass('transitionOut exo-animate-' + transitionOut);
      $trigger.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
        if ($trigger.get(0) === e.currentTarget) {
          $trigger.off(Drupal.Exo.animationEvent + '.exo.modal');
          $trigger.removeClass('transitionOut exo-animate-' + transitionOut);
          $trigger.find('.show').hide();
          $trigger.find('.hide').show();
          $trigger.addClass('active');
          $trigger.addClass('transitionIn exo-animate-' + transitionIn);
          $trigger.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
            if ($trigger.get(0) === e.currentTarget) {
              $trigger.off(Drupal.Exo.animationEvent + '.exo.modal');
            }
          });
        }
      });
    }
    else {
      $trigger.find('.show').hide();
      $trigger.find('.hide').show();
      $trigger.removeClass('active');
      $pane.show().addClass('active');
    }
  }

  protected hidePanel($trigger:JQuery, animate?:boolean) {
    this.panelOpen = false;
    const id = $trigger.data('exo-modal-panel');
    const $pane = this.$element.find('.exo-modal-panel[data-exo-modal-panel=' + id + ']');
    this.setWidth($trigger.data('exo-modal-width'));
    const transitionIn = this.get('transitionInPanel');
    const transitionOut = this.get('transitionOutPanel');
    if (animate !== false && transitionIn && transitionOut && Drupal.Exo.animationEvent !== undefined) {
      $pane.addClass('transitionOut exo-animate-' + transitionOut);
      this.$content.addClass('transitionIn exo-animate-' + transitionIn);
      $pane.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
        if ($pane.get(0) === e.currentTarget) {
          $pane.off(Drupal.Exo.animationEvent + '.exo.modal');
          $pane.removeClass('exo-animate-' + transitionOut  + ' transitionOut');
          this.$content.removeClass('exo-animate-' + transitionIn + ' transitionIn');
          $pane.hide().removeClass('active');
        }
      });

      $trigger.addClass('transitionOut exo-animate-' + transitionOut);
      $trigger.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
        if ($trigger.get(0) === e.currentTarget) {
          $trigger.off(Drupal.Exo.animationEvent + '.exo.modal');
          $trigger.removeClass('transitionOut exo-animate-' + transitionOut);
          $trigger.find('.show').show();
          $trigger.find('.hide').hide();
          $trigger.removeClass('active');
          $trigger.addClass('transitionIn exo-animate-' + transitionIn);
          $trigger.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
            if ($trigger.get(0) === e.currentTarget) {
              $trigger.off(Drupal.Exo.animationEvent + '.exo.modal');
            }
          });
        }
      });
    }
    else {
      $trigger.find('.show').show();
      $trigger.find('.hide').hide();
      $trigger.removeClass('active');
      $pane.hide().removeClass('active');
    }
  }

  protected hidePanels(animate?:boolean) {
    if (this.hasOpenPanel()) {
      const $activeTrigger = this.$element.find('.exo-modal-panel-trigger.active');
      if ($activeTrigger.length) {
        this.hidePanel($activeTrigger, animate);
      }
    }
  }

  public hasOpenPanel():boolean {
    return this.panelOpen === true;
  }

  public set(key, value):any {
    super.set(key, value)
    switch (key) {
      case 'title':
      case 'closeButton':
      case 'fullscreen':
      case 'subtitle':
      case 'borderBottom':
      case 'icon':
      case 'iconText':
        this.createHeader();
        this.bindElement();
        break;
    }
    return this;
  }

  protected getIframeResponsivePadding() {
    let percent = 0;
    let width = parseInt(this.get('iframeWidth'));
    let height = parseInt(this.get('iframeHeight'));
    if (width > height) {
      percent = (height / width) * 100;
    }
    if (height > width) {
      percent = (width / height) * 100;
    }
    return percent;
  }

  public setWidth(width:string|number) {
    this.set('width', width);
    this.recalcSize();
  }

  protected recalcSize() {
    var windowWidth = Drupal.Exo.$window.width() - displace.offsets.left - displace.offsets.right;
    var modalWidth = this.get('left') && this.get('right') ? windowWidth - (Drupal.Exo.getMeasurementValue(this.get('left')) || 0) - (Drupal.Exo.getMeasurementValue(this.get('right')) || 0) : this.get('width');
    this.$element.css('max-width', modalWidth);
    var modalHeight = this.get('height');
    if (modalHeight) {
      this.$element.css('max-height', modalHeight);
    }
    if (windowWidth < modalWidth + this.widthOffset) {
      this.isCompressed = true;
      this.$element.addClass('isCompressed');
    }
    else {
      this.isCompressed = false;
      this.$element.removeClass('isCompressed');
    }
    if (Drupal.Exo.isIE()) {
      if (modalWidth.toString().split('%').length > 1) {
        modalWidth = this.$element.outerWidth();
      }
    }
  }

  protected recalcVerticalPos(first?:boolean) {
    if (this.get('top') !== null && this.get('top') !== false) {
      this.$element.css('margin-top', this.get('top'));
      if (this.get('top') === 0) {
        this.$element.css({
          borderTopRightRadius: 0,
          borderTopLeftRadius: 0
        });
      }
    } else {
      if (first === false) {
        this.$element.css({
          marginTop: '',
          borderRadius: this.get('radius')
        });
      }
    }
    if (this.get('bottom') !== null && this.get('bottom') !== false) {
      this.$element.css('margin-bottom', this.get('bottom'));
      if (this.get('bottom') === 0) {
        this.$element.css({
          borderBottomRightRadius: 0,
          borderBottomLeftRadius: 0
        });
      }
    } else {
      if (first === false) {
        this.$element.css({
          marginBottom: '',
          borderRadius: this.get('radius')
        });
      }
    }
  }

  protected recalcHorizontalPos(first?:boolean) {
    if (this.get('left') !== null && this.get('left') !== false) {
      this.$element.css('margin-left', this.get('left'));
      if (this.get('left') === 0) {
        this.$element.css({
          borderTopLeftRadius: 0,
          borderBottomLeftRadius: 0,
        });
      }
    } else {
      if (first === false) {
        this.$element.css({
          marginLeft: '',
          borderRadius: this.get('radius')
        });
      }
    }
    if (this.get('right') !== null && this.get('right') !== false) {
      this.$element.css('margin-right', this.get('right'));
      if (this.get('right') === 0) {
        this.$element.css({
          borderTopRightRadius: 0,
          borderBottomRightRadius: 0
        });
      }
    } else {
      if (first === false) {
        this.$element.css({
          marginRight: '',
          borderRadius: this.get('radius')
        });
      }
    }
  }

  public toggle() {
    if (this.state == this.states.OPENED) {
      this.close();
    }
    if (this.state == this.states.CLOSED) {
      this.open();
    }
  }

  public shouldAutoOpen():boolean {
    return this.get('autoOpen') && this.autoOpenProcessed === false;
  }

  public autoOpen() {
    if (this.shouldAutoOpen() && this.$element.length) {
      this.debug('Auto Open', this);
      this.autoOpenProcessed = true;
      setTimeout(() => {
        this.open();
      });
    }
  }

  public callCallback(callbackName:any) {
    const setting = this.get(callbackName);
    if (setting) {
      if (typeof setting === 'string') {
        let callback = Drupal.Exo.stringToCallback(setting);
        if (callback) {
          callback.object[callback.function](this);
        }
      }
      else if (typeof setting === 'function' || typeof setting === 'object') {
        setting(this);
      }
    }
    // Trigger $window events.
    Drupal.Exo.$window.trigger('exo-modal:' + callbackName, [this]);
  }

  public toggleContentSelector() {
    if (this.get('contentSelector')) {
      if (this.state == this.states.CLOSED) {
        if (!this.$contentPlaceholder) {
          this.$contentPlaceholder = $('<div class="exo-modal-placeholder data-exo-modal-placeholder="' + this.get('contentSelector') + '"></div>');
        }
        this.$contentSelection = $(this.get('contentSelector'));
        if (this.$contentSelection.length) {
          this.$content = this.$element.find('.' + this.name + '-content');
          this.$contentPlaceholder.insertBefore(this.$contentSelection);
          this.$contentSelection.appendTo(this.$content);
        }
      }
      else {
        this.$contentSelection.insertBefore(this.$contentPlaceholder);
        this.$contentPlaceholder.remove();
      }
    }
  }

  public open(param?:any) {
    if (this.state == this.states.CLOSED) {
      this.debug('log', 'Open', '[' + this.getId() + ']', this.getData());
      this.toggleContentSelector();
      this.buildContent();
      Drupal.ExoDisplace.calculate();
      if (this.get('nest') !== true) {
        Drupal.ExoModal.closeAll();
      }
      this.bindElement();
      this.state = this.states.OPENING;
      this.$element.trigger(this.states.OPENING);
      this.$element.attr('aria-hidden', 'false');

      if (this.get('timeoutProgressbar') === true) {
        this.$element.find('.' + this.name + '-progressbar > div').width('100%');
      }

      if (this.get('iframe') === true) {
        this.$content.addClass(this.name + '-content-loader');
        this.$element.find('.' + this.name + '-iframe').on('load.exo.modal', e => {
          $(e.target).parent().removeClass(this.name + '-content-loader');
        });

        var href = null;
        try {
          href = $(param.currentTarget).attr('href') !== '' ? $(param.currentTarget).attr('href') : null;
        } catch (e) { /* console.warn(e); */ }

        if ((this.get('iframeURL') !== null) && (href === null || href === undefined)) {
          href = this.get('iframeURL');
        }
        if (href === null || href === undefined) {
          throw new Error('Failed to find iframe URL');
        }
        this.$element.find('.' + this.name + '-iframe').attr('src', href);
      }
    }

    if (this.get('bodyOverflow') || Drupal.Exo.isMobile()) {
      Drupal.Exo.lockOverflow(this.$wrap);
    }

    this.doOpen(param);

    if (this.get('timeout') !== false && !isNaN(parseInt(this.get('timeout'))) && this.get('timeout') !== false && this.get('timeout') !== 0) {
      this.startProgress(this.get('timeout'));
    }

    // Close on overlay click
    if (this.get('overlayClose') && !this.$element.hasClass(this.get('transitionOut'))) {
      this.$overlay.on('click.exo.modal', () => {
        this.close();
      });
    }

    // Focus on the first field
    if (this.get('focusInput')) {
      this.$element.find(':input:not(button):enabled:visible:first').focus().filter('input:text').select();
    }

    const updateTimer = () => {
      this.recalcLayout();
      this.timer = setTimeout(updateTimer, 300);
    }
    updateTimer();
  }

  public doOpen(param?:any) {
    this.debug('log', 'Do Open', '[' + this.getId() + ']', param);
    const groupModals = Drupal.ExoModal.getGrouped(this.get('group'));

    if (groupModals.count() > 1) {
      const prevModal = groupModals.getPrev(this.getId(), this.get('loop'));
      const nextModal = groupModals.getNext(this.getId(), this.get('loop'));

      if (this.get('appendToNavigate') === false) {
        this.$navigate.appendTo('body');
      } else {
        if (this.get('appendToClosest') === true) {
          this.$navigate.appendTo(this.$element.closest(this.get('appendToNavigate')));
        }
        else {
          this.$navigate.appendTo(this.get('appendToNavigate'));
        }
        // If modal is within eXo content, we need to offset it so that it
        // accounts for displacement.
        if (this.$navigate.closest('.exo-content').length) {
          this.$navigate.css({
            top: displace.offsets.top,
            bottom: displace.offsets.bottom,
            left: displace.offsets.left,
            right: displace.offsets.right,
          });
        }
      }

      this.$navigate.addClass('fadeIn');

      if (this.get('navigateCaption') === true) {
        if (this.get('left') !== null && this.get('left') !== false) {
          this.$navigate.find('.' + this.name + '-navigate-caption').css('right', '10px').show();
        }
        else {
          this.$navigate.find('.' + this.name + '-navigate-caption').css('left', '10px').show();
        }
      }

      var modalWidth = this.$element.outerWidth();
      if (this.get('navigateArrows') !== false) {
        if (this.get('navigateArrows') === 'closeScreenEdge') {
          this.$navigate.find('.' + this.name + '-navigate-prev').css('left', 0).show();
          this.$navigate.find('.' + this.name + '-navigate-next').css('right', 0).show();
        } else {
          this.$navigate.find('.' + this.name + '-navigate-prev').css('margin-left', -((modalWidth / 2) + 84)).show();
          this.$navigate.find('.' + this.name + '-navigate-next').css('margin-right', -((modalWidth / 2) + 84)).show();
        }
      } else {
        this.$navigate.find('.' + this.name + '-navigate-prev').hide();
        this.$navigate.find('.' + this.name + '-navigate-next').hide();
      }

      if (!prevModal) {
        this.$navigate.find('.' + this.name + '-navigate-prev').hide();
      }
      if (!nextModal) {
        this.$navigate.find('.' + this.name + '-navigate-next').hide();
      }
    }

    if (this.get('overlay') === true) {
      if (this.get('appendToOverlay') === false) {
        this.$overlay.prependTo(this.$element.parent());
      } else {
        if (this.get('appendToClosest') === true) {
          this.$overlay.prependTo(this.$element.closest(this.get('appendToOverlay')));
        }
        else {
          this.$overlay.prependTo(this.get('appendToOverlay'));
        }
        // If modal is within eXo content, we need to offset it so that it
        // accounts for displacement.
        if (this.$overlay.closest('.exo-content').length) {
          this.$overlay.css({
            top: displace.offsets.top,
            bottom: displace.offsets.bottom,
            left: displace.offsets.left,
            right: displace.offsets.right,
          });
        }
      }
    }

    if (this.get('transitionInOverlay')) {
      this.$overlay.addClass(this.get('transitionInOverlay'));
    }

    var transitionIn = this.get('transitionIn');

    if (typeof param == 'object') {

      if (param.transition !== undefined || param.transitionIn !== undefined) {
        transitionIn = param.transition || param.transitionIn;
      }
      if (param.zindex !== undefined) {
        this.setZindex(param.zindex);
      }
    }

    // Bind behaviors. When using exoModalInsert ajax this has not yet happened.
    // Drupal.attachBehaviors(this.$element.get(0), drupalSettings);
    if (transitionIn !== '' && Drupal.Exo.animationEvent !== undefined) {
      this.$element.addClass('transitionIn ' + transitionIn).css('display', 'flex');
      this.event('opening').trigger(this);
      this.callCallback('onOpening');
      this.$wrap.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
        if (this.$wrap.get(0) === e.currentTarget) {
          this.$wrap.off(Drupal.Exo.animationEvent + '.exo.modal');
          this.$element.removeClass(transitionIn + ' transitionIn');
          this.$overlay.removeClass(this.get('transitionInOverlay'));
          this.$navigate.removeClass('fadeIn');
          this.opened();
        }
      });
    } else {
      this.$element.css('display', 'flex');
      this.event('opening').trigger(this);
      this.callCallback('onOpening');
      this.opened();
    }

    if (this.get('pauseOnHover') === true && this.get('pauseOnHover') === true && this.get('timeout') !== false && !isNaN(parseInt(this.get('timeout'))) && this.get('timeout') !== false && this.get('timeout') !== 0) {
      this.$element.off('mouseenter')
        .on('mouseenter.exo.modal', e => {
          e.preventDefault();
          this.isPaused = true;
        });
      this.$element.off('mouseleave')
        .on('mouseleave.exo.modal', e => {
          e.preventDefault();
          this.isPaused = false;
        });
    }

    setTimeout(() => {
      this.$wrap.scrollTop(0);
    });
  }

  protected opened() {
    // Bind behaviors. When using exoModalInsert ajax this has not yet happened.
    // We are moving this earlier and may have consequences.
    // Drupal.attachBehaviors(this.$element.get(0), drupalSettings);
    this.state = this.states.OPENED;
    this.$element.trigger(this.states.OPENED);
    this.event('opened').trigger(this);
    this.callCallback('onOpened');
    this.$element.addClass('isOpen');

    // Support drimage module.
    if (typeof Drupal.drimage !== 'undefined') {
      setTimeout(() => {
        Drupal.drimage.init(this.$element.get(0));
      });
    }
  }

  public close(param?:any) {
    if (this.state == this.states.OPENED || this.state == this.states.OPENING) {
      Drupal.Exo.$document.off('keydown.' + this.name);

      if (this.get('autoOpen')) {
        // When closed, we set dataOriginal to an empty object. This allows
        // modals opened via AJAX to be processed again as needed.
        this.dataOriginal = {};
      }

      this.state = this.states.CLOSING;
      this.$element.trigger(this.states.CLOSING);
      this.$element.attr('aria-hidden', 'true');

      clearTimeout(this.timer);
      clearTimeout(this.timerTimeout);

      this.event('closing').trigger(this);
      this.callCallback('onClosing');

      var transitionOut = this.get('transitionOut');

      if (typeof param == 'object') {
        if (param.transition !== undefined || param.transitionOut !== undefined) {
          transitionOut = param.transition || param.transitionOut;
        }
      }

      if ((transitionOut === false || transitionOut === '') || Drupal.Exo.animationEvent === undefined) {
        this.$element.hide();
        this.$overlay.remove();
        this.$navigate.remove();
        this.closed();
      } else {
        this.$element.attr('class', [
          this.class,
          this.name,
          transitionOut,
          this.isFullscreen === true ? 'isFullscreen' : '',
          this.isCompressed === true ? 'isCompressed' : '',
          this.isSetHeight ? 'isSetHeight' : '',
          this.isTall === true ? 'isTall' : '',
          this.get('rtl') ? this.name + '-rtl' : ''
        ].join(' '));

        this.$overlay.attr('class', this.name + '-overlay ' + this.get('transitionOutOverlay'));

        if (this.get('navigateArrows') !== false && !Drupal.Exo.isMobile()) {
          this.$navigate.attr('class', this.name + '-navigate fadeOut');
        }

        this.$element.on(Drupal.Exo.animationEvent + '.exo.modal', e => {
          if (e.target === e.currentTarget) {
            this.$element.off(Drupal.Exo.animationEvent + '.exo.modal');
            if (this.$element.hasClass(transitionOut)) {
              this.$element.removeClass(transitionOut + ' transitionOut').hide();
            }
            this.$overlay.removeClass(this.get('transitionOutOverlay')).remove();
            this.$navigate.removeClass('fadeOut').remove();
            this.closed();
          }
        });
      }
    }
  }

  protected closed() {
    this.toggleContentSelector();
    this.state = this.states.CLOSED;
    this.$element.trigger(this.states.CLOSED);

    if (this.get('iframe') === true) {
      this.$element.find('.' + this.name + '-iframe').attr('src', '');
    }

    if (this.get('bodyOverflow') || Drupal.Exo.isMobile()) {
      Drupal.Exo.unlockOverflow(this.$wrap);
    }

    this.event('closed').trigger(this);
    this.callCallback('onClosed');
    this.$element.removeClass('isOpen');

    if (this.get('restoreDefaultContent') === true) {
      this.$content.html(this.content);
    }

    if ($('.' + this.name + ':visible').length === 0) {
      $('html').removeClass(this.name + '-isAttached');
    }

    if (this.get('destroyOnClose')) {
      this.destroy();
    }
  }

  protected destroy() {
    this.debug('log', 'Destroy', '[' + this.getId() + ']');
    var e = $.Event('destroy');
    this.$element.trigger(e);

    Drupal.Exo.$document.off('keydown.' + this.name);

    clearTimeout(this.timer);
    clearTimeout(this.timerTimeout);

    if (this.get('iframe') === true) {
      this.$element.find('.' + this.name + '-iframe').remove();
    }
    Drupal.detachBehaviors(this.$element.get(0), drupalSettings);
    this.$element.html(this.$content.html());

    this.$element.off('click', '[data-' + this.name + '-close]');
    this.$element.off('click', '[data-' + this.name + '-fullscreen]');

    this.$element
      .off('.' + this.name)
      .removeData(this.name)
      .attr('style', '')
      .remove();

    this.$overlay.remove();
    this.$navigate.remove();
    this.$element.trigger(this.states.DESTROYED);
    this.$element = null;
    this.autoOpenProcessed = false;
    this.state = this.states.CLOSED;
    this.built = false;
  }

  protected bindElement() {
    // Close when button pressed.
    this.$element.off('click.exo.modal', '[data-' + this.name + '-close]')
      .on('click.exo.modal', '[data-' + this.name + '-close]', e => {
        e.preventDefault();
        var transition = $(e.currentTarget).attr('data-' + this.name + '-transitionOut');

        if (transition !== undefined) {
          this.close({
            transition: transition
          });
        } else {
          this.close();
        }
      });

    // Expand when button pressed.
    this.$element.off('click.exo.modal', '[data-' + this.name + '-fullscreen]')
      .on('click.exo.modal', '[data-' + this.name + '-fullscreen]', e => {
        e.preventDefault();
        if (this.isFullscreen === true) {
          this.isFullscreen = false;
          this.$element.addClass('exo-no-transitions').removeClass('isFullscreen');
          setTimeout(() => {
            this.$element.removeClass('exo-no-transitions');
          }, 300);
        } else {
          this.isFullscreen = true;
          this.$element.addClass('exo-no-transitions isFullscreen');
          setTimeout(() => {
            this.$element.removeClass('exo-no-transitions');
          }, 300);
        }
        this.$element.trigger('fullscreen', this);
        this.callCallback('onFullscreen');
      });

    // Next modal.
    this.$navigate.off('click.exo.modal', '.' + this.name + '-navigate-next')
      .on('click.exo.modal', '.' + this.name + '-navigate-next', e => {
        e.preventDefault();
        this.next(e);
      });
    this.$element.off('click.exo.modal', '[data-' + this.name + '-next]')
      .on('click.exo.modal', '[data-' + this.name + '-next]', e => {
        e.preventDefault();
        this.next(e);
      });

    // Previous modal.
    this.$navigate.off('click.exo.modal', '.' + this.name + '-navigate-prev')
      .on('click.exo.modal', '.' + this.name + '-navigate-prev', e => {
        e.preventDefault();
        this.prev(e);
      });
    this.$element.off('click.exo.modal', '[data-' + this.name + '-prev]')
      .on('click.exo.modal', '[data-' + this.name + '-prev]', e => {
        e.preventDefault();
        this.prev(e);
      });
  }

  protected startProgress(param?:any) {
    this.isPaused = false;
    clearTimeout(this.timerTimeout);

    if (this.get('timeoutProgressbar') === true) {

      this.progressBar = {
        hideEta: null,
        maxHideTime: null,
        currentTime: new Date().getTime(),
        el: this.$element.find('.' + this.name + '-progressbar > div'),
        updateProgress: () => {
          if (!this.isPaused) {
            this.progressBar.currentTime = this.progressBar.currentTime + 10;
            var percentage = ((this.progressBar.hideEta - (this.progressBar.currentTime)) / this.progressBar.maxHideTime) * 100;
            this.progressBar.el.width(percentage + '%');
            if (percentage < 0) {
              this.close();
            }
          }
        }
      };
      if (param > 0) {
        this.progressBar.maxHideTime = parseFloat(param);
        this.progressBar.hideEta = new Date()
          .getTime() + this.progressBar.maxHideTime;
        this.timerTimeout = setInterval(this.progressBar.updateProgress, 10);
      }

    } else {
      this.timerTimeout = setTimeout(() => {
        this.close();
      }, this.get('timeout'));
    }
  }

  protected pauseProgress() {
    this.isPaused = true;
  }

  protected resumeProgress() {
    this.isPaused = false;
  }

  protected resetProgress(param?:any) {
    clearTimeout(this.timerTimeout);
    this.progressBar = {};
    this.$element.find('.' + this.name + '-progressbar > div').width('100%');
  }

  protected recalcLayout() {
    if (this.state == this.states.OPENED || this.state == this.states.OPENING) {
      let windowHeight:number = Drupal.Exo.$window.height() - (displace.offsets.top + displace.offsets.bottom);
      const windowWidth:number = Drupal.Exo.$window.width() - (displace.offsets.left + displace.offsets.right);
      let modalHeight:number = this.$element.outerHeight();
      const modalWidth:number = this.$element.outerWidth();
      const contentHeight:number = this.$content[0].scrollHeight;
      const sectionHeaderHeight:number = this.$sectionHeader.outerHeight() || 0;
      const sectionFooterHeight:number = this.$sectionFooter.outerHeight() || 0;
      const scrollTop:number = this.$wrap.scrollTop();
      const paddingV = this.$content.innerHeight() - this.$content.height();
      const paddingH = this.$content.innerWidth() - this.$content.width();
      let borderSize:number = 0;
      let allowScroll:boolean = false;

      if (Drupal.Exo.isIE()) {
        if (modalWidth >= Drupal.Exo.$window.width() || this.isFullscreen === true) {
          this.$element.css({
            left: '0',
            marginLeft: ''
          });
        }
      }

      if (this.get('borderBottom') === true && this.get('title') !== '') {
        borderSize = 3;
      }

      this.headerHeight = 0;
      if (this.$element.find('.' + this.name + '-header').length && this.$element.find('.' + this.name + '-header').is(':visible')) {
        this.headerHeight = this.$element.find('.' + this.name + '-header').innerHeight();
      }

      this.footerHeight = 0;
      if (this.$element.find('.' + this.name + '-footer').length && this.$element.find('.' + this.name + '-footer').is(':visible')) {
        this.footerHeight = this.$element.find('.' + this.name + '-footer').innerHeight();
      }

      const endHeight = this.headerHeight + this.footerHeight + sectionHeaderHeight + sectionFooterHeight;
      const wrapperHeight:number = this.$element.outerHeight() - endHeight;
      let outerHeight:number = 0;
      if (this.isSetHeight) {
        // If we have a set height, use modal height.
        outerHeight =  modalHeight;
        allowScroll = true;
      }
      else {
        outerHeight = contentHeight + endHeight;
      }

      if (this.$element.find('.' + this.name + '-loader').length) {
        this.$element.find('.' + this.name + '-loader').css('top', this.headerHeight);
      }

      if (modalHeight !== this.modalHeight) {
        this.modalHeight = modalHeight;
        this.callCallback('onResize');
      }

      if (this.get('iframe') === true) {
        if (this.get('iframeHeight') !== null && this.get('iframeWidth') !== null && this.get('iframeResponsive') === true) {
          if (windowHeight <= this.$element.outerHeight() || this.isFullscreen === true) {
            let newHeight = windowHeight;
            let percentOfChange = newHeight / this.$element.outerHeight();
            this.$element.css('max-width', (this.$element.width() * percentOfChange));
          }
          else {
            this.recalcSize();
          }
        }
        else {
          // If the height of the window is smaller than the modal with iframe
          if (windowHeight < (this.get('iframeHeight') + endHeight + borderSize) || this.isFullscreen === true) {
            this.$element.find('.' + this.name + '-iframe').css('height', Math.round(windowHeight - (endHeight + borderSize + paddingV)));
          } else {
            this.$element.find('.' + this.name + '-iframe').css('height', this.get('iframeHeight'));
          }
        }
      }
      else {
        if (this.$element.find('.' + this.name + '-expand:visible').length === 1) {
          this.$element.find('.' + this.name + '-expand')
            .height(windowHeight - (endHeight + borderSize + paddingV))
            .width(windowWidth - paddingH);
          this.$element.css('max-width', '');
        }
        else {
          this.recalcSize();
        }
      }

      if (modalHeight == windowHeight) {
        this.$element.addClass('isAttached');
      } else {
        this.$element.removeClass('isAttached');
      }

      if (this.isFullscreen === false && this.$element.width() >= Drupal.Exo.$window.width()) {
        this.$element.find('.' + this.name + '-button-fullscreen').hide();
      } else {
        this.$element.find('.' + this.name + '-button-fullscreen').show();
      }
      this.recalcButtons();

      if (this.isFullscreen === false) {
        windowHeight = windowHeight - (Drupal.Exo.getMeasurementValue(this.get('top')) || 0) - (Drupal.Exo.getMeasurementValue(this.get('bottom')) || 0);
      }
      // If the modal is larger than the height of the window.
      if (outerHeight > windowHeight) {
        if (this.get('top') > 0 && this.get('bottom') === null && contentHeight < Drupal.Exo.$window.height()) {
          this.$element.addClass('isAttachedBottom');
        }
        if (this.get('bottom') > 0 && this.get('top') === null && contentHeight < Drupal.Exo.$window.height()) {
          this.$element.addClass('isAttachedTop');
        }
        if ($('.' + this.name + ':visible').length === 1) {
          $('html').addClass(this.name + '-isAttached');
        }
        this.$element.css('height', windowHeight);
      } else {
        if (this.isTall === true) {
          this.$element.css('height', windowHeight);
        }
        else if (this.get('top') && this.get('bottom')) {
          this.$element.css('height', windowHeight);
        }
        else if (!this.isSetHeight) {
          this.$element.css('height', contentHeight + (endHeight + borderSize));
        }
        this.$element.removeClass('isAttachedTop isAttachedBottom');
        if ($('.' + this.name + ':visible').length === 1) {
          $('html').removeClass(this.name + '-isAttached');
        }
      }

      (applyScroll => {
        if (contentHeight > wrapperHeight && (allowScroll || outerHeight > windowHeight)) {
          this.$element.addClass('hasScroll');
          this.$wrap.css('height', modalHeight - (endHeight + borderSize));
        } else {
          this.$element.removeClass('hasScroll');
          this.$wrap.css('height', '');
        }
      })();

      (applyShadow => {
        if (wrapperHeight + scrollTop < (contentHeight - 30)) {
          this.$element.addClass('hasShadow');
        } else {
          this.$element.removeClass('hasShadow');
        }
      })();
    }
  }

  protected recalcButtons() {
    var widthButtons = this.$header.find('.' + this.name + '-header-buttons').innerWidth() + 10;
    if (this.get('rtl') === true) {
      this.$header.css('padding-left', widthButtons);
    } else {
      this.$header.css('padding-right', widthButtons);
    }
  }

  public next(event:JQuery.Event) {
    const modals = Drupal.ExoModal.getGrouped(this.get('group'));
    const modal = modals.getNext(this.getId(), this.get('loop'));

    if (modal) {
      const transitionOut = this.get('transitionOut') === this.defaults.transitionOut ? 'fadeOutLeft' : this.get('transitionOut');
      const transitionIn = modal.get('transitionIn') === this.defaults.transitionIn ? 'fadeInRight' : modal.get('transitionIn');

      this.close({
        transition: transitionOut
      });

      setTimeout(() => {
        modal.open({
          transition: transitionIn
        });
      }, 200);
    }
  }

  public prev(event:JQuery.Event) {
    const modals = Drupal.ExoModal.getGrouped(this.get('group'));
    const modal = modals.getPrev(this.getId(), this.get('loop'));

    if (modal) {
      const transitionOut = this.get('transitionOut') === this.defaults.transitionOut ? 'fadeOutRight' : this.get('transitionOut');
      const transitionIn = modal.get('transitionIn') === this.defaults.transitionIn ? 'fadeInLeft' : modal.get('transitionIn');

      this.close({
        transition: transitionOut
      });

      setTimeout(() => {
        modal.open({
          transition: transitionIn
        });
      }, 200);
    }
  }

  protected bindTrigger() {
    const $trigger = this.getTriggerElement();
    if ($trigger.length) {
      if (this.get('ajax') || this.get('contentAjax')) {
        this.bindTriggerAjax();
      }
      else {
        $trigger.on('click.exo.modal.' + this.getId(), e => {
          e.preventDefault();
          this.toggle();
        });
      }
    }
  }

  protected bindTriggerAjax() {
    const $trigger = this.getTriggerElement();
    if ($trigger.length) {
      const route = this.get('ajax') ? this.get('ajax') : this.get('contentAjax');
      const href = this.addDestination(Drupal.url(route));

      const triggerSettings = {
        progress: { type: 'fullscreen' },
        base: $trigger.attr('id'),
        element: $trigger[0],
        url: href,
        exoModal: this,
        event: 'click.exo.modal.' + this.getId()
      };

      Drupal.ajax(triggerSettings);
    }
  }

  public addDestination(url:string):string {
    let key = 'destination';
    let value = window.location.pathname;
    key = encodeURI(key);
    value = encodeURI(value);
    var isQuestionMarkPresent = url && url.indexOf('?') !== -1,
      separator = isQuestionMarkPresent ? '&' : '?';
    url += separator + key + '=' + value;
    return url;
  }

  public isOpen():boolean {
    return this.state === this.states.OPENED || this.state === this.states.OPENING;
  }

  public getState():string {
    return this.state;
  }

  public getSelector():string {
    return this.getId().replace(/_/g, '-');
  }

  public getSelectorAsId():string {
    return '#' + this.getSelector();
  }

  public getTriggerElement():JQuery {
    this.$trigger = $(this.getSelectorAsId() + '-trigger');
    return this.$trigger;
  }

  public getElement():JQuery {
    this.$element = $(this.getSelectorAsId());
    return this.$element;
  }

}
