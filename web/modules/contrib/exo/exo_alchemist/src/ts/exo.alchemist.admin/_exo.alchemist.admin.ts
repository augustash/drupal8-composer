class ExoAlchemistAdmin {
  protected label:string = 'ExoAlchemistAdmin';
  protected doDebug:boolean = false;
  protected ranOnce:boolean = false;
  protected overlapOffset = 10;
  protected $obtrusiveElements:JQuery;
  protected $shade:JQuery;
  protected $highlight:JQuery;
  protected $overlay:JQuery;
  protected $overlayHeader:JQuery;
  protected $overlayOps:JQuery;
  protected $overlayClose:JQuery;
  protected $fieldBreadcrumbs:JQuery;
  protected $target:JQuery;
  protected $targetHeader:JQuery;
  protected $targetTitle:JQuery;
  protected $targetClose:JQuery;
  protected $targetOps:JQuery;
  protected targetTimer:number;
  // protected fieldHoverTimer:number;
  protected $activeTarget:JQuery = null;
  protected $activeComponent:JQuery = null;
  protected $activeField:JQuery = null;

  /**
   * Initial setup.
   * @param context
   */
  public setup():void {
    this.buildElements();
    Drupal.Exo.$document.on('drupalViewportOffsetChange', offsets => {
      if (this.$activeTarget && !this.$activeComponent) {
        this.sizeTarget(this.$activeTarget);
      }
    });
    Drupal.Exo.addOnResize('exo.alchemist', e => {
      if (this.$activeComponent) {
        this.sizeComponentOverlay(this.$activeComponent);
      }
      if (this.$activeField) {
        this.sizeFieldOverlay(this.$activeField);
      }
      if (this.$activeTarget) {
        this.sizeTarget(this.$activeTarget);
      }
    });
  }

  protected buildElements() {
    this.$obtrusiveElements = $('.exo-fixed-region, .exo-layout-builder-top').addClass('exo-component-hide');
    this.$shade = $('<div class="exo-alchemist-shade" />').appendTo(Drupal.Exo.$exoContent);
    this.$highlight = $('<div class="exo-alchemist-highlight" />').appendTo(Drupal.Exo.$exoContent);
    this.$overlay = $('<div class="exo-alchemist-overlay exo-reset exo-font" />').appendTo(Drupal.Exo.$exoContent);
    this.$overlayHeader = $('<div class="exo-alchemist-overlay-header" />').appendTo(this.$overlay);
    this.$overlayOps = $('<span />').appendTo(this.$overlayHeader).wrap('<div class="exo-alchemist-ops exo-alchemist-overlay-ops" />');
    this.$overlayClose = $('<div class="exo-alchemist-overlay-close" />').appendTo(this.$overlayHeader);
    this.$overlayClose.html('<a>' + drupalSettings.exoAlchemist.icons.close + '</a>');
    this.$target = $('<div class="exo-alchemist-target exo-reset exo-font" />').appendTo(Drupal.Exo.$exoContent);
    this.$targetHeader = $('<div class="exo-alchemist-target-header" />').appendTo(this.$target);
    this.$targetTitle = $('<div class="exo-alchemist-target-title" />').appendTo(this.$target);
    this.$targetOps = $('<span />').appendTo(this.$targetHeader).wrap('<div class="exo-alchemist-ops exo-alchemist-target-ops" />');
    this.$targetClose = $('<div class="exo-alchemist-target-close" />').appendTo(this.$targetHeader);
    this.$targetClose.html('<a>' + drupalSettings.exoAlchemist.icons.close + '</a>');
    this.$fieldBreadcrumbs = $('<ul class="exo-alchemist-breadcrumbs" />').appendTo(this.$overlay);
  }

  /**
   * Drupal Attach event.
   * @param context
   */
  public attach(context:HTMLElement):Promise<boolean> {
    if (this.ranOnce === false) {
      this.ranOnce = true;
      this.setup();
    }
    return new Promise((resolve, reject) => {

      function ready() {
        $('#layout-builder').trigger('exo.alchemist.ready');
      }

      // Called each time layout builder is rebuilt.
      $('#layout-builder').once('exo.alchemist').each((index, element) => {
        // Check active components and fields and make sure we store the new
        // elements.
        if (this.$activeComponent && !$(element).find(this.$activeComponent).length) {
          const $new = $('#' + this.$activeComponent.attr('id'));
          if ($new.length) {
            $(element).imagesLoaded(() => {
              this.setComponentActive($new, true);
              if (this.$activeField && !$($new).find(this.$activeField).length) {
                const $new = $('#' + this.$activeField.attr('id'));
                if ($new.length) {
                  this.setFieldActive($new, true);
                }
              }
              ready();
            });
          }
          else {
            ready();
          }
        }
        else {
          ready();
        }
      });

      $('.exo-component-edit', context).once('exo.alchemist.component').each((index, element) => {
        const $element = $(element);
        const editableCount = $('.exo-component-field-edit, .exo-section', $element).length;
        if ($element.hasClass('exo-component-locked') && !editableCount) {
          $element.addClass('exo-component-blocked');
        }
      }).on('click.exo.alchemist.component', e => {
        const $element = $(e.currentTarget);
        e.preventDefault();
        e.stopPropagation();
        if (!$element.hasClass('exo-component-blocked')) {
          this.setComponentActive($element, this.isNestedComponent(e.currentTarget));
          $('.exo-component-field-edit:hover').trigger('mouseenter');
        }
      }).on('mouseenter.exo.alchemist.component', e => {
        const doAction = () => {
          const $element = $(e.currentTarget);
          const data = JSON.parse(e.currentTarget.getAttribute('data-exo-component'));
          if (!$element.hasClass('exo-component-blocked')) {
            this.showTarget($element, 'Click to focus this <strong>' + data.label.toLowerCase() + '</strong> component', 'regular-cog');
          }
          else {
            this.showTarget($element, 'This component cannot be changed.', 'regular-lock');
          }
        }

        if (!this.$activeComponent) {
          doAction();
        }
        else if (this.isNestedComponent(e.currentTarget)) {
          // This is a nested section.
          doAction();
        }
      }).on('mouseleave.exo.alchemist.component', e => {
        if (!this.$activeComponent) {
          this.hideTarget();
        }
        else if (this.isNestedComponent(e.currentTarget)) {
          // This is a nested section.
          this.hideTarget();
        }
      });

      // This class can be assigned to elements that have functionality that
      // needs to function even in edit mode.
      $('.exo-component-event-allow', context).once('exo.alchemist.allow').on('click.exo.alchemist.allow', e => {
        if (!$(e.currentTarget).hasClass('exo-component-field-edit')) {
          var $wrapped = $(e.currentTarget).closest('.exo-component-field-edit');
          if ($wrapped.length) {
            $wrapped.trigger('click');
          }
        }
        this.watch();
      });

      $('.exo-component-field-edit', context).once('exo.alchemist.field').on('click.exo.alchemist.field', e => {
        const $element = $(e.currentTarget);
        if ($element.hasClass('exo-component-field-edit-lock') || $element.closest('.exo-component-field-edit-lock').length) {
          return;
        }
        this.setFieldActive($element);
      }).on('mouseenter.exo.alchemist.field', e => {
        if (!this.$activeField) {
          const $element = $(e.currentTarget);
          if ($element.hasClass('exo-component-field-edit-lock') || $element.closest('.exo-component-field-edit-lock').length) {
            return;
          }
          const data = JSON.parse(e.currentTarget.getAttribute('data-exo-field'));
          this.showTarget($element, 'Click to manage <strong>' + data.label.toLowerCase() + '</strong> field', 'regular-cog');
        }
      }).on('mouseleave.exo.alchemist.field', e => {
        if (!this.$activeField) {
          this.hideTarget();
        }
      });
      resolve(true);
    });
  }

  // Temporarily watch elements for changes and adjust until size stops.
  public watch() {
    var groups = [];
    groups.push([this.$activeComponent, 'sizeComponentOverlay']);
    groups.push([this.$activeField, 'sizeFieldOverlay']);
    groups.push([this.$activeTarget, 'sizeTarget']);
    groups.forEach(group => {
      const $element = group[0];
      if ($element && $element.length) {
        const callback = group[1];
        let count = 0;
        let height = Math.round($element.outerHeight(true));
        let heightCalc = () => {
          setTimeout(() => {
            const newHeight = Math.round($element.outerHeight(true));
            if (newHeight !== height) {
              this[callback]($element);
              heightCalc();
            }
            else {
              if (count < 10) {
                heightCalc();
              }
              count++;
            }
            height = newHeight;
          }, 10);
        }
        heightCalc();
      }
    });
  }

  public isLayoutBuilder() {
    return drupalSettings.exoAlchemist && drupalSettings.exoAlchemist.isLayoutBuilder;
  }

  public setComponentActive($element:JQuery, force?:boolean) {
    if (!this.$activeComponent || force) {
      this.$activeComponent = $element;
      $element.addClass('exo-component-edit-active');
      if (this.$obtrusiveElements.length) {
        this.$obtrusiveElements.addClass('exo-component-hide-active');
      }
      this.buildComponentOps($element);
      this.hideTarget();
      this.showComponentOverlay($element, () => {
        this.setComponentInactive();
      });
    }
  }

  public buildComponentOps($element:JQuery) {
    const componentOps = drupalSettings.exoAlchemist.componentOps;
    const componentData = $element.data('exo-component');
    const tokens = $.extend({}, componentData);

    this.$overlayOps.html('');
    this.showComponentOps();
    if (componentData.description) {
      $('<div class="exo-description exo-component-description">' + componentData.description + '</div>').appendTo(this.$overlayOps);
    }
    for (const key in componentOps) {
      if (componentOps.hasOwnProperty(key) && componentData.ops.includes(key)) {
        const op = componentOps[key];
        let url = op.url;
        for (const token in tokens) {
          if (tokens.hasOwnProperty(token)) {
            if (typeof tokens[token] === 'string' || typeof tokens[token] === 'number') {
              url = url.replace(new RegExp('-' + token + '-', 'g'), tokens[token]);
            }
          }
        }
        let title = op.title;
        if (typeof componentData[key + '_badge'] !== 'undefined') {
          title += ' <span class="exo-alchemist-op-badge">' + componentData[key + '_badge'] + '</span>';
        }
        url = url.replace(new RegExp('(\/-.*?)-', 'g'), '');
        const $link = $('<a href="' + url + '" title="' + op.label + '" class="exo-component-op use-ajax">' + title + '</a>').appendTo(this.$overlayOps);
        $link.data('dialog-type', 'dialog');
        $link.data('dialog-renderer', 'off_canvas');
        $link.data('dialog-options', {
          exo_modal: {
            title: op.label,
            subtitle: op.description,
            icon: op.icon,
          }
        });
      }
    }
    Drupal.attachBehaviors(this.$overlayOps[0]);
  }

  public showComponentOps() {
    this.$overlayOps.addClass('active');
  }

  public hideComponentOps() {
    this.$overlayOps.removeClass('active');
  }

  public isNestedComponent(element):boolean {
    return this.$activeComponent !== null && this.$activeComponent.find(element).length !== 0;
  }

  public setComponentInactive() {
    if (this.$activeComponent) {
      const $parentComponent = this.$activeComponent.parent().closest('.exo-component-edit');
      this.setFieldInactive();
      this.$activeComponent.removeClass('exo-component-edit-active');
      this.$activeComponent = null;
      if ($parentComponent.length) {
        this.setComponentActive($parentComponent);
        return;
      }
      this.hideTarget();
      this.hideComponentOverlay();
      this.hideComponentOps();
      if (this.$obtrusiveElements.length) {
        this.$obtrusiveElements.removeClass('exo-component-hide-active');
      }
    }
  }

  public showComponentOverlay($element:JQuery, callback?:Function) {
    return new Promise((resolve, reject) => {
      this.restrictComponentOverlayPointerEvents();
      this.sizeComponentOverlay($element);
      if (callback) {
        this.showOverlayClose(e => {
          this.hideOverlayClose();
          callback();
        });
        this.$shade.off('click').on('click.exo', e => {
          if (e.target === this.$shade.get(0)) {
            callback();
          }
        });
      }
      resolve(true);
    });
  }

  public showComponentClose() {
    this.$overlayClose.addClass('active');
  }

  public hideComponentClose() {
    this.$overlayClose.removeClass('active');
  }

  public hideComponentOverlay() {
    return new Promise((resolve, reject) => {
      this.hideComponentClose();
      this.$shade.unbind('click.exo');
      this.$overlay.one(Drupal.Exo.transitionEvent, e => {
        this.$shade.removeAttr('style');
        this.$overlay.removeAttr('style');
        resolve();
      });
      this.$shade.css({
        opacity: 0,
        visibility: 'hidden',
      });
      this.$overlay.css({
        opacity: 0,
        visibility: 'hidden',
      });
      resolve(true);
    });
  }

  public sizeComponentOverlay($element) {
    const outerWidth = $element.outerWidth(true);
    const outerHeight = $element.outerHeight(true);
    const offsets = $element.offset();
    const top = offsets.top - displace.offsets.top - $element.css('marginTop').replace('px', '');
    const bottom = top + outerHeight;
    const left = offsets.left - displace.offsets.left - $element.css('marginLeft').replace('px', '');
    const right = left + outerWidth;
    this.$shade.css({
      top: '0px',
      right: '0px',
      bottom: '0px',
      left: '0px',
      width: '100%',
      height: '100%',
      visibility: 'visible',
      clipPath: 'polygon(0% 0%, 0% 100%, ' + left + 'px 100%, ' + left + 'px ' + top + 'px, ' + right + 'px ' + top + 'px, ' + right + 'px ' + bottom + 'px, ' + left + 'px ' + bottom + 'px, ' + left + 'px 100%, 100% 100%, 100% 0%)',
    });
    setTimeout(() => {
      this.$shade.css('opacity', 1);
    });
    this.$overlay.css({
      top: top + 'px',
      left: left + 'px',
      width: outerWidth + 'px',
      height: outerHeight + 'px',
      opacity: 1,
      visibility: 'visible',
    });
  }

  /**
   * Set the shade to allow click-through.
   */
  public allowComponentOverlayPointerEvents() {
    this.$shade.removeClass('restrict');
  }

  /**
   * Set the shade to intercept all click events.
   */
  public restrictComponentOverlayPointerEvents() {
    this.$shade.addClass('restrict');
  }

  public showTarget($element:JQuery, title?:string, icon?:string) {
    clearTimeout(this.targetTimer);
    this.$target.off(Drupal.Exo.transitionEvent);
    this.$activeTarget = $element;
    if (icon) {
      title = '<i class="exo-icon exo-icon-font icon-' + icon + '" aria-hidden="true"></i> ' + title;
    }
    title = title ? '<span>' + title + '</span>' : '';
    this.$target.off(Drupal.Exo.transitionEvent);
    if (title) {
      this.$targetTitle.addClass('active');
      this.$targetTitle.html(title).show();
    }
    else {
      this.$targetTitle.removeClass('active');
    }
    this.sizeTarget($element);
  }

  public hideTarget() {
    clearTimeout(this.targetTimer);
    this.targetTimer = setTimeout(() => {
      this.hideTargetClose();
      this.$target.one(Drupal.Exo.transitionEvent, e => {
        this.$target.removeAttr('style');
      });
      this.$activeTarget = null;
      this.$target.css({
        opacity: 0,
        visibility: 'hidden',
      });
    }, 200);
  }

  public showTargetClose(callback?:Function) {
    this.$targetClose.addClass('active');
    if (callback) {
      this.$targetClose.off('click').on('click.exo', e => {
        e.preventDefault();
        e.stopPropagation();
        callback(e);
      });
    }
  }

  public hideTargetClose() {
    this.$targetClose.removeClass('active');
    this.$targetClose.off('click.exo');
  }

  /**
   * Set the target to allow click-through.
   */
  public allowTargetPointerEvents() {
    this.$target.removeClass('restrict');
  }

  /**
   * Set the target to intercept all click events.
   */
  public restrictTargetPointerEvents() {
    this.$target.addClass('restrict');
  }

  public sizeTarget($element) {
    const windowWidth = Drupal.Exo.$window.outerWidth();
    const offsets = $element.offset();
    let outerWidth = $element.outerWidth(true);
    let outerHeight = $element.outerHeight(true);
    let top = offsets.top - displace.offsets.top - $element.css('marginTop').replace('px', '');
    let left = offsets.left - displace.offsets.left - $element.css('marginLeft').replace('px', '');
    if (this.$activeComponent) {
      // If we have an active component, account for overlap.
      const componentOffsets = this.$activeComponent.offset();
      const componentMarginTop = parseInt(this.$activeComponent.css('marginTop').replace('px', ''));
      if (componentOffsets.top - componentMarginTop >= offsets.top) {
        top += this.overlapOffset;
        left += this.overlapOffset;
        outerWidth -= this.overlapOffset * 2;
        outerHeight -= this.overlapOffset * 2;
      }
    }
    this.$target.css({
      top: top + 'px',
      left: left + 'px',
      width: outerWidth + 'px',
      height: outerHeight + 'px',
      opacity: 1,
      visibility: 'visible',
    });

    this.$target.attr('data-align', (windowWidth / 2) < left ? 'right' : 'left');
  }

  public lockNestedFields($element:JQuery) {
    $element.addClass('exo-component-field-edit-lock');
  }

  public unlockNestedFields($element:JQuery) {
    $element.removeClass('exo-component-field-edit-lock');
    $('.exo-component-field-edit:hover').trigger('mouseenter');
  }

  public setFieldActive($element:JQuery, force?:boolean) {
    if (!this.$activeField || force) {
      this.$activeField = $element;
      $element.addClass('exo-component-field-edit-active');
      this.restrictTargetPointerEvents();
      this.buildFieldOps($element);
      this.buildBreadcrumbs();
      // We make sure the component size has not changed.
      this.sizeComponentOverlay(this.$activeComponent);
      this.showFieldOverlay($element, () => {
        this.setFieldInactive();
      });
      $(document).trigger('exoComponentFieldEditActive', this.$activeField);
    }
  }

  public buildFieldOps($element:JQuery) {
    const fieldOps = drupalSettings.exoAlchemist.fieldOps;
    const componentData = $element.closest('.exo-component-edit').data('exo-component');
    const fieldData = $element.data('exo-field');
    const tokens = $.extend({}, componentData, fieldData);

    this.$targetOps.html('');
    this.showFieldOps();
    this.$targetOps.addClass('active');
    if (fieldData.description) {
      $('<div class="exo-description exo-field-description">' + fieldData.description + '</div>').appendTo(this.$targetOps);
    }
    for (const key in fieldOps) {
      if (fieldOps.hasOwnProperty(key) && fieldData.ops.includes(key)) {
        const op = fieldOps[key];
        let url = op.url;
        for (const token in tokens) {
          if (tokens.hasOwnProperty(token)) {
            if (typeof tokens[token] === 'string' || typeof tokens[token] === 'number') {
              url = url.replace('-' + token + '-', tokens[token]);
            }
          }
        }
        const $link = $('<a href="' + url + '" title="' + op.label + '" class="exo-field-op use-ajax">' + op.title + '</a>').appendTo(this.$targetOps);
        $link.data('dialog-type', 'dialog');
        $link.data('dialog-renderer', 'off_canvas');
        $link.data('dialog-options', {
          exo_modal: {
            title: op.label,
            subtitle: op.description,
            icon: op.icon,
          }
        });
      }
    }
    Drupal.attachBehaviors(this.$targetOps[0]);
  }

  public showFieldOps() {
    this.hideComponentOps();
    this.hideComponentClose();
    this.$targetOps.addClass('active');
  }

  public hideFieldOps() {
    this.showComponentOps();
    this.showComponentClose();
    this.$targetOps.removeClass('active');
  }

  public setFieldInactive() {
    if (this.$activeField) {
      this.$activeField.removeClass('exo-component-field-edit-active');
      this.$activeField = null;
      this.hideFieldOverlay();
      this.hideFieldOps();
      this.hideBreadcrumbs();
      this.hideTarget();
      this.allowTargetPointerEvents();
    }
  }

  public showFieldOverlay($element:JQuery, callback?:Function) {
    return new Promise((resolve, reject) => {
      this.$highlight.off(Drupal.Exo.transitionEvent);
      this.showTarget($element);
      this.restrictFieldOverlayPointerEvents();
      this.sizeFieldOverlay($element);
      if (callback) {
        this.showTargetClose(e => {
          this.hideTargetClose();
          callback();
        });
        this.$highlight.off('click').on('click.exo', e => {
          e.preventDefault();
          e.stopPropagation();
          if (e.target === this.$highlight.get(0)) {
            callback();
          }
        });
      }
      resolve(true);
    });
  }

  /**
   * Set the highlight to allow click-through.
   */
  public allowFieldOverlayPointerEvents() {
    this.$highlight.removeClass('restrict');
  }

  /**
   * Set the highlight to intercept all click events.
   */
  public restrictFieldOverlayPointerEvents() {
    this.$highlight.addClass('restrict');
  }

  public hideFieldOverlay() {
    return new Promise((resolve, reject) => {
      this.hideTargetClose();
      this.$highlight.unbind('click.exo');
      this.$highlight.one(Drupal.Exo.transitionEvent, e => {
        this.$highlight.removeAttr('style');
        resolve();
      });
      this.$highlight.css({
        opacity: 0,
        visibility: 'hidden',
      });
      resolve(true);
    });
  }

  public sizeFieldOverlay($element) {
    const overlayOffsets = this.$activeComponent.offset();
    const overlayWidth = this.$activeComponent.outerWidth(true);
    const overlayHeight = this.$activeComponent.outerHeight(true);
    const overlayTop = overlayOffsets.top - parseInt(this.$activeComponent.css('marginTop').replace('px', ''));
    const overlayLeft = overlayOffsets.left - parseInt(this.$activeComponent.css('marginLeft').replace('px', ''));
    const outerWidth = $element.outerWidth(true);
    const outerHeight = $element.outerHeight(true);
    const offsets = $element.offset();
    let top = offsets.top - overlayTop - $element.css('marginTop').replace('px', '');
    let bottom = top + outerHeight;
    let left = offsets.left - overlayLeft - $element.css('marginLeft').replace('px', '');
    let right = left + outerWidth;
    // if (this.$activeComponent) {
    //   // If we have an active component, account for overlap.
    //   const componentOffsets = this.$activeComponent.offset();
    //   const componentMarginTop = parseInt(this.$activeComponent.css('marginTop').replace('px', ''));
    //   if (componentOffsets.top - componentMarginTop >= offsets.top) {
    //     top += this.overlapOffset;
    //     left += this.overlapOffset;
    //     right -= this.overlapOffset;
    //     bottom -= this.overlapOffset;
    //   }
    // }
    this.$highlight.css({
      top: (overlayTop - displace.offsets.top) + 'px',
      bottom: (overlayTop + overlayHeight) + 'px',
      left: (overlayLeft - displace.offsets.left) + 'px',
      right: (overlayLeft + overlayWidth) + 'px',
      width: overlayWidth,
      height: overlayHeight,
      // opacity: 1,
      visibility: 'visible',
      clipPath: 'polygon(0% 0%, 0% 100%, ' + left + 'px 100%, ' + left + 'px ' + top + 'px, ' + right + 'px ' + top + 'px, ' + right + 'px ' + bottom + 'px, ' + left + 'px ' + bottom + 'px, ' + left + 'px 100%, 100% 100%, 100% 0%)',
    });
    setTimeout(() => {
      this.$highlight.css('opacity', 1);
    }, 10);
  }

  public showOverlayClose(callback?:Function) {
    this.$overlayClose.addClass('active');
    if (callback) {
      this.$overlayClose.off('click.exo').on('click.exo', e => {
        e.preventDefault();
        e.stopPropagation();
        callback(e);
      });
    }
  }

  public hideOverlayClose() {
    this.$overlayClose.removeClass('active');
    this.$overlayClose.off('click.exo');
  }

  /**
   * Build field operations.
   */
  protected buildBreadcrumbs() {
    this.hideBreadcrumbs();
    if (this.$activeField) {
      const $field = this.$activeField;
      let $availableFields = this.$activeComponent.find('.exo-component-field-edit');
      // Remove self.
      $availableFields = $availableFields.not($field.find('.exo-component-field-edit'));
      // Remove peers.
      $availableFields = $availableFields.not($field.parents('.exo-component-group').find('.exo-component-field-edit'));
      // Add parents.
      $availableFields = $availableFields.add($field.parents('.exo-component-field-edit'));
      const $fields = $availableFields.overlaps($field).add($field);
      $('<li class="exo-alchemist-breadcrumb-label">Nested Elements:</li>').appendTo(this.$fieldBreadcrumbs);
      $fields.each((index, element) => {
        const $element = $(element);
        const fieldData = $element.data('exo-field');
        $('<li class="exo-alchemist-breadcrumb-field"><a>' + fieldData.label + '</a></li>').on('click', e => {
          e.preventDefault();
          e.stopPropagation();
          this.setFieldInactive();
          this.setFieldActive($element, true);
        }).appendTo(this.$fieldBreadcrumbs);
      });
    }
  }

  protected hideBreadcrumbs() {
    this.$fieldBreadcrumbs.children().remove();
  }

}

Drupal.ExoAlchemistAdmin = Drupal.ExoAlchemistAdmin ? Drupal.ExoAlchemistAdmin : new ExoAlchemistAdmin();
