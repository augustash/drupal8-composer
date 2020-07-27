<?php

namespace Drupal\exo_paragraphs\Plugin\Field\FieldWidget;

use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'entity_reference_revisions paragraphs' widget.
 *
 * @FieldWidget(
 *   id = "exo_paragraphs",
 *   label = @Translation("eXo Paragraphs"),
 *   description = @Translation("eXo paragraphs inline form widget."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ExoParagraphsWidget extends ParagraphsWidget {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'title' => t('Component'),
      'title_plural' => t('Components'),
      'edit_mode' => 'closed',
      'closed_mode' => 'summary',
      'autocollapse' => 'all',
      'add_mode' => 'modal',
      'form_display_mode' => 'default',
      'default_paragraph_type' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $default_settings = self::defaultSettings();

    $form['add_mode'] = [
      '#type' => 'value',
      '#value' => $default_settings['add_mode'],
    ];

    return $form;
  }

  /**
   * Returns the default paragraph type.
   *
   * @return string
   *   Label name for default paragraph type.
   */
  protected function getDefaultParagraphTypeLabelName() {
    if ($this->getDefaultParagraphTypeMachineName() !== NULL) {
      $allowed_types = $this->getAllowedTypes();
      return $allowed_types[$this->getDefaultParagraphTypeMachineName()]['label'];
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $element = parent::formMultipleElements($items, $form, $form_state);
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $element['#table_no_header'] = TRUE;
    if ($cardinality === 1) {
      $element['#cardinality_multiple'] = FALSE;
      if (!empty($element[0]['subform']['#entity_type'])) {
        $element['#prefix'] = $element['#prefix'] . '<div class="paragraph-cardinality-1 paragraph-has-subform">';
      }
      else {
        $element['#prefix'] = $element['#prefix'] . '<div class="paragraph-cardinality-1">';
      }
      $element['#suffix'] = '</div>' . $element['#suffix'];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\content_translation\Controller\ContentTranslationController::prepareTranslation()
   *   Uses a similar approach to populate a new translation.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $has_subform = !empty($element['subform']['#entity_type']);
    if ($has_subform) {
      $element['#wrapper_attributes']['class'][] = 'paragraph-has-subform';
    }
    if (isset($element['top']['type']['label']['#markup'])) {
      $field_name = $this->fieldDefinition->getName();
      $parents = $element['#field_parents'];
      $widget_state = static::getWidgetState($parents, $field_name, $form_state);

      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraphs_entity */
      $paragraphs_entity = NULL;
      if (isset($widget_state['paragraphs'][$delta]['entity'])) {
        $paragraphs_entity = $widget_state['paragraphs'][$delta]['entity'];
      }
      elseif (isset($items[$delta]->entity)) {
        $paragraphs_entity = $items[$delta]->entity;
      }

      $target_type = $this->getFieldSetting('target_type');
      $item_bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($target_type);
      if (isset($item_bundles[$paragraphs_entity->bundle()])) {
        if ($icon = exo_icon_entity_icon($paragraphs_entity)) {
          $element['top']['type']['label']['#markup'] = '<span class="paragraph-type-label' . ($has_subform ? ' active' : '') . '">' . $this->icon($item_bundles[$paragraphs_entity->bundle()]['label'])->setIcon($icon) . '</span>';
        }
      }
    }
    return $element;
  }

  /**
   * Builds dropdown button for adding new paragraph.
   *
   * @return array
   *   The form element array.
   */
  protected function buildButtonsAddMode() {
    $add_more_elements = parent::buildButtonsAddMode();

    if (count($this->getAccessibleOptions()) === 1) {
      $this->setSetting('add_mode', 'button');
      $add_more_elements = parent::buildButtonsAddMode();
      foreach ($add_more_elements as &$add_more_element) {
        if (isset($add_more_element['#type']) && $add_more_element['#type'] == 'submit') {
          $add_more_element['#type'] = 'exo_button';
          $add_more_element['#ajax']['event'] = 'click';
          $add_more_element['#as_button'] = TRUE;
          $add_more_element['#label'] = $this->icon($add_more_element['#value'])->setIcon('regular-plus-circle');
        }
      }
      return $add_more_elements;
    }

    $options = $this->getAccessibleOptions();
    $paragraph_types = ParagraphsType::loadMultiple(array_keys($options));
    ksort($paragraph_types);
    $weight = 0;
    foreach ($paragraph_types as $bundle => $paragraph_type) {
      $button_key = 'add_more_button_' . $bundle;
      $add_more_elements[$button_key]['#type'] = 'exo_button';
      $add_more_elements[$button_key]['#ajax']['event'] = 'click';
      $add_more_elements[$button_key]['#trigger_attributes']['data-exo-modal-close'] = '';
      $add_more_elements[$button_key]['#label'] = $this->icon($add_more_elements[$button_key]['#value'])->setIcon(exo_icon_entity_icon($paragraph_type));
      $add_more_elements[$button_key]['#weight'] = $weight;
      $weight++;
    }

    unset($add_more_elements['add_modal_form_area']);
    $add_more_elements['#type'] = 'exo_modal';

    return $add_more_elements;
  }

  /**
   * Builds an add paragraph button for opening of modal form.
   *
   * @param array $element
   *   Render element.
   */
  protected function buildModalAddForm(array &$element) {
    $element['#use_close'] = FALSE;
    $element['#attached']['library'][] = 'exo_paragraphs/theme';
    $element['#trigger_text'] = $this->t('Add @title', [
      '@title' => $this->getSetting('title'),
    ]);
    $element['#field_suffix'] = $this->t('to %type', [
      '%type' => $this->fieldDefinition->getLabel(),
    ]);
    $element['#trigger_icon'] = 'regular-plus-circle';
    $element['#trigger_as_button'] = TRUE;

    $element['#modal_settings']['modal'] = [
      'theme' => 'default',
      'theme_content' => 'default',
      'title' => $this->t('Add @title', [
        '@title' => $this->getSetting('title'),
      ]),
      'subtitle' => $this->t('to %type', [
        '%type' => $this->fieldDefinition->getLabel(),
      ]),
      'padding' => '0',
      'top' => '0',
      'width' => '100%',
      'radius' => '0',
      'transitionIn' => 'fadeInDown',
      'transitionOut' => 'fadeOutUp',
    ];
    $element['#modal_attributes']['class'][] = 'exo-paragraphs-widget';
  }

}
