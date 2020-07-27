<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo_alchemist\ExoComponentContextInterface;
use Drupal\exo_alchemist\ExoComponentContextTrait;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Base class for Component Field plugins.
 */
abstract class ExoComponentFieldBase extends ContextAwarePluginBase implements ExoComponentFieldInterface, ExoComponentContextInterface {

  use ExoIconTranslationTrait;
  use ExoComponentContextTrait;

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    return $this->configuration['fieldDefinition'];
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
  }

  /**
   * {@inheritdoc}
   */
  public function onInstall(ConfigEntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(ConfigEntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function onUninstall(ConfigEntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldInstall() {}

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdate() {}

  /**
   * {@inheritdoc}
   */
  public function onFieldUninstall() {}

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return $this->pluginDefinition['properties'];
  }

  /**
   * {@inheritdoc}
   */
  public function getParents() {
    if ($this->getFieldDefinition()->isComputed()) {
      // If this is a computed field, direct to parent.
      return array_merge($this->getFieldDefinition()->getComponent()->getParents(), [
        $this->getFieldDefinition()->getName(),
      ]);
    }
    return array_merge($this->getFieldDefinition()->getComponent()->getParents(), [
      $this->getFieldDefinition()->safeId(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getParentsAsPath() {
    return implode('.', $this->getParents());
  }

  /**
   * {@inheritdoc}
   */
  public function getItemParents($delta) {
    return array_merge($this->getParents(), [
      $delta,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getItemParentsAsPath($delta) {
    return implode('.', $this->getItemParents($delta));
  }

  /**
   * {@inheritdoc}
   */
  public function alterContexts(ContentEntityInterface $entity, array &$contexts) {}

  /**
   * {@inheritdoc}
   */
  public function isEditable(array $contexts) {
    // Never allow when we are locked and not on the default storage.
    if ($this->isLocked($contexts) && !$this->isDefaultStorage($contexts)) {
      return FALSE;
    }
    if (!$this instanceof ExoComponentFieldFormInterface) {
      return FALSE;
    }
    return $this->getFieldDefinition()->isEditable();
  }

  /**
   * {@inheritdoc}
   */
  public function isRemoveable(array $contexts) {
    // Never allow when we are locked and not on the default storage.
    if ($this->isLocked($contexts) && !$this->isDefaultStorage($contexts)) {
      return FALSE;
    }
    return $this->getFieldDefinition()->supportsUnlimimted();
  }

  /**
   * {@inheritdoc}
   */
  public function isHideable(array $contexts) {
    // Never allow when we are locked and not on the default storage.
    if ($this->isLocked($contexts) && !$this->isDefaultStorage($contexts)) {
      return FALSE;
    }
    // If it's required, you can't hide it.
    if ($this->getFieldDefinition()->isRequired()) {
      return FALSE;
    }
    return !$this->getFieldDefinition()->supportsMultiple() && !$this->getFieldDefinition()->getComponent()->isComputed() && $this->getFieldDefinition()->isHideable();
  }

  /**
   * {@inheritdoc}
   */
  public function isDefaultStorageLocked() {
    return $this instanceof ExoComponentFieldDefaultStorageLockInterface;
  }

  /**
   * Return markup that can be used for a placeholder.
   *
   * @param string $title
   *   The placeholder text.
   * @param string $description
   *   The placeholder description.
   *
   * @return array
   *   A render array.
   */
  protected function componentPlaceholder($title, $description = NULL) {
    $context = [
      'title' => $title,
    ];
    if ($description) {
      $context['description'] = $this->icon($description)->setIcon('regular-question-circle');
    }
    return [
      '#type' => 'inline_template',
      '#template' => '<div class="exo-alchemist-component-placeholder exo-font"><span class="exo-alchemist-component-title">{{ title }}</span>{% if description %} <span class="exo-alchemist-component-description">{{ description }}</span>{% endif %}</div>',
      '#context' => $context,
    ];
  }

  /**
   * Return markup that can be used for a placeholder with default description.
   *
   * @param string $title
   *   The placeholder text.
   * @param string $description
   *   The placeholder description.
   *
   * @return array
   *   A render array.
   */
  protected function componentPlaceholderDefault($title, $description = 'This box will be replaced with the actual content when in full view.') {
    return $this->componentPlaceholder($title, $description);
  }

  /**
   * Gets the entity that has the component.
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface
   *   The entity.
   */
  protected function getParentEntity() {
    return $this->getContextValue('entity');
  }

  /**
   * Gets the entity view mode that has the component.
   *
   * @return string
   *   The entity view mode.
   */
  protected function getParentViewMode() {
    return $this->getContextValue('view_mode');
  }

}
