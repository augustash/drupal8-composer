<?php

namespace Drupal\exo\Plugin\ExtraField\example;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo\Plugin\ExtraField\Display\ExoExtraFieldDisplayFormattedBase;

/**
 * Example Extra field with formatted output.
 *
 * @ExoExtraFieldDisplay(
 *   id = "formatted_field",
 *   label = @Translation("Data formatted as field with label"),
 *   bundles = {
 *     "node.article",
 *   }
 * )
 */
class ExoExtraFieldExampleFormattedField extends ExoExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Three items');
  }

  /**
   * {@inheritdoc}
   */
  public function getLabelDisplay() {
    return 'above';
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    return [
      ['#markup' => 'One'],
      ['#markup' => 'Two'],
      ['#markup' => 'Three'],
    ];
  }

}
