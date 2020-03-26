<?php

namespace Drupal\exo_modal;

use Drupal\exo\ExoSettingsInterface;

/**
 * Provides a class which generates an eXo modal.
 */
class ExoModalGenerator implements ExoModalGeneratorInterface {

  /**
   * The eXo menu settings service.
   *
   * @var \Drupal\exo\ExoSettingsInterface
   */
  protected $exoSettings;

  /**
   * Constructs a new ExoModalGenerator object.
   *
   * @param \Drupal\exo\ExoSettingsInterface $exo_settings
   *   The UX options service.
   */
  public function __construct(ExoSettingsInterface $exo_settings) {
    $this->exoSettings = $exo_settings;
  }

  /**
   * Generate an eXo modal.
   *
   * @return \Drupal\exo_modal\ExoModalInterface
   *   An eXo modal.
   */
  public function generate($id, array $settings = [], $modal = NULL) {
    // Allow theme and theme_content to be passed in within the modal settings
    // but move it to where eXo Modal expects to find it.
    foreach (['theme', 'theme_content'] as $key) {
      if (isset($settings['modal'][$key])) {
        $settings[$key] = $settings['modal'][$key];
        unset($settings['modal'][$key]);
      }
    }
    return new ExoModal($this->exoSettings->createInstance($settings), $id, $modal);
  }

}
