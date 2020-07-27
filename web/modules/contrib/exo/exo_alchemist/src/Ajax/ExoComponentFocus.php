<?php

namespace Drupal\exo_alchemist\Ajax;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that focus the current component.
 *
 * @ingroup ajax
 */
class ExoComponentFocus implements CommandInterface {

  /**
   * A uuid of the component.
   *
   * @var string
   */
  protected $uuid;

  /**
   * Constructs a ExoComponentFocus object.
   *
   * @param string $uuid
   *   The uuid to the component.
   */
  public function __construct($uuid) {
    $this->uuid = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'exoComponentFocus',
      'id' => Html::getId('exo-component-' . $this->uuid),
    ];
  }

}
