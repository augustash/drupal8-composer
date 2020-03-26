<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarDialogType;

use Drupal\exo_toolbar\Plugin\ExoToolbarDialogTypeBase;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'tip' eXo toolbar dialog type.
 *
 * @ExoToolbarDialogType(
 *   id = "tip",
 *   label = @Translation("Tooltip"),
 * )
 */
class Tip extends ExoToolbarDialogTypeBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'animate_in' => 'comingIn',
      'animate_out' => 'comingOut',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function dialogTypeForm(array $form, FormStateInterface $form_state) {
    $form['animate_in'] = [
      '#type' => 'select',
      '#title' => $this->t('Animate in'),
      '#options' => ['' => '- None -'] + exo_animate_in_options(),
      '#default_value' => $this->configuration['animate_in'],
    ];
    $form['animate_out'] = [
      '#type' => 'select',
      '#title' => $this->t('Animate out'),
      '#options' => ['' => '- None -'] + exo_animate_out_options(),
      '#default_value' => $this->configuration['animate_out'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function dialogBuild(ExoToolbarItemInterface $exo_toolbar_item, $arg = NULL) {
    $build = parent::dialogBuild($exo_toolbar_item, $arg);
    $build['#wrapper_attributes']['class'][] = 'exo-toolbar-item-aside-tip';
    return $build;
  }

}
