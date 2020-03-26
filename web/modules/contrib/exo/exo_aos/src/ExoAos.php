<?php

namespace Drupal\exo_aos;

use Drupal\exo\ExoSettingsInstanceInterface;
use Drupal\Core\Template\Attribute;

/**
 * Defines an eXo AOS.
 */
class ExoAos {

  /**
   * The eXo Menu options service.
   *
   * @var \Drupal\exo\ExoSettingsInstanceInterface
   */
  protected $exoSettings;

  /**
   * The attributes object.
   *
   * @var \Drupal\Core\Template\Attribute
   */
  protected $attributes;

  /**
   * Constructs a new ExoModal.
   */
  public function __construct(ExoSettingsInstanceInterface $exo_settings) {
    $this->exoSettings = $exo_settings;
    $this->attributes = new Attribute();
  }

  /**
   * Set setting.
   *
   * @param mixed $key
   *   The key to set.
   * @param mixed $value
   *   The value.
   */
  protected function setSetting($key, $value) {
    $allowed = ExoAosSettings::getElementProperties();
    if (isset($allowed[$key])) {
      $this->exoSettings->setSetting($key, $value);
    }
    return $this;
  }

  /**
   * Set animation.
   *
   * @param mixed $value
   *   The value.
   */
  public function setAnimation($value) {
    $allowed = ExoAosSettings::getElementAnimations();
    if (isset($allowed[$value])) {
      $this->setSetting('animation', $value);
    }
    return $this;
  }

  /**
   * Set offset.
   *
   * @param mixed $value
   *   The value.
   */
  public function setOffset($value) {
    $this->setSetting('offset', $value);
    return $this;
  }

  /**
   * Set delay.
   *
   * @param mixed $value
   *   The value.
   */
  public function setDelay($value) {
    $this->setSetting('delay', $value);
    return $this;
  }

  /**
   * Set duration.
   *
   * @param mixed $value
   *   The value.
   */
  public function setDuration($value) {
    $this->setSetting('duration', $value);
    return $this;
  }

  /**
   * Set easing.
   *
   * @param mixed $value
   *   The value.
   */
  public function setEasing($value) {
    $allowed = ExoAosSettings::getElementEasings();
    if (isset($allowed[$value])) {
      $this->setSetting('easing', $value);
    }
    return $this;
  }

  /**
   * Set once.
   *
   * @param mixed $value
   *   The value.
   */
  public function setOnce($value = TRUE) {
    $this->setSetting('once', $value === TRUE);
    return $this;
  }

  /**
   * Set mirror.
   *
   * @param bool $value
   *   The value.
   */
  public function setMirror($value = TRUE) {
    $this->setSetting('mirror', $value === TRUE);
    return $this;
  }

  /**
   * Set anchor placement.
   *
   * @param mixed $value
   *   The value.
   */
  public function setAnchorPlacement($value) {
    $allowed = ExoAosSettings::getElementAnchorPlacements();
    if (isset($allowed[$value])) {
      $this->setSetting('anchorPlacement', $value);
    }
    return $this;
  }

  /**
   * Get the attributes object.
   */
  public function getAttributes() {
    foreach (ExoAosSettings::getElementProperties() as $key => $data_key) {
      $value = $this->exoSettings->getSetting($key, $key != 'animation');
      if ($value) {
        if (is_bool($value)) {
          $value = $value ? 1 : 0;
        }
        $this->attributes->setAttribute('data-' . $data_key, $value);
      }
    }
    return $this->attributes;
  }

  /**
   * Get the attributes object.
   */
  public function getAttachments() {
    $attached = [];
    $attached['library'][] = 'exo_aos/base';
    $attached['drupalSettings']['exoAos']['defaults'] = $this->exoSettings->getSiteSettingsDiff();
    return $attached;
  }

  /**
   * Applies the values of this ExoAos object to a render array.
   *
   * @param array &$build
   *   A render array.
   */
  public function applyTo(array &$build) {
    $build += [
      '#attributes' => [],
      '#attached' => [],
    ];
    $build['#attached'] = array_merge_recursive($build['#attached'], $this->getAttachments());
    $build['#attributes'] = array_merge_recursive($build['#attributes'], $this->getAttributes()->toArray());
  }

}
