<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionField;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\link\LinkItemInterface;

/**
 * A 'link' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "link",
 *   label = @Translation("Link"),
 *   properties = {
 *     "url" = @Translation("The absolute url of the link."),
 *     "title" = @Translation("The title of the link."),
 *   },
 *   widget = {
 *     "type" = "link_default",
 *   },
 * )
 */
class Link extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function componentProcessDefinition(ExoComponentDefinitionField $field) {
    parent::componentProcessDefinition($field);
    if (!$field->hasPreviewPropertyOnAll('uri')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [preview.uri] be set.', $field->getType()));
    }
    if (!$field->hasPreviewPropertyOnAll('title')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [preview.title] be set.', $field->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentStorage(ExoComponentDefinitionField $field) {
    return [
      'type' => 'link',
      'settings' => [
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_REQUIRED,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentViewValue(ExoComponentDefinitionField $field, FieldItemInterface $item, $delta, $is_layout_builder) {
    /** @var \Drupal\link\LinkItemInterface $item */
    $value = $item->getValue();
    $value['url'] = $item->getUrl()->setAbsolute()->toString();
    return $value;
  }

}
