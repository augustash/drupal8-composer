/**
 * @file
 * Global video_bg javascript.
 */

TSinclude('./exo.video/_exo.video.base.ts')

(function ($, Drupal, debounce) {

  Drupal.ExoVideoProviders = {};
  TSinclude('./exo.video/_exo.video.youtube.ts')
  TSinclude('./exo.video/_exo.video.vimeo.ts')
  TSinclude('./exo.video/_exo.videos.ts')

  /**
   * Fixed build behavior.
   */
  Drupal.behaviors.exoVideo = {
    attach: function(context) {
      Drupal.ExoVideo.attach(context);
    }
  }

}(jQuery, Drupal, Drupal.debounce));
