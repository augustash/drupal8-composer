<?php

namespace Drupal\exo_alchemist\Plugin\SectionStorage;

use Drupal\exo_alchemist\ExoComponentSectionStorageInterface;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Defines the 'defaults' section storage type.
 */
class ExoDefaultsSectionStorage extends DefaultsSectionStorage implements ExoComponentSectionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->getContextValue('display');
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    // Parent entity is same as entity.
    return $this->getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function getViewMode() {
    return $this->getContextValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function getRegionSize($delta, $region) {
    $section = $this->getSection($delta);
    $settings = $section->getLayoutSettings();
    return isset($settings['column_sizes'][$region]) ? $settings['column_sizes'][$region] : 'full';
  }

}
