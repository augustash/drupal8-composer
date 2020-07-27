<?php

namespace Drupal\exo_alchemist\Definition;

use Drupal\exo\Shared\ExoArrayAccessDefinitionTrait;

/**
 * Class ExoComponentDefinitionAnimation.
 *
 * @package Drupal\exo_alchemist\Definition
 */
class ExoComponentDefinitionAnimation implements \ArrayAccess {

  use ExoArrayAccessDefinitionTrait;

  /**
   * Default field values.
   *
   * @var array
   */
  protected $definition = [
    'name' => NULL,
    'label' => NULL,
    'animation' => 'fade',
    'offset' => 120,
    'delay' => 0,
    'duration' => 400,
    'easing' => 'ease',
    'once' => FALSE,
    'mirror' => FALSE,
    'anchorPlacement' => 'top-bottom',
  ];

  /**
   * The parent component.
   *
   * @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   */
  protected $component;

  /**
   * ExoComponentDefinitionAnimationProperty constructor.
   */
  public function __construct($name, array $values, $component) {
    $this->component = $component;
    foreach ($values as $key => $value) {
      if (array_key_exists($key, $this->definition)) {
        $this->definition[$key] = $value;
      }
    }
    $this->definition['name'] = !isset($values['name']) ? $name : $values['name'];
    $this->definition['label'] = $values['label'];
  }

  /**
   * Return array definition.
   *
   * @return array
   *   Array definition.
   */
  public function toArray() {
    $definition = $this->definition;
    return array_filter($definition);
  }

  /**
   * Return array definition as settings for an animation.
   *
   * @return array
   *   Array definition.
   */
  public function toAnimationSettings() {
    $definition = $this->toArray();
    unset($definition['name'], $definition['label']);
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getComponent()->id() . '_' . $this->getName();
  }

  /**
   * Get Name property.
   *
   * @return mixed
   *   Property value.
   */
  public function getName() {
    return $this->definition['name'];
  }

  /**
   * Get Label property.
   *
   * @return mixed
   *   Property value.
   */
  public function getLabel() {
    return $this->definition['label'];
  }

  /**
   * Get component property.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The eXo component.
   */
  public function getComponent() {
    return $this->component;
  }

  /**
   * Set component property.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $component
   *   Property value.
   *
   * @return $this
   */
  public function setComponent(ExoComponentDefinition $component) {
    $this->component = $component;
    return $this;
  }

}
