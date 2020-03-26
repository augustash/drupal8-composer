<?php

namespace Drupal\exo\Plugin\ExtraField\example;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo\Plugin\ExtraField\Display\ExoExtraFieldDisplayBase;

/**
 * Example Extra field Display.
 *
 * @ExoExtraFieldDisplay(
 *   id = "article_only",
 *   label = @Translation("Only for articles"),
 *   bundles = {
 *     "node.article",
 *   }
 * )
 */
class ExoExtraFieldExampleArticle extends ExoExtraFieldDisplayBase {

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity) {

    $elements = ['#markup' => 'This is output from ExampleArticle'];

    return $elements;
  }

}
