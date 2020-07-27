<?php

namespace Drupal\exo_alchemist\Ajax;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that focuses a field by path.
 *
 * @ingroup ajax
 */
class ExoComponentFieldFocus implements CommandInterface {

  /**
   * A path to the field.
   *
   * @var string
   */
  protected $path;

  /**
   * Constructs a ExoComponentFieldFocus object.
   *
   * @param string $path
   *   The path to the field.
   */
  public function __construct($path) {
    $this->path = $path;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'exoComponentFieldFocus',
      'id' => Html::getId($this->path),
    ];
  }

}
