<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\ExoToolbarElement;

/**
 * Plugin implementation of the 'divider' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "divider",
 *   admin_label = @Translation("Divider"),
 *   category = @Translation("Common"),
 *   is_dependent = TRUE,
 * )
 */
class Divider extends ExoToolbarItemBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'title_show' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function itemForm(array $form, FormStateInterface $form_state) {

    $form['title_show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show title'),
      '#default_value' => $this->configuration['title_show'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function itemSubmit(array $form, FormStateInterface $form_state) {
    $this->configuration['title_show'] = $form_state->getValue('title_show');
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    return ExoToolbarElement::create([
      'title' => $this->configuration['title_show'] ? $this->label() : '',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function elementPreviewBuild() {
    return ExoToolbarElement::create([
      'title' => $this->configuration['title_show'] ? $this->label() : '[Hidden]',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function alterSectionElement(array &$element, array $context) {
    if (!empty($context['items']) && !$context['toolbar']->isAdminMode()) {
      // Dividers that follow another divider should be removed from the output
      // as they do not have any child items.
      $dividers = array_map(function ($item) {
        return $item->id();
      }, array_filter($context['items'], function ($item) {
        return $item->getPluginId() == 'divider';
      }));
      $ids = array_keys($element['items']);
      foreach (array_keys($ids) as $key) {
        if (isset($dividers[$ids[$key]]) && isset($ids[$key + 1])) {
          $el = &$element['items'][$ids[$key]];
          $next = &$element['items'][$ids[$key + 1]];
          if (isset($dividers[$ids[$key + 1]])) {
            $el['#access'] = FALSE;
          }
        }
      }
    }
  }

}
