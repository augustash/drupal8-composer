<?php

namespace Drupal\exo_entity_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo\Plugin\Field\FieldWidget\ExoAlignmentHorizontalWidget;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'exo image' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_node_embed",
 *   label = @Translation("eXo Node Embed"),
 *   provider = "node",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoNodeEmbedFormatter extends EntityReferenceEntityFormatter {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'alignment' => 'center',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['alignment'] = [
      '#type' => 'exo_radios',
      '#exo_style' => 'inline',
      '#title' => $this->t('Alignment'),
      '#default_value' => $this->getSetting('alignment'),
      '#options' => ExoAlignmentHorizontalWidget::defaultOptions(),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as &$element) {
      $element['#attributes']['class'][] = 'exo-embed-alignment-' . $this->getSetting('alignment');
      // @see exo_entity_embed_media_embed_alter().
      $element['#remove_theme_wrappers'] = TRUE;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $route_name = \Drupal::routeMatch()->getRouteName();
    if (substr($route_name, 0, 27) == 'entity.entity_view_display.') {
      return FALSE;
    }
    return parent::isApplicable($field_definition);
  }

}
