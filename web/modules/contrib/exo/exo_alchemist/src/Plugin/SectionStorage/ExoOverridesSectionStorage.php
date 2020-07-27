<?php

namespace Drupal\exo_alchemist\Plugin\SectionStorage;

use Drupal\exo_alchemist\ExoComponentSectionStorageInterface;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;

/**
 * Extends the 'overrides' section storage type.
 */
class ExoOverridesSectionStorage extends OverridesSectionStorage implements ExoComponentSectionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getStorageId() {
    $entity = $this->getEntity();
    return $entity->getEntityTypeId() . '.' . ($entity->isNew() ? $entity->uuid() : $entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    // Convert to public method.
    $entity = parent::getEntity();
    return $entity;
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
