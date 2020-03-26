<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'text_align' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "text_align",
 *   label = @Translation("Text: Align"),
 * )
 */
class TextAlign extends ClassAttribute {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios';

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'left' => $this->icon('Left')->setIcon('regular-align-left'),
      'center' => $this->icon('Center')->setIcon('regular-align-center'),
      'right' => $this->icon('Right')->setIcon('regular-align-right'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['#exo_style'] = 'grid';
    return $form;
  }

}
