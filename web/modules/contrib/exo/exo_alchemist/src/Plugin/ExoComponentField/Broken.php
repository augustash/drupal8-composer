<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;

/**
 * A 'view' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "broken",
 *   label = @Translation("Broken"),
 *   hidden = true,
 * )
 */
class Broken extends ExoComponentFieldComputedBase {

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    return [];
  }

}
