<?php

namespace Drupal\exo_modal\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Defines a fallback plugin for missing block plugins.
 *
 * @Block(
 *   id = "exo_modal_close",
 *   admin_label = @Translation("eXo Modal Close"),
 *   category = @Translation("eXo Modal"),
 * )
 */
class ExoModalCloseBlock extends BlockBase {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build() {
    return ['#markup' => '<a href="#" class="exo-modal-action exo-modal-close-button" data-exo-modal-close="">' . $this->icon('Close')->setIcon('regular-times')->setIconOnly() . '</a>'];
  }

}
