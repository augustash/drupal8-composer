<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'position' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "position_horizontal",
 *   label = @Translation("Position: Horizontal"),
 * )
 */
class PositionHorizontal extends ClassAttribute {
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
      'left' => $this->icon('Left')->setIcon('regular-arrow-to-left'),
      'right' => $this->icon('Right')->setIcon('regular-arrow-to-right'),
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
