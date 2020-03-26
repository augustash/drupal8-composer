<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\exo_toolbar\ExoToolbarSection;

/**
 * Base class for eXo theme plugins.
 */
abstract class ExoToolbarRegionHorizontalBase extends ExoToolbarRegionBase implements ExoToolbarRegionHorizontalInterface {

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    return [
      new ExoToolbarSection('left', $this->t('Left')),
      new ExoToolbarSection('right', $this->t('Right'), 'desc'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getAlignment() {
    return 'horizontal';
  }

}
