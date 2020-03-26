/**
 * @file
 * Global eXo.toolbar.toggle javascript.
 */

(function ($, Drupal, drupalSettings) {
  TSinclude('./exo.toolbar.toggle/_exo.toolbar.toggle.ts')
  TSinclude('./exo.toolbar.toggle/_exo.toolbar.toggle.item.ts')

  Drupal.behaviors.exoToolbarToggle = {
    attach: function(context) {
      if (!Drupal.ExoToolbar.isAdminMode()) {
        Drupal.ExoToolbar.isReady().then(instances => {
          instances.each((toolbar:ExoToolbar) => {
            Drupal.ExoToolbarToggle.attach(toolbar);
          });
        });
      }
    }
  }

})(jQuery, Drupal, drupalSettings);
