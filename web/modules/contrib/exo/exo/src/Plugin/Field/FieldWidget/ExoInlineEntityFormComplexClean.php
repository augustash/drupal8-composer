<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

/**
 * Complex inline widget.
 *
 * @FieldWidget(
 *   id = "exo_inline_entity_form_complex_clean",
 *   label = @Translation("Inline entity form - Complex & Clean"),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions",
 *   },
 *   multiple_values = true,
 *   provider = "inline_entity_form"
 * )
 */
class ExoInlineEntityFormComplexClean extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    if (!empty($element['form'])) {
      $element['entities']['#access'] = FALSE;
    }
    else {
      $open_form = FALSE;
      foreach (Element::children($element['entities']) as $key) {
        $row = &$element['entities'][$key];
        $row['actions']['ief_entity_edit']['#value'] = $this->t('Manage');
        if (!empty($row['form'])) {
          $open_form = TRUE;
        }
      }
      // When we have an open form, we want to simplify the display and remove
      // all unopened rows.
      if ($open_form) {
        foreach (Element::children($element['entities']) as $key) {
          $row = &$element['entities'][$key];
          if (empty($row['form'])) {
            $row['#access'] = FALSE;
            unset($element['entities'][$key]);
          }
        }
        $element['actions']['#access'] = FALSE;
      }
    }
    return $element;
  }

}
