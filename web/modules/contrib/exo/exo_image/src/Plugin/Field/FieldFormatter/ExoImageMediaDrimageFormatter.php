<?php

namespace Drupal\exo_image\Plugin\Field\FieldFormatter;

use Drupal\drimage\Plugin\Field\FieldFormatter\DrImageFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo_media\Plugin\Field\FieldFormatter\ExoMediaFormatterTrait;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\exo\Plugin\Field\FieldFormatter\ExoEntityReferenceSelectionTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Plugin implementation of the 'exo image drimage' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_image_media_drimage",
 *   label = @Translation("eXo Image: Dynamic Responsive Image"),
 *   provider = "drimage",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoImageMediaDrimageFormatter extends DrImageFormatter {
  use ExoMediaFormatterTrait;
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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $element) {
      $elements[$delta]['#item_attributes']['class'][] = 'exo-image';
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function needsEntityLoad(EntityReferenceItem $item) {
    return $this->mediaNeedsEntityLoad($item);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    $entities = $this->mediaGetEntitiesToView($items, $langcode);
    $entities = $this->filterSelectionEntities($entities);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if (!self::mediaIsApplicable($field_definition)) {
      return FALSE;
    }

    $storage = \Drupal::service('entity_type.manager')->getStorage('media_type');
    $settings = $field_definition->getSetting('handler_settings');
    if (isset($settings['target_bundles'])) {
      foreach ($settings['target_bundles'] as $bundle) {
        if ($storage->load($bundle)->getSource()->getPluginId() !== 'image') {
          return FALSE;
        }
      }
    }
    return parent::isApplicable($field_definition);
  }

}
