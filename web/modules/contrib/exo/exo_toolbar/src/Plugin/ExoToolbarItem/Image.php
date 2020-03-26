<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'image' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "image",
 *   admin_label = @Translation("Image"),
 *   category = @Translation("Common"),
 * )
 */
class Image extends Link {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label' => 'Image',
      'image' => '',
      'image_style' => 'exo_toolbar_regular',
      'image_position' => 'before',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function itemForm(array $form, FormStateInterface $form_state) {
    $form = parent::itemForm($form, $form_state);
    $form['icon']['#access'] = FALSE;
    $image = $this->configuration['image'];

    $form['image'] = [
      '#type' => 'exo_config_file',
      '#title' => !$image ? $this->t('Image') : $this->t('Replace Image'),
      '#default_value' => $image,
      '#extensions' => ['jpg', 'jpeg', 'png', 'gif'],
      '#required' => empty($image),
    ];

    $form['image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Image Style'),
      '#default_value' => $this->configuration['image_style'],
      '#options' => ['exo_toolbar_regular' => $this->t('Regular')],
    ];

    $form['image_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Image Position'),
      '#default_value' => $this->configuration['image_position'],
      '#options' => [
        'before' => $this->t('Before'),
        'after' => $this->t('After'),
      ],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function itemSubmit(array $form, FormStateInterface $form_state) {
    parent::itemSubmit($form, $form_state);
    $this->configuration['image'] = $form_state->getValue('image');
    $this->configuration['image_style'] = $form_state->getValue('image_style');
    $this->configuration['image_position'] = $form_state->getValue('image_position');
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    $element = parent::elementBuild();
    $element
      ->setImage($this->configuration['image'])
      ->setImageStyle($this->configuration['image_style'])
      ->setImagePosition($this->configuration['image_position']);
    return $element;
  }

}
