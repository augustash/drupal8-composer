(function ($, _, Drupal, drupalSettings, displace) {

  TSinclude('./exo.alchemist.admin/_exo.alchemist.field.ts')
  TSinclude('./exo.alchemist.admin/_exo.alchemist.field.ops.ts')
  TSinclude('./exo.alchemist.admin/_exo.alchemist.field.breadcrumbs.ts')
  TSinclude('./exo.alchemist.admin/_exo.alchemist.component.ts')
  TSinclude('./exo.alchemist.admin/_exo.alchemist.admin.ts')

  /**
   * eXo Alchemist admin behavior.
   */
  Drupal.behaviors.exoAlchemistAdmin = {
    attach: function(context) {
      if (typeof drupalSettings.exoAlchemist !== 'undefined') {
        Drupal.ExoAlchemistAdmin.attach(context);
      }
    }
  }

})(jQuery, _, Drupal, drupalSettings, Drupal.displace);
