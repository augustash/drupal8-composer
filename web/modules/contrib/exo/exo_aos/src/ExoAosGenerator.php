<?php

namespace Drupal\exo_aos;

use Drupal\exo\ExoSettingsInterface;

/**
 * Class ExoAosGenerator.
 */
class ExoAosGenerator implements ExoAosGeneratorInterface {

  /**
   * Drupal\exo\ExoSettingsInterface definition.
   *
   * @var \Drupal\exo_aos\ExoAossettings
   */
  protected $exoSettings;

  /**
   * Constructs a new ExoAosGenerator object.
   */
  public function __construct(ExoSettingsInterface $exo_settings) {
    $this->exoSettings = $exo_settings;
  }

  /**
   * Generate an eXo aos instance.
   *
   * @return \Drupal\exo_aos\ExoAos
   *   An eXo modal.
   */
  public function generate(array $settings = []) {
    return new ExoAos($this->exoSettings->createInstance($settings));
  }

  /**
   * The list of available animations.
   */
  public function getElementAnimations() {
    return $this->exoSettings::getElementAnimations();
  }

  /**
   * The list of available anchor placements.
   */
  public function getElementAnchorPlacements() {
    return $this->exoSettings::getElementAnchorPlacements();
  }

  /**
   * The list of available easings.
   */
  public function getElementEasings() {
    return $this->exoSettings::getElementEasings();
  }

}
