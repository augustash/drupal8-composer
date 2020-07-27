<?php

namespace Drupal\exo_alchemist;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Provides a management object for new components.
 */
class ExoComponentBuilder {

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * The eXo component manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * The UUID generator service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The working entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $workingEntity;

  /**
   * The view mode.
   *
   * @var string
   */
  protected $viewMode;

  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\SectionStorageInterface
   */
  protected $sectionStorage;

  /**
   * The section.
   *
   * @var \Drupal\layout_builder\Section
   */
  protected $section;

  /**
   * Constructs a new SectionComponent.
   *
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The component manager.
   * @param Drupal\Component\Uuid\UuidInterface $uuid
   *   The uuid generator.
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage manager.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to insert the component into.
   * @param bool $temporary
   *   If TRUE, components will only be saved as temporary.
   * @param string $view_mode
   *   The entity view mode.
   *
   * @return \Drupal\exo_alchemist\ExoComponentEntityBuilder
   *   The component container.
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, ExoComponentManager $exo_component_manager, UuidInterface $uuid, SectionStorageInterface $section_storage, ContentEntityInterface $entity, $temporary = FALSE, $view_mode = 'full') {
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->exoComponentManager = $exo_component_manager;
    $this->sectionStorage = $section_storage;
    $this->uuidGenerator = $uuid;
    $this->sectionStorage = $section_storage;
    $this->entity = $entity;
    $this->viewMode = $view_mode;
    $this->enforceAsTemporary($temporary);
  }

  /**
   * Create a new component.
   *
   * @param string $component_type_id
   *   The component type id.
   * @param int $position
   *   The position in which to insert the component. If a component is already
   *   in this place, it will be replaced.
   * @param int $delta
   *   The delta of the section.
   * @param string $region
   *   The region within the section.
   *
   * @return \Drupal\exo_alchemist\ExoComponentContainer
   *   The component container.
   */
  public function create($component_type_id, $position, $delta = 0, $region = 'content') {
    $container = new ExoComponentContainer($this->exoComponentManager, $component_type_id);
    $this->insertSectionComponent($container, $position, $delta, $region);
    $this->flagForSave();
    return $container;
  }

  /**
   * Insert a section component.
   *
   * @param \Drupal\exo_alchemist\ExoComponentContainer $container
   *   The component container.
   * @param int $position
   *   The position in which to insert the component. If a component is already
   *   in this place, it will be replaced.
   * @param int $delta
   *   The delta of the section.
   * @param string $region
   *   The region within the section.
   */
  protected function insertSectionComponent(ExoComponentContainer $container, $position, $delta = 0, $region = 'content') {
    /** @var \Drupal\layout_builder\SectionComponent[] $components */
    $section = $this->getSection($delta);
    // Only allow inserting of components into unlocked sections.
    if (!empty($section->getLayoutSettings()['exo_section_lock'])) {
      $unlocked_sections = ExoComponentGenerator::getUnlockedSections($this->sectionStorage->getSections());
      $message = 'The section delta of (' . (string) $delta . ') is locked.';
      if (empty($unlocked_sections)) {
        $message .= ' This entity does not have any unlocked sections.';
      }
      else {
        $message .= ' Available section deltas are (' . implode(', ', array_keys($unlocked_sections)) . ').';
      }
      throw new \Exception($message);
    }
    // Make sure region exists within component.
    $layout = $this->layoutPluginManager()->getDefinition($section->getLayoutId());
    $regions = $layout->getRegions();
    if (!isset($regions[$region])) {
      $message = 'The section region of (' . (string) $region . ') does not exist within this layout.';
      if (empty($regions)) {
        $message .= ' There are not sections configured for this layout.';
      }
      else {
        $message .= ' Available regions are (' . implode(', ', array_keys($regions)) . ').';
      }
      throw new \Exception($message);
    }
    $section_component = $this->createSectionComponent($container, $region);
    $components = array_values($section->getComponentsByRegion($section_component->getRegion()));
    $count = count($components);
    if ($position > $count) {
      $section->appendComponent($section_component);
    }
    else {
      $section->insertComponent($position, $section_component);
    }
    if (isset($components[$position])) {
      $this->removeSectionComponent($components[$position], $delta);
    }
  }

  /**
   * Create the section component.
   *
   * @param \Drupal\exo_alchemist\ExoComponentContainer $container
   *   The component container.
   * @param string $region
   *   The region within the section.
   *
   * @return \Drupal\layout_builder\SectionComponent
   *   The section component.
   */
  protected function createSectionComponent(ExoComponentContainer $container, $region = 'content') {
    $component = $container->getComponent();
    return new SectionComponent($this->uuidGenerator->generate(), $region, [
      'id' => 'inline_block:' . $component->bundle(),
      'label_display' => FALSE,
      'exo_component' => $component,
    ]);
  }

  /**
   * Remove a component from an entity.
   *
   * @param int $position
   *   The position in which to insert the component. If a component is already
   *   in this place, it will be replaced.
   * @param int $delta
   *   The delta of the section.
   * @param string $region
   *   The region within the section.
   *
   * @return $this
   */
  public function remove($position, $delta = 0, $region = 'content') {
    if ($this->hasSection($delta)) {
      $section = $this->getSection($delta);
      $components = array_values($section->getComponentsByRegion($region));
      if (isset($components[$position])) {
        $this->removeSectionComponent($components[$position], $delta);
        $this->flagForSave();
      }
    }
    return $this;
  }

  /**
   * Remove a section component.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   The section component to remove.
   * @param int $delta
   *   The delta of the section.
   *
   * @return $this
   */
  protected function removeSectionComponent(SectionComponent $component, $delta = 0) {
    $this->flagForRemoval($component);
    $section = $this->getSection($delta);
    $section->removeComponent($component->getUuid());
    return $this;
  }

  /**
   * Remove a section component entity.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   The section component to remove.
   *
   * @return $this
   */
  public function doRemove(SectionComponent $component) {
    if (!$this->isTemporary() && $this->exoComponentManager->entityAllowCleanup($this->entity)) {
      $configuration = $component->get('configuration');
      if (!empty($configuration['block_revision_id'])) {
        $entity_for_removal = $this->exoComponentManager->entityLoadByRevisionId($configuration['block_revision_id']);
        if ($entity_for_removal) {
          $entity_for_removal->delete();
        }
      }
    }
    return $this;
  }

  /**
   * Save all components attached to an entity.
   *
   * Will be automatically called when saving the parent entity.
   *
   * @param \Drupal\layout_builder\Section[] $sections
   *   The sections.
   */
  public function doSave(array $sections) {
    if ($this->isTemporary()) {
      $this->saveAsTemporary($sections);
    }
    else {
      $this->saveAsLive($sections);
    }
  }

  /**
   * Save all component to temporary layout builder.
   *
   * @param \Drupal\layout_builder\Section[] $sections
   *   The sections.
   */
  protected function saveAsTemporary(array $sections) {
    $this->prepareForSave($sections);
    $this->layoutTempstoreRepository->set($this->sectionStorage);
  }

  /**
   * Save all component to temporary layout builder.
   *
   * @param \Drupal\layout_builder\Section[] $sections
   *   The sections.
   */
  protected function saveAsLive(array $sections) {
    $this->prepareForSave($sections);
    $this->entity->get(OverridesSectionStorage::FIELD_NAME)->setValue($sections);
  }

  /**
   * Prepare components for saving.
   *
   * @param \Drupal\layout_builder\Section[] $sections
   *   The sections.
   *
   * @return $this
   */
  protected function prepareForSave(array $sections) {
    $this->sectionStorage->removeAllSections(TRUE);
    foreach ($sections as $section) {
      $components = $section->getComponents();
      foreach ($components as $component) {
        $configuration = $component->get('configuration');
        if (isset($configuration['exo_component'])) {
          $entity = $configuration['exo_component'];
          // Set any temporary values as actual field values.
          if (!empty($entity->_exoComponentValues)) {
            foreach ($entity->_exoComponentValues as $values) {
              $this->exoComponentManager->getExoComponentFieldManager()->setEntityFieldValue($values, $entity);
            }
          }
          // Serialize any exo components and place it where layout builder
          // needs it.
          $configuration['block_serialized'] = serialize($entity);
          unset($configuration['exo_component']);
        }
        $component->setConfiguration($configuration);
      }
      $this->sectionStorage->appendSection($section);
    }
    return $this;
  }

  /**
   * Flag this entity for saving.
   *
   * @return $this
   */
  protected function flagForSave() {
    $this->setTemporaryValue('sections', $this->sectionStorage->getSections());
    return $this;
  }

  /**
   * Flag this entity for saving.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   The section component to remove.
   *
   * @return $this
   */
  protected function flagForRemoval(SectionComponent $component) {
    $this->appendTemporaryValue('remove', $component);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function enforceAsTemporary($value = TRUE) {
    $this->setTemporaryValue('temporary', $value);
    return $this;
  }

  /**
   * Check if we should use temporary storage.
   *
   * @return bool
   *   TRUE if we should.
   */
  public function isTemporary() {
    return $this->getTemporaryValue('temporary');
  }

  /**
   * Gets a section.
   *
   * @param int $delta
   *   The delta of the section.
   * @param int $section_layout
   *   The layout plugin that will be used to create the section if one does
   *   not already exist at the provided delta.
   *
   * @return \Drupal\layout_builder\Section
   *   The layout section.
   */
  public function getSection($delta = 0, $section_layout = 'layout_onecol') {
    $key = (string) $delta;
    if (!isset($this->section[$key])) {
      $section_storage = $this->sectionStorage;
      if (!$this->hasSection($delta)) {
        $section_storage->insertSection($delta, new Section($section_layout));
      }
      $this->section[$key] = $section_storage->getSection($delta);
    }
    return $this->section[$key];
  }

  /**
   * Check if section exists.
   *
   * @param int $delta
   *   The delta of the section.
   *
   * @return bool
   *   TRUE if section exists.
   */
  public function hasSection($delta = 0) {
    $section_storage = $this->sectionStorage;
    $sections = $section_storage->getSections();
    return isset($sections[$delta]);
  }

  /**
   * Sets a temporary value on an entity.
   *
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   *
   * @return $this
   */
  public function setTemporaryValue($key, $value) {
    $this->entity->_exoComponent[$this->viewMode][$key] = $value;
    return $this;
  }

  /**
   * Appends a temporary value on an entity.
   *
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   *
   * @return $this
   */
  public function appendTemporaryValue($key, $value) {
    $this->entity->_exoComponent[$this->viewMode][$key][] = $value;
    return $this;
  }

  /**
   * Gets a temporary value on an entity.
   *
   * @param string $key
   *   The key.
   *
   * @return mixed|null
   *   The value if it exists.
   */
  public function getTemporaryValue($key) {
    return isset($this->entity->_exoComponent[$this->viewMode][$key]) ? $this->entity->_exoComponent[$this->viewMode][$key] : NULL;
  }

  /**
   * Removes temporary record.
   *
   * @return $this
   */
  public function removeTemporary() {
    unset($this->entity->_exoComponent[$this->viewMode]);
    return $this;
  }

  /**
   * Gets the section storage.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface|null
   *   The section storage.
   */
  public function getStorage() {
    return $this->sectionStorage;
  }

  /**
   * Wraps the layout plugin manager.
   *
   * @return \Drupal\Core\Layout\LayoutPluginManagerInterface
   *   The layout plugin manager.
   */
  protected function layoutPluginManager() {
    return \Drupal::service('plugin.manager.core.layout');
  }

}
