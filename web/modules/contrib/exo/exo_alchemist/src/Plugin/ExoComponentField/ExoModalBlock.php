<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Url;
use Drupal\exo_alchemist\ExoComponentValue;

/**
 * A 'exo_modal_block' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "exo_modal_block",
 *   label = @Translation("eXo Modal: Block"),
 * )
 */
class ExoModalBlock extends ExoModalBase {

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    parent::validateValue($value);
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('block_id')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [block_id] be set.', $field->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getModalUrl(FieldItemInterface $item, $delta, array $contexts) {
    $field = $this->getFieldDefinition();
    return Url::fromRoute('exo_modal.api.block.view', [
      'block' => $field->getAdditionalValue('block_id'),
    ]);
  }

}
