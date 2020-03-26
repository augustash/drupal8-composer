/**
 * @file
 * Global eXo.toolbar.dialog javascript.
 */

TSinclude('./exo.toolbar.dialog/_exo.toolbar.dialog.type.base.ts')

(function ($, Drupal, drupalSettings) {

  TSinclude('./exo.toolbar.dialog/_exo.toolbar.dialog.ts')
  TSinclude('./exo.toolbar.dialog/_exo.toolbar.dialog.item.ts')
  TSinclude('./exo.toolbar.dialog/_exo.toolbar.dialog.command.ts')

  /**
   * Includes types. Other modules can extend Drupal.ExoToolbarDialogTypes
   * prototype to add additional types.
   */
  TSinclude('./exo.toolbar.dialog/_exo.toolbar.dialog.type.tip.ts')

  Drupal.behaviors.exoToolbarDialog = {
    attach: function(context) {
      if (!Drupal.ExoToolbar.isAdminMode()) {
        Drupal.ExoToolbar.isReady().then(instances => {
          instances.each((toolbar:ExoToolbar) => {
            Drupal.ExoToolbarDialog.attach(toolbar);
          });
        });
      }
    }
  }

})(jQuery, Drupal, drupalSettings);
