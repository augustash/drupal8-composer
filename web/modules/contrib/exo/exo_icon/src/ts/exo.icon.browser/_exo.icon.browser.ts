
class ExoIconBrowser extends ExoData {
  protected defaults:ExoSettingsGroupInterface = {
    onBuild: function () {},
    onSelect: function () {}
  };
  protected $element:JQuery;
  protected $categories:JQuery;
  protected $pager:JQuery;
  protected $pagerPrev:JQuery;
  protected $pagerNext:JQuery;
  protected $infoPages:JQuery;
  protected $infoTotal:JQuery;
  protected $search:JQuery;
  protected $icons:JQuery;
  protected category:string = 'all';
  protected icons:Array<string>;
  protected searchTimer:number;
  protected searchQuery:string = '';
  protected limit:number = 80;
  protected page:number = 1;
  protected selected:string = '';
  protected readonly events = {
    build: new ExoEvent<ExoIconBrowser>(),
    select: new ExoEvent<ExoIconBrowser>()
  };

  constructor(id:string, $element:JQuery) {
    super(id);
    this.$element = $element;
  }

  public build(data):Promise<ExoSettingsGroupInterface> {
    return new Promise((resolve, reject) => {
      data = _.extend({}, this.defaults, data);
      super.build(data).then(data => {
        if (data !== null) {
          this.event('build').trigger(this);
          this.callCallback('onBuild');
          // If we show an empty icon we need to account for it in the list.
          if (drupalSettings.exoIcon.field && drupalSettings.exoIcon.field.emptyIcon) {
            this.limit -= 1;
          }
        }
        resolve(data);
      }, reject);
    });
  }

  public afterBuild() {
    this.$categories = this.$element.find('.exo-icon-browser-category-select');
    this.$icons = this.$element.find('.exo-icon-browser-icons');
    this.$pager = this.$element.find('.exo-icon-browser-pager');
    this.$pagerPrev = this.$element.find('.exo-icon-browser-pager-prev');
    this.$pagerNext = this.$element.find('.exo-icon-browser-pager-next');
    this.$search = this.$element.find('.exo-icon-browser-search-input');
    this.$infoPages = this.$element.find('.exo-icon-browser-info-pages');
    this.$infoTotal = this.$element.find('.exo-icon-browser-info-total');
    this.buildCategories();
    this.buildIcons();
    this.buildPager();
    this.buildSearch();
  }

  protected buildCategories() {
    this.$categories.on('change', e => {
      this.category = this.$categories.val().toString();
      this.buildIcons();
    });
  }

  protected buildPager() {
    this.$pagerPrev.once('exo.icon').on('click', e => {
      if (!this.$pagerPrev.hasClass('disabled')) {
        this.page--;
        this.placeIcons();
      }
    });
    this.$pagerNext.once('exo.icon').on('click', e => {
      if (!this.$pagerNext.hasClass('disabled')) {
        this.page++;
        this.placeIcons();
      }
    });
    this.togglePager();
  }

  protected togglePager() {
    if (this.page === 1) {
      this.$pagerPrev.addClass('disabled');
    }
    else {
      this.$pagerPrev.removeClass('disabled');
    }
    if (this.limit * this.page > this.icons.length) {
      this.$pagerNext.addClass('disabled');
    }
    else {
      this.$pagerNext.removeClass('disabled');
    }
  }

  protected buildSearch() {
    this.$search.once('exo.icon').focus().on('keyup', e => {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => {
        this.searchQuery = this.$search.val().toString().toLowerCase();
        this.buildIcons();
      }, 300);
    });
  }

  protected buildIcons() {
    this.icons = [];
    this.get('packages').forEach(packageId => {
      if (this.category === 'all' || this.category === packageId) {
        this.icons = this.icons.concat(this.getIconsByPackageId(packageId));
      }
    });
    let name;
    if (this.searchQuery !== '') {
      this.icons = this.icons.filter(icon => {
        name = icon.match(/data-icon-id="(.*?)"/)[1].replace(/-/g, ' ');
        return name.toLowerCase().indexOf(this.searchQuery) > -1;
      });
    }
    this.page = 1;
    this.placeIcons();
  }

  protected placeIcons() {
    let max = this.limit * this.page;
    let min = max - this.limit;
    let count = 0;
    let name;
    let iconClasses;
    this.$icons.off().empty();
    if (drupalSettings.exoIcon.field && drupalSettings.exoIcon.field.emptyIcon) {
      this.$icons.append('<a class="exo-icon-browser-icon empty ' + (this.selected ? '' : ' selected') + '">' + drupalSettings.exoIcon.field.emptyIcon + '</a>');
    }
    this.icons.forEach(icon => {
      if (count >= min && count < max) {
        iconClasses = 'exo-icon-browser-icon';
        name = icon.match(/data-icon-id="(.*?)"/)[1];
        if (name === this.selected) {
          iconClasses += ' selected';
        }
        this.$icons.append('<a class="' + iconClasses + '">' + icon + '</a>');
      }
      count++;
    });
    this.$icons.find('.exo-icon-browser-icon').once('exo.icon').on('click', e => {
      this.selected = $(e.currentTarget).find('.exo-icon').data('icon-id') || '';
      this.event('select').trigger(this);
      this.callCallback('onSelect');
    });
    this.togglePager();
    this.buildInfo();
  }

  protected buildInfo() {
    this.$infoPages.html(this.page + '/' + (Math.floor(this.icons.length / this.limit) + 1));
    this.$infoTotal.html('(' + this.icons.length.toString() + ')');
  }

  protected getIconsByPackageId(packageId:string) {
    if (drupalSettings.exoIcon.package && drupalSettings.exoIcon.package[packageId]) {
      return drupalSettings.exoIcon.package[packageId];
    }
    return null;
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
  }

  public setSelected(iconId):this {
    this.selected = iconId;
    return this;
  }

  public getSelected():string {
    return this.selected;
  }

  public getSelectedIcon():string {
    let name;
    const icon = this.icons.find(icon => {
      name = icon.match(/data-icon-id="(.*?)"/)[1];
      return name === this.selected;
    });
    return icon || '';
  }

}
