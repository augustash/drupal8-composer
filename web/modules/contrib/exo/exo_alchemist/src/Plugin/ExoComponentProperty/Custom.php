<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;

/**
 * A 'custom' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "custom",
 *   label = @Translation("Custom"),
 * )
 */
class Custom extends ClassAttribute {

  /**
   * Get element type.
   *
   * @return string
   *   A valid element type.
   */
  protected function getType() {
    return $this->asBoolean() ? 'checkbox' : $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    return $this->asBoolean() ? 0 : parent::getDefault();
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    if (!isset($this->options)) {
      $this->options = [];
      if ($definition = $this->getPropertyDefinition()) {
        $this->options = $definition->getAdditionalValue('options');
        if (count($this->options) === 1) {
          $this->options = [
            '_none' => 'None',
            1 => reset($this->options),
          ];
        }
      }
    }
    return parent::getOptions();
  }

  /**
   * Check if custom element should be treated as a boolean.
   *
   * @return bool
   *   TRUE if this is a boolean.
   */
  protected function asBoolean() {
    $options = $this->getOptions();
    unset($options['_none']);
    return count($options) === 1;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHtmlClassName($name, $value) {
    return $this->asBoolean() ? 'exo-modifier--' . $name : parent::getHtmlClassName($name, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    if ($this->asBoolean()) {
      unset($form['#options']);
    }
    return $form;
  }

}
