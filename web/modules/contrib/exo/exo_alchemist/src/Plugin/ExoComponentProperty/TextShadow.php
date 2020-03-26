<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;

/**
 * A 'text_shadow' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "text_shadow",
 *   label = @Translation("Text Shadow"),
 * )
 */
class TextShadow extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'checkbox';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '_none' => 'Normal',
    1 => 'Shadow',
  ];

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    // Always default to non-inverted.
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHtmlClassName($name, $value) {
    return 'exo-modifier--' . $name;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    unset($form['#options']);
    return $form;
  }

}
