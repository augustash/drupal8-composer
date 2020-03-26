<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyAsClassInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyBase;

/**
 * A 'class attribute' adapter for exo components.
 */
abstract class ClassAttribute extends ExoComponentPropertyBase implements ExoComponentPropertyAsClassInterface {

  /**
   * The element type.
   *
   * @var string
   */
  protected $type = 'exo_radios';

  /**
   * An array of options.
   *
   * Key should be class key and value should be class label.
   *
   * @var array
   */
  protected $options;

  /**
   * Get element type.
   *
   * @return string
   *   A valid element type.
   */
  protected function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    return key($this->getOptions());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedOptions() {
    $formatted = [];
    foreach ($this->getOptions() as $key => $label) {
      $formatted[$key] = Html::getClass($this->getHtmlClassName($this->getPropertyDefinition()->getName(), $key));
    }
    return $formatted;
  }

  /**
   * The class name.
   *
   * @param string $name
   *   The property name.
   * @param string $value
   *   The property value.
   *
   * @return string
   *   The class to use.
   */
  protected function getHtmlClassName($name, $value) {
    return 'exo-modifier--' . $name . '-' . $value;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $required = $this->getPropertyDefinition()->isRequired();
    $form['#type'] = $this->getType();
    $form['#required'] = $required;
    if ($options = $this->getOptions()) {
      $form['#options'] = $options;
      if ($required) {
        unset($form['#options']['_none']);
      }
    }
    $form['#default_value'] = $this->getValue();
    switch ($form['#type']) {
      case 'exo_radios':
        $form['#exo_style'] = 'grid';
        break;

      case 'exo_radios_slider':
        $form['#pips'] = TRUE;
        break;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function asAttributeArray() {
    $attributes = [];
    $formatted_options = $this->getFormattedOptions();
    if (!empty($formatted_options[$this->getValue()])) {
      $attributes['class'][] = $formatted_options[$this->getValue()];
    }
    return $attributes;
  }

}
