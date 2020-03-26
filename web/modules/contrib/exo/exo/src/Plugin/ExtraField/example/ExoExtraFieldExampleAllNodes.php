<?php

namespace Drupal\exo\Plugin\ExtraField\example;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo\Plugin\ExtraField\Display\ExoExtraFieldDisplayBase;

/**
 * Example Extra field Display.
 *
 * @ExoExtraFieldDisplay(
 *   id = "all_nodes",
 *   label = @Translation("For all nodes"),
 *   bundles = {
 *     "node.*"
 *   },
 *   weight = -30,
 *   visible = true
 * )
 */
class ExoExtraFieldExampleAllNodes extends ExoExtraFieldDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {

    $elements = ['#markup' => 'This is output from ExampleAllNodes'];

    return $elements;
  }

}
