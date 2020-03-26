(function ($, Drupal, drupalSettings) {

  TSinclude('./exo.icon.browser/_exo.icon.browser.ts')

  /**
   * Icon browser build behavior.
   */
  Drupal.behaviors.exoIconBrowser = {
    instance: null,

    attach: function(context) {
      if (drupalSettings.exoIcon && drupalSettings.exoIcon.browser) {
        for (const browserId in drupalSettings.exoIcon.browser) {
          if (drupalSettings.exoIcon.browser.hasOwnProperty(browserId)) {
            const $element = $('#exo-icon-browser-' + browserId).once('exo.icon');
            if ($element.length) {
              const options = drupalSettings.exoIcon.browser[browserId];
              this.instance = new ExoIconBrowser(browserId, $element);
              this.instance.build(options).then(success => {
                if (success) {
                  this.instance.afterBuild();
                  delete drupalSettings.exoIcon.browser[browserId];
                }
              });
            }
          }
        }
      }
    }
  }

})(jQuery, Drupal, drupalSettings);
