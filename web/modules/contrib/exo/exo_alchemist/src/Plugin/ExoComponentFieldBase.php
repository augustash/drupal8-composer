<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Base class for Component Field plugins.
 */
abstract class ExoComponentFieldBase extends PluginBase implements ExoComponentFieldInterface {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentInstallEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentUpdateEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentUninstallEntityType(ExoComponentDefinitionField $field, ConfigEntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentPropertyInfo(ExoComponentDefinitionField $field) {
    return $this->pluginDefinition['properties'];
  }

  /**
   * {@inheritdoc}
   */
  public function componentFormAlter(array &$form, FormStateInterface $form_state, ExoComponentDefinitionField $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function componentParents(ExoComponentDefinitionField $field, $delta) {
    return [
      $field->safeId(),
      $delta,
    ];
  }

  /**
   * Return markup that can be used for a placeholder.
   *
   * @param string $text
   *   The placeholder text.
   */
  protected function componentPlaceholder($text) {
    return [
      '#type' => 'inline_template',
      '#template' => '<div class="exo-alchemist-component-placeholder"><span class="exo-alchemist-component-title">{{ title }}</span> <span class="exo-alchemist-component-description">{{ description }}</span></div>',
      '#context' => [
        'title' => $text,
        'description' => $this->icon('This box will be replaced with the actual content when in full view.')->setIcon('regular-question-circle'),
      ],
    ];
  }

}
