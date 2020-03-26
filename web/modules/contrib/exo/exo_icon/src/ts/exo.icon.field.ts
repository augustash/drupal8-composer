(function ($, Drupal, drupalSettings) {

  /**
   * Icon field build behavior.
   */
  Drupal.ExoIconField = {
    getFieldById: function (fieldId) {
      return $('.exo-icon-field[data-exo-icon=' + fieldId + ']');
    },

    onBuild: function (exoIconBrowser:ExoIconBrowser) {
      const $field = this.getFieldById(exoIconBrowser.getId());
      const value = $field.find('input').val();
      exoIconBrowser.setSelected(value);
    },

    onSelect: function (exoIconBrowser:ExoIconBrowser) {
      const $field = this.getFieldById(exoIconBrowser.getId());
      $field.removeClass('empty');
      $field.find('input').val(exoIconBrowser.getSelected()).trigger('change').trigger('keyup');
      let icon = exoIconBrowser.getSelectedIcon();
      if (!icon) {
        $field.addClass('empty');
        icon = drupalSettings.exoIcon.field.emptyIcon;
      }
      $field.find('.exo-icon-field-icon').html(icon);
      Drupal.ExoModal.close(exoIconBrowser.getId());
    }
  }

})(jQuery, Drupal, drupalSettings);
