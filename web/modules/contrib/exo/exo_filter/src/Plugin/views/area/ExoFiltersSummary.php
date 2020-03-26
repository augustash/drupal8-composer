<?php

namespace Drupal\exo_filter\Plugin\views\area;

use Drupal\views\Plugin\views\area\AreaPluginBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;

/**
 * Provides an area for FSOUT filter summary.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("exo_filter_summary")
 */
class ExoFiltersSummary extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // Set the default to TRUE so it shows on empty pages by default.
    $options['empty']['default'] = TRUE;
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $output = [];

    foreach ($this->view->filter as $id => $filter) {
      if (!$filter->isExposed() || empty($filter->value)) {
        continue;
      }
      $info = $filter->exposedInfo();
      $values = is_array($filter->value) ? $filter->value : [$filter->value];
      $label = $info['label'];
      $alias = $info['value'];
      $multiple = !empty($filter->options['expose']['multiple']);
      $form = ['#pre_render' => []];
      $form_state = new FormState();
      $form_state->set('exposed', TRUE);
      $filter->buildExposedForm($form, $form_state);
      if (!isset($form[$alias]['#type'])) {
        continue;
      }
      $field_type = $form[$alias]['#type'];
      $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $form[$alias]['#type']))) . 'Value';
      if (!method_exists($this, $method)) {

      }
      $filter_values = [];
      foreach ($values as $key => $value) {
        $filter_values[$key] = $this->{$method}($form[$alias], $value);
      }
      if (empty($filter_values)) {
        continue;
      }
      $output[$id] = [
        '#type' => 'item',
        '#title' => $label,
        '#wrapper_attributes' => [
          'class' => ['exo-filter-summary-item'],
          'data-exo-filter-summary-field' => $alias . ($multiple ? '[]' : ''),
        ],
        '#attached' => ['library' => ['exo_filter/summary']],
        'values' => [
          '#theme' => 'item_list',
          '#items' => [],
        ],
      ];
      foreach ($filter_values as $filter_key => $filter_value) {
        $output[$id]['values']['#items'][$filter_key] = [
          '#type' => 'link',
          '#title' => $filter_value,
          '#url' => Url::fromRoute('<none>'),
          '#attributes' => [
            'class' => ['exo-filter-summary-value'],
            'data-exo-filter-summary-value' => $values[$filter_key],
          ],
        ];
      }
    }
    return $output;
  }

  /**
   * Extract and return select value.
   *
   * @param array $element
   *   The form element array.
   * @param string $value
   *   The raw value of the field.
   *
   * @return string|array|void
   *   Return string or array of values.
   */
  protected function getSelectValue(array $element, $value) {
    return isset($element['#options'][$value]) ? $element['#options'][$value] : NULL;
  }

  /**
   * Extract and return textfield value.
   *
   * @param array $element
   *   The form element array.
   * @param string $value
   *   The raw value of the field.
   *
   * @return string|array|void
   *   Return string or array of values.
   */
  protected function getTextfieldValue(array $element, $value) {
    return $value;
  }

}
