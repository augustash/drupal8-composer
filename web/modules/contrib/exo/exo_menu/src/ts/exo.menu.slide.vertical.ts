(function ($) {

  class ExoMenuStyleSlideVertical extends ExoMenuStyleBase {
    protected defaults:ExoSettingsGroupInterface = {
      debug: false,
      // show back button
      backNav: 0,
      // back nav text
      backText: 'Back',
      // back icon
      backIcon: '',
      // show breadcrumbs
      breadcrumbNav: 1,
      // the location of this element within the dom
      backSelector: '.exo-menu-top',
      // initial breadcrumb text
      breadcrumbText: 'All',
      // icon used in initial breadcrumb
      breadcrumbIcon: '',
      // breadcrumb icon that separates each item
      breadcrumbSeparatorIcon: '',
      // a selector that allows for placing the breadcrumb somehwere else in the dom
      breadcrumbSelector: '.exo-menu-top',
      // icon used to signify menu items that will open a submenu
      itemIcon: '',
      // delay between each menu item sliding animation
      itemDelayInterval: 60,
      // direction
      direction: 'r2l',
      // theme
      theme: '',
      relocateNav: '',
       // callback: item that doesn´t have a submenu gets clicked -
       // onItemClick([event], [inner HTML of the clicked item])
      onItemClick: null
    }
    protected $menu:JQuery;
    protected $wrap:JQuery;
    protected $menus:JQuery;
    protected $breadcrumbWrapper:JQuery;
    protected $breadcrumbNav:JQuery;
    protected $backWrapper:JQuery;
    protected $backNav:JQuery;
    protected menuStorage:ExoMenuSlideStorage = {};
    protected currentMenu:string;
    protected isAnimating:boolean;

    constructor(exoMenu:ExoMenu) {
      super(exoMenu);
    }

    public build() {
      super.build();
      this.buildCache();
      if (this.$menus.length) {
        this.buildElement();
        this.bindEvents();
      }
    }

    public refresh() {
      if (this.menuStorage[this.currentMenu]) {
        this.setWrapHeight(this.menuStorage[this.currentMenu]['$menuEl']);
      }
    }

    protected buildCache() {
      this.$menu = this.$element.find('.exo-menu-nav');
      this.$wrap = this.$menu.find('.exo-menu-wrap');
      this.$menus = this.$menu.find('.exo-menu-level');

      if (this.get('breadcrumbNav')) {
        this.$breadcrumbWrapper = this.$element.find('.exo-menu-top');
      }

      if (this.get('backNav')) {
        this.$backWrapper = this.$element.find('.exo-menu-top');
      }

      var currentMenu = '0-0';
      this.$menus.each((index, element) => {
        if ($(element).find('.exo-menu-link.is-active').length) {
          currentMenu = $(element).data('menu');
        }
      });

      this.currentMenu = currentMenu;
    }

    protected buildElement() {
      const submenus = [];
      let $wrapper:JQuery;

      this.$menus.each((index, element) => {
        const $menuEl = $(element);
        const id = $menuEl.data('menu');
        const parentId = $menuEl.data('menu-parent');

        var menu = {
          $menuEl: $menuEl,
          $menuItems: $menuEl.find('.exo-menu-item'),
          backIdx: parentId,
          name: id === '0-0' ? this.get('breadcrumbText') : this.$menus.find('.exo-menu-link[data-submenu="' + id + '"]').html(),
        };
        this.menuStorage[id] = menu;

        // set current menu class
        if (id === this.currentMenu) {
          $menuEl.addClass('current');
          this.setWrapHeight($menuEl);
        }

        $menuEl.find('.exo-menu-link[data-submenu]').each((index, element) => {
          var $linkEl = $(element);
          if (this.get('itemIcon')) {
            $linkEl.append(this.get('itemIcon'));
          }
        });
      });

      // create back button
      if (this.get('backNav')) {
        $wrapper = this.fetchSelector(this.get('backSelector'));
        if ($wrapper) {
          $wrapper.find('.exo-menu-back').remove();
          this.$backNav = $('<a class="exo-menu-back" aria-label="' + this.get('backText') + '" href="#"></a>').prependTo($wrapper);
          var html = this.get('backText');
          if (this.get('backIcon')) {
            html = this.get('backIcon') + ' ' + html;
          }
          this.$backNav.html(html);

          if (this.currentMenu !== '0-0') {
            this.$backNav.addClass('animate-fadeIn');
          }
        }
      }

      // create breadcrumbs
      if (this.get('breadcrumbNav')) {
        $wrapper = this.fetchSelector(this.get('breadcrumbSelector'));
        if ($wrapper) {
          $wrapper.find('.exo-menu-breadcrumbs').remove();
          this.$breadcrumbNav = $('<nav class="exo-menu-breadcrumbs" aria-label="You are here"></nav>').prependTo($wrapper);

          // Need to add breadcrumbs for all parents of current submenu
          this.crawlCrumbs(this.menuStorage[this.currentMenu].backIdx, this.menuStorage);

          // Create current submenu breadcrumb
          this.addBreadcrumb(this.currentMenu);
        }
      }
    }

    protected bindEvents() {
      for (const key in this.menuStorage) {
        if (this.menuStorage.hasOwnProperty(key)) {
          this.menuStorage[key].$menuItems.each((index, element) => {
            $(element).find('.exo-menu-link[data-submenu]').on('click', (e) => {
              var $linkEl = $(e.currentTarget);
              var itemName = $linkEl.html();
              var submenu = $linkEl.attr('data-submenu');
              var $subMenuEl = this.$menu.find('.exo-menu-level[data-menu="' + submenu + '"]');
              if ($subMenuEl.length) {
                e.preventDefault();
                this.openSubMenu($subMenuEl, index, itemName);
              }
              else {
                this.$menu.find('.current').removeClass('current');
                $linkEl.addClass('current');
              }
            });
          });
        }
      }

      // back navigation
      if (this.get('backNav')) {
        this.$backNav.on('click', e => {
          e.preventDefault();
          this.back();
        });
      }
    }

    protected addBreadcrumb(idx):void {
      if (!this.get('breadcrumbNav')) {
        return;
      }

      var div = document.createElement('div');
      div.innerHTML = this.menuStorage[idx].name;
      var title = div.innerText;
      var $bc = $('<span class="exo-menu-breadcrumb">');
      if (idx === '0-0' && this.get('breadcrumbIcon')) {
        title = this.get('breadcrumbIcon') + title;
      }
      else if (this.get('breadcrumbSeparatorIcon')) {
        $bc.html('<span class="exo-menu-seperator">' + this.get('breadcrumbSeparatorIcon') + '</span>');
      }
      var $link = $('<a href="#"></a>').html(title).appendTo($bc);

      $link.on('click', (e) => {
        e.preventDefault();
        // do nothing if this breadcrumb is the last one in the list of breadcrumbs
        if (!$bc.next().length || this.isAnimating) {
          return false;
        }

        this.isAnimating = true;
        // current menu slides out
        this.menuOut();
        // next menu slides in
        var $nextMenu = this.menuStorage[idx].$menuEl;
        this.menuIn($nextMenu);

        // remove breadcrumbs that are ahead
        // $bc.nextAll().remove();
        var $remaining = $bc.nextAll();
        $remaining.one(Drupal.Exo.animationEvent, function (e) {
          $remaining.remove();
        }).addClass('animate-fadeOut');
      });

      this.$breadcrumbNav.append($bc);
      $bc.addClass('animate-fadeIn');
    }

    protected openSubMenu($subMenuEl, clickPosition, subMenuName) {
      if (this.isAnimating) {
        return false;
      }
      // var menuIdx = this.$menus.index($subMenuEl);
      const menuIdx = $subMenuEl.data('menu');
      this.isAnimating = true;

      // save "parent" menu index for back navigation
      this.menuStorage[menuIdx].backIdx = this.currentMenu;
      // save "parent" menu´s name
      this.menuStorage[menuIdx].name = subMenuName;
      // current menu slides out
      this.menuOut(clickPosition);
      // next menu (submenu) slides in
      this.menuIn($subMenuEl, clickPosition);
    }

    protected back() {
      if (this.isAnimating) {
        return false;
      }
      this.isAnimating = true;

      // current menu slides out
      this.menuOut();
      // next menu (previous menu) slides in
      var $backMenu = this.menuStorage[this.menuStorage[this.currentMenu].backIdx].$menuEl;
      this.menuIn($backMenu);

      // remove last breadcrumb
      if (this.get('breadcrumbNav')) {
        this.$breadcrumbNav.children().last().remove();
      }
    }

    protected menuIn($nextMenuEl, clickPosition?) {
      var $currentMenu = this.menuStorage[this.currentMenu].$menuEl;
      var isBackNavigation = typeof clickPosition == 'undefined' ? true : false;
      var nextMenuIdx = $nextMenuEl.data('menu');
      var nextMenu = this.menuStorage[nextMenuIdx];
      var $nextMenuItems = nextMenu.$menuItems;
      var nextMenuItemsTotal = $nextMenuItems.length;

      // set height of nav based on children
      this.setWrapHeight($nextMenuEl);

      // control back button and breadcrumbs navigation elements
      if (!isBackNavigation) {
        // show back button
        if (this.get('backNav')) {
          this.$backNav.removeClass('animate-fadeOut').addClass('animate-fadeIn');
        }
        // add breadcrumb
        this.addBreadcrumb(nextMenuIdx);
      }
      else if (nextMenuIdx === '0-0' && this.get('backNav')) {
        // hide back button
        this.$backNav.removeClass('animate-fadeIn').addClass('animate-fadeOut');
      }

      $nextMenuItems.each((index, element) => {
        element.style.webkitAnimationDelay = element.style.animationDelay = isBackNavigation ? index * this.get('itemDelayInterval') + 'ms' : Math.abs(clickPosition - index) * this.get('itemDelayInterval') + 'ms';
        var farthestIdx = clickPosition <= nextMenuItemsTotal / 2 || isBackNavigation ? nextMenuItemsTotal - 1 : 0;

        if (index === farthestIdx) {
          $(element).one(Drupal.Exo.animationEvent, e => {
            // reset classes
            if (this.get('direction') === 'r2l') {
              $currentMenu.removeClass(!isBackNavigation ? 'animate-fadeOutLeft' : 'animate-fadeOutRight');
              $nextMenuEl.removeClass(!isBackNavigation ? 'animate-fadeInRight' : 'animate-fadeInLeft');
            }
            else {
              $currentMenu.removeClass(isBackNavigation ? 'animate-fadeOutLeft' : 'animate-fadeOutRight');
              $nextMenuEl.removeClass(isBackNavigation ? 'animate-fadeInRight' : 'animate-fadeInLeft');
            }
            $currentMenu.removeClass('current');
            $nextMenuEl.addClass('current');

            this.currentMenu = nextMenuIdx;

            // we can navigate again.
            this.isAnimating = false;

            // focus retention
            $nextMenuEl.focus();
          });
        }
      });

      // animation class
      if (this.get('direction') === 'r2l') {
        $nextMenuEl.addClass(!isBackNavigation ? 'animate-fadeInRight' : 'animate-fadeInLeft');
      }
      else {
        $nextMenuEl.addClass(isBackNavigation ? 'animate-fadeInRight' : 'animate-fadeInLeft');
      }
    }

    protected menuOut(clickPosition?) {
      var $currentMenu = this.menuStorage[this.currentMenu].$menuEl;
      var isBackNavigation = typeof clickPosition == 'undefined' ? true : false;

      // slide out current menu items - first, set the delays for the items
      this.menuStorage[this.currentMenu].$menuItems.each((index, element) => {
        element.style.webkitAnimationDelay = element.style.animationDelay = isBackNavigation ? index * this.get('itemDelayInterval') + 'ms' : Math.abs(clickPosition - index) * this.get('itemDelayInterval') + 'ms';
      });
      // animation class
      if (this.get('direction') === 'r2l') {
        $currentMenu.addClass(!isBackNavigation ? 'animate-fadeOutLeft' : 'animate-fadeOutRight');
      }
      else {
        $currentMenu.addClass(isBackNavigation ? 'animate-fadeOutLeft' : 'animate-fadeOutRight');
      }
    }

    protected crawlCrumbs(currentMenu, menuArray) {
      if (currentMenu === 0) {
        return;
      }
      if (menuArray[currentMenu].backIdx !== '0-0') {
        this.crawlCrumbs(menuArray[currentMenu].backIdx, menuArray);
      }
      // create breadcrumb
      this.addBreadcrumb(currentMenu);
    }

    protected setWrapHeight($menuEl):void {
      // Slight timeout to allow display hide/show to not affect height calculations.
      setTimeout(() => {
        $menuEl = $menuEl || this.$menus.filter('.current');
        var currentHeight = this.$wrap.height();
        var height = 0;
        $menuEl.children().each((index, element) => {
          height += $(element).outerHeight();
        });

        // this.$element.offsetParent().animate({scrollTop: 0}, 1000);
        if (currentHeight <= height) {
          this.$wrap.height(height);
        }
        else {
          $menuEl.one(Drupal.Exo.animationEvent, e => {
            this.$wrap.height(height);
          });
        }
      }, 10);
    }

    /**
     * Given a selector, locate the closest.
     */
    protected fetchSelector(selector:string):JQuery {
      var $wrapper = this.$element.find(selector);
      if (!$wrapper.length) {
        $wrapper = $(selector).first();
      }
      return $wrapper.length ? $wrapper : null;
    }

    protected log(item) {
      if (!this.get('debug')) {
        return;
      }
      if (typeof item === 'object') {
        console.log('[Exo Menu]', item); // eslint-disable-line no-console
      }
      else {
        console.log('[Exo Menu] ' + item); // eslint-disable-line no-console
      }
    };

  }

  Drupal.ExoMenuStyles['slide_vertical'] = ExoMenuStyleSlideVertical;

})(jQuery);
