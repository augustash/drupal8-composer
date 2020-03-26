<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\exo_toolbar\ExoToolbarElement;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;

/**
 * Plugin implementation of the 'create' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "create",
 *   admin_label = @Translation("Create new item"),
 *   category = @Translation("Common"),
 *   no_sort = TRUE,
 *   no_admin = TRUE,
 *   no_ui = TRUE,
 * )
 */
class Create extends ExoToolbarItemBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label' => 'Create',
      'toolbar' => NULL,
      'region' => NULL,
      'section' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    $url = Url::fromRoute('entity.exo_toolbar.library', [
      'exo_toolbar' => $this->configuration['toolbar'],
      'exo_toolbar_region' => $this->configuration['region'],
    ], [
      'query' => [
        'section' => $this->configuration['section'],
      ],
    ]);
    return ExoToolbarElement::create([
      'title' => $this->t('Create'),
      'icon' => 'regular-plus-circle',
      'attributes' => [
        'class' => ['exo-ajax'],
        'data-dialog-type' => 'exo_modal',
        'data-dialog-options' => Json::encode([
          'width' => 700,
        ]),
      ],
    ])
      ->setAsLink($url)
      ->setMarkOnly()
      ->addLibrary('exo/ajax');
  }

}
