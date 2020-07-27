<?php

namespace Drupal\exo_alchemist;

use Drupal\Core\Template\Attribute;

/**
 * Collects, sanitizes, and renders HTML attributes.
 */
class ExoComponentAttribute extends Attribute {

  /**
   * Flag indicating if attribute is being used within layout builder.
   *
   * @var bool
   */
  protected $isLayoutBuilder = FALSE;

  /**
   * Constructs a \Drupal\Core\Template\Attribute object.
   *
   * @param array $attributes
   *   An associative array of key-value pairs to be converted to attributes.
   * @param bool $is_layout_builder
   *   Setting to true will enable the possibility of entering edit mode.
   */
  public function __construct($attributes = [], $is_layout_builder = FALSE) {
    $this->setAsLayoutBuilder($is_layout_builder);
    foreach ($attributes as $name => $value) {
      $this->offsetSet($name, $value);
    }
  }

  /**
   * Set this attribute set as running within layout builder.
   *
   * @param bool $is_layout_builder
   *   Setting to true will enable the possibility of entering edit mode.
   */
  public function setAsLayoutBuilder($is_layout_builder = TRUE) {
    $this->isLayoutBuilder = $is_layout_builder;
  }

  /**
   * Adds modifier.
   *
   * @param mixed $attributes
   *   An associative array of key-value pairs to be converted to attributes.
   *
   * @return $this
   */
  public function addModifier($attributes = []) {
    return $this->addAttributes($attributes);
  }

  /**
   * Adds enhancement.
   *
   * @param mixed $attributes
   *   An associative array of key-value pairs to be converted to attributes.
   *
   * @return $this
   */
  public function addEnhancement($attributes = []) {
    return $this->addAttributes($attributes);
  }

  /**
   * Adds animation.
   *
   * @param mixed $attributes
   *   An associative array of key-value pairs to be converted to attributes.
   *
   * @return $this
   */
  public function addAnimation($attributes = []) {
    return $this->addAttributes($attributes);
  }

  /**
   * Adds attributes.
   *
   * @param mixed $attributes
   *   An associative array of key-value pairs to be converted to attributes.
   *
   * @return $this
   */
  protected function addAttributes($attributes = []) {
    if ($attributes instanceof Attribute) {
      $attributes = $attributes->toArray();
    }
    foreach ($attributes as $name => $value) {
      if ($name === 'class') {
        $this->addClass($value);
      }
      else {
        $this->offsetSet($name, $value);
      }
    }
    return $this;
  }

  /**
   * Set this attribute as editable.
   */
  public function events($allow = TRUE) {
    if ($this->isLayoutBuilder === TRUE) {
      if ($allow) {
        $this->addClass('exo-component-event-allow');
      }
      else {
        $this->removeClass('exo-component-event-allow');
      }
    }
    return $this;
  }

  /**
   * Set this attribute as editable.
   */
  public function editable($is_editable = TRUE) {
    if ($this->isLayoutBuilder === TRUE) {
      if ($is_editable) {
        $this->addClass('exo-component-field-edit');
      }
      else {
        $this->removeClass('exo-component-field-edit');
      }
    }
    return $this;
  }

  /**
   * Allowing cloning.
   */
  public function clone() {
    return clone $this;
  }

}
