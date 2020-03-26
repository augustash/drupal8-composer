(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.machineName.showMachineName = function showMachineName(machine, data) {
    var settings = data.options;

    if (machine !== '') {
      if (machine !== settings.replace) {
        data.$target.val(machine).trigger('change');
        data.$preview.html(settings.field_prefix + Drupal.checkPlain(machine) + settings.field_suffix);
      }
      data.$suffix.show();
    } else {
      data.$suffix.hide();
      data.$target.val(machine).trigger('change');
      data.$preview.empty();
    }
  }

})(jQuery, Drupal, drupalSettings);
