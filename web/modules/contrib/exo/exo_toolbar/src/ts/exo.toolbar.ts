TSinclude('./exo.toolbar/_exo.data.toolbar.ts')

(function ($, _, Drupal, drupalSettings, displace) {

  TSinclude('./exo.toolbar/_exo.toolbar.region.collection.ts')
  TSinclude('./exo.toolbar/_exo.toolbar.region.ts')
  TSinclude('./exo.toolbar/_exo.toolbar.section.ts')
  TSinclude('./exo.toolbar/_exo.toolbar.item.ts')
  TSinclude('./exo.toolbar/_exo.toolbar.ts')
  TSinclude('./exo.toolbar/_exo.toolbars.ts')

  Drupal.ExoDisplace.event('calculate').on('exo.toolbar', (offsets:ExoDisplaceOffsetsInterface) => {
    Drupal.ExoToolbar.getDisplacement().then(offsets => {
      Drupal.ExoDisplace.offsets = offsets;
      Drupal.ExoDisplace.broadcast();
    });
  });

  /**
   * Toolbar build behavior.
   */
  Drupal.Exo.event('init').on('toolbar', () => {
    // We wait for eXo core to be initialized. At this point we add our attach
    // as a dependency to eXo core being ready so that our toolbars are aligned
    // when the page is revealed.
    Drupal.Exo.addInitWait(Drupal.ExoToolbar.attach(document.body));
    // For all future calls, we register a behavior which takes care of calling
    // all toolbar updates.
    Drupal.behaviors.exoToolbar = {
      attach: function(context) {
        Drupal.ExoToolbar.attach(context);
      }
    }
  });

})(jQuery, _, Drupal, drupalSettings, Drupal.displace);
