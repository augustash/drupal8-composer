<?php

namespace Drupal\exo_modal\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command to close a modal.
 *
 * @ingroup ajax
 */
class ExoModalCloseCommand implements CommandInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'exoModalClose',
    ];
  }

}
