<?php

namespace Drupal\exo_entity_embed\Plugin\EmbedType;

use Drupal\entity_embed\Plugin\EmbedType\Entity;
use Drupal\Core\Form\FormStateInterface;

/**
 * Entity embed type.
 *
 * @EmbedType(
 *   id = "exo_entity",
 *   label = @Translation("eXo Entity")
 * )
 */
class ExoEntity extends Entity {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'icon' => 'regular-image',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['icon'] = [
      '#type' => 'exo_icon',
      '#title' => $this->t('Icon'),
      '#default_value' => $this->getConfigurationValue('icon'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    return '';
  }

}
