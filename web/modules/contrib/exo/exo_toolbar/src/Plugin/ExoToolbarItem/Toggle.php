<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\ExoToolbarElement;
use Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface;

/**
 * Plugin implementation of the 'toggle' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "toggle",
 *   admin_label = @Translation("Toggle"),
 *   category = @Translation("Common"),
 * )
 */
class Toggle extends ExoToolbarItemBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'region' => '',
      'event' => 'click',
      'icon' => 'regular-bars',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function itemForm(array $form, FormStateInterface $form_state) {
    $form = parent::itemForm($form, $form_state);
    $form['icon']['#required'] = TRUE;
    $entity = $form_state->get('entity');

    $regions = $entity->getToolbar()->getRegionLabels();
    unset($regions[$entity->getRegionId()]);

    $form['region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region to toggle'),
      '#options' => $regions,
      '#default_value' => $this->configuration['region'],
      '#required' => TRUE,
    ];

    $form['event'] = [
      '#type' => 'select',
      '#title' => $this->t('Toggle event'),
      '#options' => ['click' => $this->t('Click'), 'hover' => $this->t('Hover')],
      '#default_value' => $this->configuration['event'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function itemSubmit(array $form, FormStateInterface $form_state) {
    parent::itemSubmit($form, $form_state);
    $this->configuration['region'] = $form_state->getValue('region');
    $this->configuration['event'] = $form_state->getValue('event');
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    return ExoToolbarElement::create([
      'title' => $this->label(),
      'icon' => $this->getIcon(),
      'attributes' => [
        'class' => ['exo-toolbar-toggle'],
      ],
    ])
      ->setHorizontalMarkOnly()
      ->addLibrary('exo_toolbar/toggle')
      ->addJsSettings([
        'toggle' => [
          'region' => $this->configuration['region'],
          'event' => $this->configuration['event'],
        ],
      ])
      ->setAsLink();
  }

  /**
   * {@inheritdoc}
   */
  public function alterRegionJsSettings(array &$settings, ExoToolbarRegionPluginInterface $region) {
    if ($region->getPluginId() == $this->configuration['region']) {
      $settings['toggleable'] = TRUE;
      $settings['hidden'] = TRUE;
    }
  }

}
