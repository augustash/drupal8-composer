<?php

namespace Drupal\exo_alchemist\Controller;

use Drupal\block_content\Access\RefinableDependentAccessInterface;
use Drupal\block_content\Access\RefinableDependentAccessTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;

/**
 * Provides means of fetching target entity.
 */
trait ExoFieldParentsTrait {

  use RefinableDependentAccessTrait;

  /**
   * The block content entity.
   *
   * @var \Drupal\block_content\BlockContentInterface
   */
  protected $parentEntity;

  /**
   * Crawl path and return the child entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The parent entity.
   * @param array $parents
   *   The parents of the child entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The child entity.
   */
  protected function getTarget(ContentEntityInterface $entity, array $parents) {
    $items = NULL;
    $item = NULL;
    foreach ($parents as $parent) {
      if (is_numeric($parent) && $items) {
        /** @var \Drupal\Core\Field\FieldItemListInterface $items */
        $item = $items->get((int) $parent);
        if ($item && $item->entity) {
          $entity = $item->entity;
        }
      }
      elseif ($entity->hasField($parent) && !$entity->get($parent)->isEmpty()) {
        $items = $entity->get($parent);
      }
    }
    return [
      'entity' => $entity,
      'items' => $items,
      'item' => $item,
    ];
  }

  /**
   * Crawl path and return the child entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The parent entity.
   * @param array $parents
   *   The parents of the child entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The child entity.
   */
  protected function getTargetEntity(ContentEntityInterface $entity, array $parents = []) {
    return $this->getTarget($entity, $parents)['entity'];
  }

  /**
   * Crawl path and set the child entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The parent entity.
   * @param \Drupal\Core\Entity\ContentEntityInterface $child_entity
   *   The child entity.
   * @param array $parents
   *   The parents of the child entity.
   *
   * @return $this
   */
  protected function setTargetEntity(ContentEntityInterface $entity, ContentEntityInterface $child_entity, array $parents = []) {
    $target = $this->getTarget($entity, $parents);
    if (!empty($target['item'])) {
      $target['item']->setValue([
        'target_id' => NULL,
        'entity' => $child_entity,
      ]);
    }
    return $this;
  }

  /**
   * Crawl path and return the child entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The parent entity.
   * @param array $parents
   *   The parents of the child entity.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   Items.
   */
  protected function getTargetField(ContentEntityInterface $entity, array $parents) {
    return $this->getTarget($entity, $parents)['items'];
  }

  /**
   * Loads or creates the block content entity of the block.
   *
   * @param \Drupal\layout_builder\Plugin\Block\InlineBlock $block_plugin
   *   The block plugin.
   *
   * @return \Drupal\block_content\BlockContentInterface
   *   The block content entity.
   */
  protected function extractBlockEntity(InlineBlock $block_plugin) {
    if (!isset($this->parentEntity)) {
      $configuration = $block_plugin->getConfiguration();
      if (!empty($configuration['block_serialized'])) {
        $this->parentEntity = unserialize($configuration['block_serialized']);
      }
      elseif (!empty($configuration['block_uuid'])) {
        $entity = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $configuration['block_uuid']);
        $this->parentEntity = $entity;
      }
      elseif (!empty($configuration['block_revision_id'])) {
        $entity = \Drupal::entityTypeManager()->getStorage('block_content')->loadRevision($configuration['block_revision_id']);
        $this->parentEntity = $entity;
      }
      else {
        $this->parentEntity = \Drupal::entityTypeManager()->getStorage('block_content')->create([
          'type' => $this->getDerivativeId(),
          'reusable' => FALSE,
        ]);
      }
      if ($this->parentEntity instanceof RefinableDependentAccessInterface && $dependee = $this->getAccessDependency()) {
        $this->parentEntity->setAccessDependency($dependee);
      }
    }
    return $this->parentEntity;
  }

}
