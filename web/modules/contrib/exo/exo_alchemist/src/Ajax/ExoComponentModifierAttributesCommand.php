<?php

namespace Drupal\exo_alchemist\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * An AJAX command for updating component modifier properties.
 *
 * @ingroup ajax
 */
class ExoComponentModifierAttributesCommand implements CommandInterface {

  /**
   * An array of property/value pairs to set in the CSS for the selector.
   *
   * @var array
   */
  protected $attributes = [];

  /**
   * Constructs a CssCommand object.
   *
   * @param array $attributes
   *   An array of CSS property/value pairs to set.
   */
  public function __construct(array $attributes = []) {
    $this->attributes = $attributes;
  }

  /**
   * Adds a property/value pair to the CSS to be added to this element.
   *
   * @param string $property
   *   The CSS property to be changed.
   * @param mixed $value
   *   The new value of the CSS property.
   *
   * @return $this
   */
  public function setProperty($property, $value) {
    $this->attributes[$property] = $value;
    return $this;
  }

  /**
   * Implements Drupal\Core\Ajax\CommandInterface:render().
   */
  public function render() {

    return [
      'command' => 'exoComponentModifierAttributes',
      'argument' => $this->attributes,
    ];
  }

}
