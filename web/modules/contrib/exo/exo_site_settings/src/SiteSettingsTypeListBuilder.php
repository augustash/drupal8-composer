<?php

namespace Drupal\exo_site_settings;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of config page type entities.
 */
class SiteSettingsTypeListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_site_settings_type_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Type');
    $header['id'] = $this->t('ID');
    $header['aggregated'] = $this->t('Aggregated');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id']['data']['#markup'] = $entity->id();
    $row['aggregated']['data']['#markup'] = $entity->isAggregate() ? $this->t('Yes') : $this->t('No');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $entities = $this->load();
    // If there are not multiple vocabularies, disable dragging by unsetting the
    // weight key.
    if (count($entities) <= 1) {
      unset($this->weightKey);
    }
    $build = parent::render();
    $build['table']['#empty'] = t('No config types available. <a href=":link">Add type</a>.', [':link' => Url::fromRoute('entity.exo_site_settings_type.add_form')]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    // Place the edit operation after the operations added by field_ui.module
    // which have the weights 15, 20, 25.
    if (isset($operations['edit'])) {
      $operations['edit']['weight'] = 30;
    }
    return $operations;
  }

}
