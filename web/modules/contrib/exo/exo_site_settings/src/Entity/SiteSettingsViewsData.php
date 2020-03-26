<?php

namespace Drupal\exo_site_settings\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for config pages.
 */
class SiteSettingsViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
