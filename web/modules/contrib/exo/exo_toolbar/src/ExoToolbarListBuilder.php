<?php

namespace Drupal\exo_toolbar;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of eXo Toolbar entities.
 */
class ExoToolbarListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_toolbar_overview';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Title');
    $header['id'] = $this->t('ID');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id']['data']['#markup'] = $entity->id();
    $row['status']['data']['#markup'] = $entity->status() ? 'Enabled' : 'Disabled';
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
    $build['table']['#empty'] = t('No toolbars available. <a href=":link">Add toolbar</a>.', [':link' => Url::fromRoute('entity.exo_toolbar.add_form')]);
    return $build;
  }

  /**
   * Gets this list's default operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operations are for.
   *
   * @return array
   *   The array structure is identical to the return value of
   *   self::getOperations().
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if (isset($operations['edit'])) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form'),
      ];
    }
    return $operations;
  }

}
