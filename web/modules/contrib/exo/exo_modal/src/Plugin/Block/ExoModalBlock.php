<?php

namespace Drupal\exo_modal\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_modal\Plugin\ExoModalBlockBase;

/**
 * Provides a block to display a view within a modal.
 *
 * @Block(
 *   id = "exo_modal",
 *   admin_label = @Translation("eXo Modal"),
 *   provider = "exo_modal"
 * )
 */
class ExoModalBlock extends ExoModalBlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'block' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['block'] = [
      '#type' => 'details',
      '#title' => $this->t('Blocks (Content)'),
      '#open' => TRUE,
    ];

    $form['block']['block'] = $this->blocksForm('content', $form_state, $this->configuration['block'], FALSE);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['block'] = $form_state->getValue(['block', 'block']);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildModalContent() {
    $build = [];
    $count = 0;
    foreach ($this->configuration['block'] as $block_id => $settings) {
      $build[$block_id] = $this->buildBlock($block_id);
      $build[$block_id]['#weight'] = $count;
      $count++;
    }
    return $build;
  }

}
