<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface;
use Drupal\exo_toolbar\ExoToolbarElement;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\SubformState;

/**
 * Plugin implementation of the 'region' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "region",
 *   admin_label = @Translation("Region"),
 *   category = @Translation("Common"),
 * )
 */
class Region extends ExoToolbarItemBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'region' => '',
      'ajax' => FALSE,
      'event' => 'click',
      'icon' => 'regular-bars',
      'region' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function itemForm(array $form, FormStateInterface $form_state) {
    $form = parent::itemForm($form, $form_state);
    $form['icon']['#required'] = TRUE;

    $form['ajax'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use AJAX'),
      '#description' => $this->t('When checked, the region will not be loaded until the user clicks this item.'),
      '#default_value' => $this->configuration['ajax'],
    ];

    $form['event'] = [
      '#type' => 'select',
      '#title' => $this->t('Region event'),
      '#options' => ['click' => $this->t('Click'), 'hover' => $this->t('Hover')],
      '#default_value' => $this->configuration['event'],
      '#required' => TRUE,
      '#states' => [
        'invisible' => [
          ':input[name="settings[ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $item = $form_state->get('entity');
    $region = $item->getRegion();
    $region->setConfiguration($this->configuration['region']);
    $form['region'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Region Settings'),
    ];
    $subform_state = SubformState::createForSubform($form['region'], $form, $form_state);
    $form['region'] += $region->buildConfigurationForm($form['region'], $subform_state);
    unset($form['region']['expanded']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function itemSubmit(array $form, FormStateInterface $form_state) {
    parent::itemSubmit($form, $form_state);
    $this->configuration['ajax'] = $form_state->getValue('ajax');
    $this->configuration['event'] = $form_state->getValue('event');
    $this->configuration['region'] = $form_state->getValue('region');
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(ExoToolbarItemInterface $item) {
    $operations = parent::getOperations($item);
    if ($item->hasLinkTemplate('edit-form')) {
      $operations['items'] = [
        'title' => t('Items'),
        'weight' => 10,
        'url' => Url::fromRoute('entity.exo_toolbar.item.items', ['exo_toolbar_item' => $item->id()]),
        'attributes' => [
          'class' => ['exo-ajax'],
        ],
      ];
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    $url = Url::fromRoute('exo_toolbar.api.item.region', ['exo_toolbar_item' => $this->getItem()->id()]);
    return ExoToolbarElement::create([
      'title' => $this->label(),
      'icon' => $this->getIcon(),
      'attributes' => [
        'class' => ['exo-toolbar-region-toggle', 'exo-ajax'],
      ],
    ])
      ->addLibrary('exo_toolbar/toggle')
      ->addJsSettings([
        'toggle' => [
          'ajax' => $this->configuration['ajax'],
          'region' => 'item:' . $this->getItem()->id(),
          'event' => $this->configuration['event'],
        ],
      ])
      ->setAsLink();
  }

  /**
   * {@inheritdoc}
   */
  public function alterRegionJsSettings(array &$settings, ExoToolbarRegionPluginInterface $region) {
  }

}
