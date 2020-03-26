(function ($) {

  class ExoMenuStyleDropdownVertical extends ExoMenuStyleBase {
    protected defaults:ExoSettingsGroupInterface = {
      // icon used to signify menu items that will open a submenu
      itemIcon: '',
      // make secondary links expandable.
      transitionIn: 'fadeIn', // comingIn, bounceInDown, bounceInUp, fadeInDown, fadeInUp, fadeInLeft, fadeInRight, flipInX
      transitionOut: '', // comingOut, bounceOutDown, bounceOutUp, fadeOutDown, fadeOutUp, , fadeOutLeft, fadeOutRight, flipOutX
    }

    public build() {
      super.build();
      const isExpandable:boolean = this.get('expandable');

      if (this.get('itemIcon')) {
        this.$element.find('.expanded > a').append(this.get('itemIcon'));
      }

      this.$element.find('.expanded > a').on('click.exo.menu.style.dropdown', e => {
        const $target = $(e.currentTarget);
        e.preventDefault();
        this.toggle($target.closest('.expanded'));
      });
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
            $submenu.find('.expand').removeClass('expand');
          });
        }
        else {
          $item.removeClass('expand');
        }
      }
    }
  }

  Drupal.ExoMenuStyles['dropdown_vertical'] = ExoMenuStyleDropdownVertical;

})(jQuery);
