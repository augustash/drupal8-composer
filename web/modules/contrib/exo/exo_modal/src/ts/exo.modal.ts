(function ($, _, Drupal, drupalSettings, displace) {

  TSinclude('./exo.modal/_exo.modal.ts')
  TSinclude('./exo.modal/_exo.modals.ts')

  /**
   * Modal build behavior.
   */
  Drupal.behaviors.exoModal = {
    attach: function(context) {
      Drupal.ExoModal.attach(context);
      const focusedModal = Drupal.ExoModal.getVisibleFocus()
      if (focusedModal && focusedModal.getElement().find(context).length) {
        // If we have a focused modal we shoudl account for changes.
        focusedModal.createFooter();
      }
    }
  }

})(jQuery, _, Drupal, drupalSettings, Drupal.displace);
