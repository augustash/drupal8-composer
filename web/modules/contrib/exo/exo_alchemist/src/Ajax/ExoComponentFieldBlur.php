<?php

namespace Drupal\exo_alchemist\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that blurs the current field.
 *
 * @ingroup ajax
 */
class ExoComponentFieldBlur implements CommandInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'exoComponentFieldBlur',
    ];
  }

}
