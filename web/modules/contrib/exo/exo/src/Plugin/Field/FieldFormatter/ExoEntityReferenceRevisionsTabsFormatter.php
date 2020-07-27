<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\VerticalTabs;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\field_group\Element\HorizontalTabs;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_entity_reference_revisions_entity_tabs",
 *   label = @Translation("Rendered entity as tabs"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   },
 *   provider = "field_group",
 * )
 */
class ExoEntityReferenceRevisionsTabsFormatter extends EntityReferenceRevisionsEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'direction' => 'horizontal',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $form['direction'] = [
      '#title' => $this->t('Direction'),
      '#type' => 'select',
      '#options' => [
        'vertical' => $this->t('Vertical'),
        'horizontal' => $this->t('Horizontal'),
      ],
      '#default_value' => $this->getSetting('direction'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Direction: @direction',
      ['@direction' => $this->getSetting('direction')]
    );
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $direction = $this->getSetting('direction');

    if (empty($elements)) {
      return $elements;
    }

    $element = [
      '#type' => $direction . '_tabs',
      '#theme_wrappers' => [$direction . '_tabs'],
      '#tree' => TRUE,
      '#parents' => ['tabs'],
      '#default_tab' => '',
    ];

    $form_state = new FormState();
    $complete_form = [];
    if ($direction == 'vertical') {
      $element = VerticalTabs::processVerticalTabs($element, $form_state, $complete_form);
    }
    else {
      $element = HorizontalTabs::processHorizontalTabs($element, $form_state, $complete_form);
    }

    // Make sure the group has 1 child. This is needed to succeed at
    // form_pre_render_vertical_tabs(). Skipping this would force us to move
    // all child groups to this array, making it an un-nestable.
    $element['group']['#groups']['tabs'] = [0 => []];
    $element['group']['#groups']['tabs']['#group_exists'] = TRUE;

    foreach ($elements as $delta => $entity_element) {
      $entity = $items->get($delta)->entity;
      $element[$delta] = [
        '#type' => 'details',
        '#title' => $entity->label(),
        '#group' => 'tabs',
        '#id' => 'tab-' . $delta,
        0 => $entity_element,
      ];
    }

    return [$element];
  }

}
