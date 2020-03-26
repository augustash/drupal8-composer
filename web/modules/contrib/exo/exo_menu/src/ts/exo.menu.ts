TSinclude('./exo.menu/_exo.menu.style.base.ts')

(function ($, Drupal, drupalSettings) {

  TSinclude('./exo.menu/_exo.menu.ts')
  TSinclude('./exo.menu/_exo.menus.ts')

  /**
   * Menu dropdown build behavior.
   */
  Drupal.behaviors.exoMenu = {
    attach: function(context) {
      Drupal.ExoMenu.attach(context);
    }
  }

})(jQuery, Drupal, drupalSettings);
