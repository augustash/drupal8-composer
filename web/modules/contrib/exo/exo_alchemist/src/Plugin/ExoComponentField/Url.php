<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\link\LinkItemInterface;

/**
 * A 'url' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "url",
 *   label = @Translation("Url"),
 *   properties = {
 *     "url" = @Translation("The absolute url."),
 *   },
 *   widget = {
 *     "type" = "link_default",
 *   },
 * )
 */
class Url extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    if ($value->has('value')) {
      $value->set('uri', $value->get('value'));
      $value->unset('value');
    }
    if (!$value->has('uri')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [default.uri] be set.', $value->getDefinition()->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    return [
      'type' => 'link',
      'settings' => [
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_DISABLED,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'uri' => 'internal:/',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    /** @var \Drupal\link\LinkItemInterface $item */
    $value = $item->getValue();
    $value['url'] = $item->getUrl()->setAbsolute()->toString();
    return $value;
  }

}
