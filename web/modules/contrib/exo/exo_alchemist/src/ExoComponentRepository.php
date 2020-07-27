<?php

namespace Drupal\exo_alchemist;

use Drupal\block_content\Access\RefinableDependentAccessInterface;
use Drupal\block_content\Access\RefinableDependentAccessTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\layout_builder\OverridesSectionStorageInterface;

/**
 * Class ExoComponentRepository.
 */
class ExoComponentRepository {
  use RefinableDependentAccessTrait;

  /**
   * Drupal\exo_alchemist\ExoComponentManager definition.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * The section storage manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   */
  protected $sectionStorageManager;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * Constructs a new ExoComponentRepository object.
   *
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The exo component manager.
   */
  public function __construct(ExoComponentManager $exo_component_manager) {
    $this->exoComponentManager = $exo_component_manager;
  }

  /**
   * Get all components attached to an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param bool $use_tempstore
   *   Flag indicating if the tempstore storage should be used.
   *
   * @return \Drupal\block_content\Entity\BlockContent[]
   *   The component entities.
   */
  public function getComponents(EntityInterface $entity, $use_tempstore = FALSE) {
    $components = [];
    $sections = $this->getEntitySections($entity, $use_tempstore);
    foreach ($sections as $section) {
      foreach ($section->getComponents() as $component) {
        $components[] = $this->extractBlockEntity($component->getPlugin());
      }
    }
    return $components;
  }

  /**
   * Get all components attached to an entity that match a certain id.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $component_id
   *   The component id.
   * @param bool $use_tempstore
   *   Flag indicating if the tempstore storage should be used.
   *
   * @return \Drupal\block_content\Entity\BlockContent[]
   *   The component entities.
   */
  public function getComponentsById(EntityInterface $entity, $component_id, $use_tempstore = FALSE) {
    return array_filter($this->getComponents($entity, $use_tempstore), function ($component) use ($component_id) {
      $definition = $this->exoComponentManager->getEntityComponentDefinition($component);
      return $definition->id() === $component_id;
    });
  }

  /**
   * Get all field items within a component entity by type.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $field_type
   *   The field type.
   * @param bool $use_tempstore
   *   Flag indicating if the tempstore storage should be used.
   *
   * @return \Drupal\block_content\Entity\BlockContent[]
   *   The component entities.
   */
  public function getComponentsWithFieldType(EntityInterface $entity, $field_type, $use_tempstore = FALSE) {
    return array_filter($this->getComponents($entity, $use_tempstore), function ($component) use ($field_type) {
      $definition = $this->exoComponentManager->getEntityComponentDefinition($component);
      return !empty($definition->getFieldsByType($field_type));
    });
  }

  /**
   * Get all field items within a component entity by type.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $field_type
   *   The field type.
   * @param bool $use_tempstore
   *   Flag indicating if the tempstore storage should be used.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The component entities.
   */
  public function getComponentItemsByFieldType(EntityInterface $entity, $field_type, $use_tempstore = FALSE) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */
    $items = NULL;
    foreach ($this->getComponentsWithFieldType($entity, $field_type, $use_tempstore) as $component) {
      $definition = $this->exoComponentManager->getEntityComponentDefinition($component);
      foreach ($definition->getFieldsByType($field_type) as $field) {
        $field_name = $field->safeId();
        if ($component->hasField($field_name)) {
          if ($items) {
            foreach ($component->get($field_name) as $item) {
              $items->appendItem($item);
            }
          }
          else {
            $items = clone $component->get($field_name);
          }
        }
      }
    }
    return $items;
  }

  /**
   * Loads the component entity of the block.
   *
   * @param \Drupal\layout_builder\Plugin\Block\InlineBlock $block_plugin
   *   The block plugin.
   *
   * @return \Drupal\block_content\BlockContentInterface
   *   The block content entity.
   */
  protected function extractBlockEntity(InlineBlock $block_plugin) {
    $entity = NULL;
    $configuration = $block_plugin->getConfiguration();
    if (!empty($configuration['block_serialized'])) {
      $entity = unserialize($configuration['block_serialized']);
    }
    elseif (!empty($configuration['block_uuid'])) {
      $entity = $this->exoComponentManager->entityLoadByUuid($configuration['block_uuid']);
    }
    elseif (!empty($configuration['block_revision_id'])) {
      $entity = $this->exoComponentManager->entityLoadByRevisionId($configuration['block_revision_id']);
    }
    if ($entity instanceof RefinableDependentAccessInterface && $dependee = $this->getAccessDependency()) {
      $entity->setAccessDependency($dependee);
    }
    return $entity;
  }

  /**
   * Gets the sections for an entity if any.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param bool $use_tempstore
   *   Flag indicating if the tempstore storage should be used.
   *
   * @return \Drupal\layout_builder\Section[]
   *   The entity layout sections if available.
   */
  protected function getEntitySections(EntityInterface $entity, $use_tempstore = FALSE) {
    $section_storage = $this->getSectionStorageForEntity($entity, $use_tempstore);
    return $section_storage ? $section_storage->getSections() : [];
  }

  /**
   * Gets the section storage for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param bool $use_tempstore
   *   Flag indicating if the tempstore storage should be used.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface|null
   *   The section storage if found otherwise NULL.
   */
  protected function getSectionStorageForEntity(EntityInterface $entity, $use_tempstore = FALSE) {
    // @todo Take into account other view modes in
    //   https://www.drupal.org/node/3008924.
    $view_mode = 'full';
    if ($entity instanceof LayoutEntityDisplayInterface) {
      $contexts['display'] = EntityContext::fromEntity($entity);
      $contexts['view_mode'] = new Context(new ContextDefinition('string'), $entity->getMode());
    }
    else {
      $contexts['entity'] = EntityContext::fromEntity($entity);
      if ($entity instanceof FieldableEntityInterface) {
        $view_mode = LayoutBuilderEntityViewDisplay::collectRenderDisplay($entity, $view_mode)->getMode();
        $display = EntityViewDisplay::collectRenderDisplay($entity, $view_mode);
        if ($display instanceof LayoutEntityDisplayInterface) {
          $contexts['display'] = EntityContext::fromEntity($display);
        }
        $contexts['view_mode'] = new Context(new ContextDefinition('string'), $view_mode);
      }
    }
    $section_storage = $this->sectionStorageManager()->findByContext($contexts, new CacheableMetadata());
    if (!$entity instanceof LayoutEntityDisplayInterface && !$section_storage instanceof OverridesSectionStorageInterface) {
      $section_storage = $this->sectionStorageManager()->load('overrides', $contexts, new CacheableMetadata());
    }
    if ($section_storage && $use_tempstore && $this->layoutTempstoreRepository()->has($section_storage)) {
      $section_storage = $this->layoutTempstoreRepository()->get($section_storage);
    }
    return $section_storage;
  }

  /**
   * Gets the section storage manager.
   *
   * @return \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   *   The section storage manager.
   */
  private function sectionStorageManager() {
    return $this->sectionStorageManager ?: \Drupal::service('plugin.manager.layout_builder.section_storage');
  }

  /**
   * Gets the layout builder tempstore repository.
   *
   * @return \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   *   The layout builder tempstore repository.
   */
  private function layoutTempstoreRepository() {
    return $this->layoutTempstoreRepository ?: \Drupal::service('layout_builder.tempstore_repository');
  }

}
