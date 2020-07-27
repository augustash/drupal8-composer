(function ($, _, Drupal, drupalSettings, displace) {

  TSinclude('./exo.alchemist.admin/_exo.alchemist.admin.ts')

  /**
   * eXo Alchemist admin behavior.
   */
  Drupal.behaviors.exoAlchemistAdmin = {
    attach: function(context) {
      Drupal.ExoAlchemistAdmin.attach(context);
    }
  }

  /**
   * Sets a field as active.
   */
  Drupal.AjaxCommands.prototype.exoComponentFocus = function (ajax, response, status) {
    const $component = $('#' + response.id);
    if ($component.length) {
      $('#layout-builder').on('exo.alchemist.ready', e => {
        Drupal.ExoAlchemistAdmin.setComponentActive($component, true);
      });
    }
  }

  /**
   * Sets a component as inactive.
   */
  Drupal.AjaxCommands.prototype.exoComponentBlur = function (ajax, response, status) {
    Drupal.ExoAlchemistAdmin.setComponentInactive();
  }

  /**
   * Sets a field as active.
   */
  Drupal.AjaxCommands.prototype.exoComponentFieldFocus = function (ajax, response, status) {
    const $field = $('#' + response.id);
    if ($field.length) {
      $('#layout-builder').on('exo.alchemist.ready', e => {
        Drupal.ExoAlchemistAdmin.setFieldActive($field, true);
      });
    }
  }

  /**
   * Sets a field as inactive.
   */
  Drupal.AjaxCommands.prototype.exoComponentFieldBlur = function (ajax, response, status) {
    Drupal.ExoAlchemistAdmin.setFieldInactive();
  }

})(jQuery, _, Drupal, drupalSettings, Drupal.displace);
