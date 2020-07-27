(function ($) {

  class ExoMenuStyleDropdownHorizontal extends ExoMenuStyleBase {
    protected defaults:ExoSettingsGroupInterface = {
      // icon used to signify menu items that will open a submenu
      itemIcon: '',
      // make secondary links expandable.
      expandable: true,
      // unbind events.
      unbindFirst: false,
      transitionIn: 'expandInY', // comingIn, bounceInDown, bounceInUp, fadeInDown, fadeInUp, fadeInLeft, fadeInRight, flipInX
      transitionOut: 'expandOutY', // comingOut, bounceOutDown, bounceOutUp, fadeOutDown, fadeOutUp, , fadeOutLeft, fadeOutRight, flipOutX
    }

    public build() {
      super.build();
      const isExpandable:boolean = this.get('expandable');
      const isFirstUnbound: boolean = this.get('unbindFirst');

      if (this.get('itemIcon')) {
        if (isExpandable) {
          this.$element.find('.expanded > a').append(this.get('itemIcon'));
        }
        else {
          this.$element.find('.level-0 > ul > .expanded > a').append(this.get('itemIcon'));
        }
      }

      if (isFirstUnbound) {
        this.$element.find('.level-0 > ul > .expanded.active-trail').addClass('no-event').addClass('expand');
        this.$element.find('.level-1 > ul > .expanded').on('mouseenter.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          let timeout = setTimeout(() => {
            Drupal.Exo.getBodyElement().addClass('exo-menu-expanded');
            this.show($(e.currentTarget));
          }, 200);
          $target.data('timeout', timeout);
        }).on('mouseleave.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          Drupal.Exo.getBodyElement().removeClass('exo-menu-expanded');
          this.hide($(e.currentTarget));
        });
      }
      else {
        this.$element.find('.level-0 > ul > .expanded').on('mouseenter.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          let timeout = setTimeout(() => {
            Drupal.Exo.getBodyElement().addClass('exo-menu-expanded');
            this.show($(e.currentTarget));
          }, 200);
          $target.data('timeout', timeout);
        }).on('mouseleave.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          Drupal.Exo.getBodyElement().removeClass('exo-menu-expanded');
          this.hide($(e.currentTarget));
        });
      }

      if (isExpandable) {
        this.$element.find('.level-1 .expanded > a').on('click.exo.menu.style.dropdown', e => {
          e.preventDefault();
          this.toggle($(e.target).closest('.expanded'), false);
        });
      }
      else {
        this.$element.find('.level-1 .expanded').addClass('expand');
      }
    }

    protected toggle($item:JQuery, animate?:boolean) {
      animate = animate !== false;
      if ($item.hasClass('expand')) {
        this.hide($item, animate);
      }
      else {
        this.show($item, animate);
      }
    }

    protected show($item:JQuery, animate?:boolean) {
      const $submenu = $item.find('> .exo-menu-level');
      animate = animate !== false;
      if ($submenu.length) {
        $item.addClass('expand');
        if (animate && this.get('transitionIn') !== '' && Drupal.Exo.animationEvent !== undefined) {
          $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.hide');
          $submenu.removeClass('exo-animate-' + this.get('transitionOut'));
          $submenu.addClass('exo-animate-' + this.get('transitionIn'));
          $submenu.one(Drupal.Exo.animationEvent + '.exo.menu.show', e => {
            $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.show');
            $submenu.removeClass('exo-animate-' + this.get('transitionIn'));
          });
        }
      }
    }

    protected hide($item:JQuery, animate?:boolean) {
      const $submenu = $item.find('> .exo-menu-level');
      animate = animate !== false;
      if ($submenu.length) {
        if (animate && this.get('transitionOut') !== '' && Drupal.Exo.animationEvent !== undefined) {
          $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.show');
          $submenu.removeClass('exo-animate-' + this.get('transitionIn'))
          $submenu.addClass('exo-animate-' + this.get('transitionOut'));
          $submenu.one(Drupal.Exo.animationEvent + '.exo.menu.hide', e => {
            $item.removeClass('expand');
            $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.hide');
            $submenu.removeClass('exo-animate-' + this.get('transitionOut'));
            if (this.get('expandable')) {
              $submenu.find('.expand').removeClass('expand');
            }
          });
        }
        else {
          $item.removeClass('expand');
        }
      }
    }
  }

  Drupal.ExoMenuStyles['dropdown_horizontal'] = ExoMenuStyleDropdownHorizontal;

})(jQuery);
