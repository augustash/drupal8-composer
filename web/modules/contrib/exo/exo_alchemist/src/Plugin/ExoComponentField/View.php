<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayFormTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldPreviewEntityTrait;
use Drupal\views\Views;

/**
 * A 'view' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "view",
 *   label = @Translation("View"),
 * )
 */
class View extends ExoComponentFieldComputedBase {

  use ExoComponentFieldDisplayFormTrait;
  use ExoComponentFieldPreviewEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('view_id')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [view_id] be set.', $field->getType()));
    }
    if (!$field->hasAdditionalValue('view_display')) {
      $field->setAdditionalValue('view_display', 'default');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The view renderable.'),
    ];
    if ($this->getFieldDefinition()->getAdditionalValue('view_count')) {
      $properties['view_count'] = $this->t('The total number of results.');
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $value = [];
    $field = $this->getFieldDefinition();
    $view = Views::getView($field->getAdditionalValue('view_id'));
    $view->setDisplay($field->getAdditionalValue('view_display'));
    $args = [];
    // Allow args to be provided when previewing.
    if (($preview_args = $field->getAdditionalValue('view_preview_args')) && ($this->isPreview($contexts) || $this->isDefaultStorage($contexts))) {
      $args = $this->buildPreviewArgs($preview_args);
    }
    else {
      $args = $field->getAdditionalValue('view_args');
    }
    if ($args) {
      $view->setArguments($args);
    }
    $render = $view->buildRenderable();
    if ($this->isLayoutBuilder($contexts)) {
      // Views can contain forms.
      $render = $this->getFormAsPlaceholder($render);
    }
    if ($field->getAdditionalValue('view_count')) {
      $view->build($field->getAdditionalValue('view_display'));
      $value['count'] = $view->query->query()->countQuery()->execute()->fetchField();
    }
    $value['render'] = $render;
    return $value;
  }

  /**
   * Build preview args.
   *
   * @param array $preview_args
   *   The preview args provided by the field.
   */
  protected function buildPreviewArgs(array $preview_args) {
    $args = [];
    foreach ($preview_args as $arg) {
      if (is_array($arg)) {
        if (isset($arg['type'])) {
          switch ($arg['type']) {
            case 'entity':
              if ($preview_entity = $this->getPreviewEntity($arg['entity_type'], $arg['bundle'])) {
                $args[] = $preview_entity->id();
              }
              break;
          }
        }
      }
      else {
        $args[] = $arg;
      }
    }
    return $args;
  }

}
