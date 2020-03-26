class ExoToolbarItem extends ExoDataToolbar {
  protected label:string = 'Toolbar Item';
  protected doDebug:boolean = false;

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      super.build(data).then(data =>{
        if (data !== null) {
          // Setup binding.
          this.bind();
        }
        resolve(data);
      }, reject);
    });
  }

  protected bind() {
    this.getElement().off('mouseenter.exo.toolbar.region').on('mouseenter.exo.toolbar.region', (e, element) => {
      $(element).css('z-index', 1);
    }).off('mouseleave.exo.toolbar.region').on('mouseleave.exo.toolbar.region', (e, element) => {
      $(element).css('z-index', '');
    });
  }

  public getSelector():string {
    let selector = `#exo-toolbar-item-${this.getId()}`;
    return selector.replace(/_/g, '-').replace(/:/g, '');
  }

  public getRegionId():string {
    return this.get('region');
  }

  public setRegionId(regionId:string):this {
    this.set('region', regionId);
    return this;
  }

  public getRegion():ExoToolbarRegion {
    return this.getToolbar().getRegion(this.getRegionId());
  }

  public getSectionId():string {
    return this.get('section');
  }

  public getSectionUniqueId():string {
    return this.getRegionId() + ':' + this.getSectionId();
  }

  public setSectionId(sectionId:string):this {
    this.set('section', sectionId);
    return this;
  }

  public getSection():ExoToolbarSection {
    return this.getToolbar().getSection(this.getSectionUniqueId());
  }

  public allowSort():boolean {
    return this.get('allow_sort');
  }

  public allowAdmin():boolean {
    return this.get('allow_admin');
  }

  public getEdge():string {
    return this.getRegion().getEdge();
  }

  public getAlignment():string {
    return this.getRegion().getAlignment();
  }

  public disableAside():this {
    this.getElement().addClass(this.getToolbar().asideDisableClass);
    return this;
  }

  public enableAside():this {
    this.getElement().removeClass(this.getToolbar().asideDisableClass);
    return this;
  }

  /**
   * Get the position id an of item based on edge and position.
   *
   * Possible return values:
   * - top:left
   * - top:right
   * - bottom:left
   * - bottom:right
   * - left:top
   * - left:bottom
   * - right:top
   * - right:bottom
   */
  public getPositionId():string {
    const edge = this.getEdge();
    return edge + ':' + this.getPositionOffsetProperty(this.getAlignment());
  }

  /**
   * Determine the offset property of an item.
   *
   * Example return values:
   * For item in top:left [horizontal:left, vertical:top]
   * For item in left:bottom [horizontal:left, vertical:bottom]
   *
   * @param alignment
   *   The alignment, either horizontal or vertical.
   */
  public getPositionOffsetProperty(alignment:string):string {
    const edge = this.getEdge();
    switch (alignment) {
      case 'horizontal':
        switch (edge) {
          case 'top':
          case 'bottom':
            return this.getElement().offset().left - $(window).scrollLeft() > $(window).width() / 2 ? 'right': 'left';
          case 'left':
          case 'right':
            return edge;
        }
        break;
      case 'vertical':
        switch (edge) {
          case 'top':
          case 'bottom':
            return edge;
          case 'left':
          case 'right':
            return this.getElement().offset().top - $(window).scrollTop() > $(window).height() / 2 ? 'bottom': 'top';
        }
        break;
    }
  }

  /**
   * Determine the offset value of an item.
   *
   * Example return values:
   * For item in top:left [horizontal:(offset from left), vertical:(offset from top + height)]
   * For item in left:bottom [horizontal:(offset from left + width), vertical:(offset from bottom + height)]
   *
   * @param alignment
   *   The alignment, either horizontal or vertical.
   */
  public  getPositionOffsetValue(alignment:string, $element?:JQuery, $parent?:JQuery<HTMLElement>|JQuery<Window>, type?:string):number {
    $element = $element || this.getElement();
    $parent = $parent || $(window);
    type = type === 'position' ? 'position' : 'offset';
    let value:number = 0;
    const alignmentId = this.getPositionId();
    switch (alignment) {
      case 'horizontal':
        switch (alignmentId) {
          case 'top:left':
          case 'bottom:left':
            value = $element[type]().left - $parent.scrollLeft();
            break;
          case 'top:right':
          case 'bottom:right':
            value = $parent.width() - (($element[type]().left - $parent.scrollLeft()) + $element.outerWidth());
            break;
          case 'left:top':
          case 'left:bottom':
            value = ($element[type]().left - $parent.scrollLeft()) + $element.outerWidth();
            break;
          case 'right:top':
          case 'right:bottom':
            value = $parent.width() - ($element[type]().left - $parent.scrollLeft());
            break;
        }
        break;
      case 'vertical':
        switch (alignmentId) {
          case 'top:left':
          case 'top:right':
            value = ($element[type]().top - $parent.scrollTop()) + $element.outerHeight();
            break;
          case 'bottom:left':
          case 'bottom:right':
            value = $parent.height() - ($element[type]().top - $parent.scrollTop());
            break;
          case 'left:top':
          case 'right:top':
            value = $element[type]().top - $parent.scrollTop();
            break;
          case 'left:bottom':
          case 'right:bottom':
            value = $parent.height() - (($element[type]().top - $parent.scrollTop()) + $element.outerHeight());
            break;
        }
        break;
    }
    return Math.round(value * 100) / 100;
  }

  public positionAside() {
    const $aside = $('.' + this.getToolbar().asideClass , this.getElement());
    if ($aside.length) {
      $aside.removeAttr('style');
      let css = {};
      css[this.getPositionOffsetProperty('horizontal')] = this.getPositionOffsetValue('horizontal') + 'px';
      css[this.getPositionOffsetProperty('vertical')] = this.getPositionOffsetValue('vertical') + 'px';
      switch (this.getAlignment()) {
        case 'horizontal':
          css['min-width'] = this.getElement().outerWidth();
          break;
        case 'vertical':
          css['min-height'] = this.getElement().outerHeight();
          break;
      }
      $aside.css(css);
      $aside.attr('data-exo-toolbar-item-position', this.getPositionId());
    }
    return this;
  }

  public positionLabels() {
    this.getElement().find('> .exo-toolbar-element').each((index, element) => {
      const $element = $(element);
      const $tip = this.getElement().find('#' + $element.attr('id') + '-label');
      if ($tip.length) {
        const alignmentId = this.getPositionId();
        let css = {};
        switch (this.getAlignment()) {
          case 'horizontal':
            css[this.getPositionOffsetProperty('horizontal')] = this.getPositionOffsetValue('horizontal', $element, this.getElement(), 'position') + 'px';
            css[this.getPositionOffsetProperty('vertical')] = '0px';
            break;
          case 'vertical':
            css[this.getPositionOffsetProperty('vertical')] = this.getPositionOffsetValue('vertical', $element, this.getElement(), 'position') + 'px';
            css[this.getPositionOffsetProperty('horizontal')] = '0px';
            break;
        }
        $tip.css(css);
      }
    });
  }

}
