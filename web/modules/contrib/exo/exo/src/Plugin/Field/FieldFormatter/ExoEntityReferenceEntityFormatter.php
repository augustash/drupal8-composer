<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_entity_reference_entity_view",
 *   label = @Translation("eXo Rendered Entity"),
 *   description = @Translation("Display a subset of the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoEntityReferenceEntityFormatter extends EntityReferenceEntityFormatter {
  use ExoEntityReferenceSelectionTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return self::selectionDefaultSettings() + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return array_merge($this->selectionSettingsSummary(), parent::settingsSummary());
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements += $this->selectionSettingsForm($form, $form_state);
    $elements += parent::settingsForm($form, $form_state);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    $entities = parent::getEntitiesToView($items, $langcode);
    $entities = $this->filterSelectionEntities($entities);
    return $entities;
  }

}
