<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;

/**
 * A 'invert' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "invert",
 *   label = @Translation("Invert"),
 * )
 */
class Invert extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'checkbox';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '_none' => 'Normal',
    1 => 'Invert',
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
