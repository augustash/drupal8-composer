<?php

namespace Drupal\exo_alchemist\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that blurs the current component.
 *
 * @ingroup ajax
 */
class ExoComponentBlur implements CommandInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'exoComponentBlur',
    ];
  }

}
