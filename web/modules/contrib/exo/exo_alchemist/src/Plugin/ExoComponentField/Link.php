<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\link\LinkItemInterface;

/**
 * A 'link' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "link",
 *   label = @Translation("Link"),
 * )
 */
class Link extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'url' => $this->t('The absolute url of the link.'),
      'title' => $this->t('The title of the link.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    if (!$value->has('uri')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [default.uri] be set.', $value->getDefinition()->getType()));
    }
    if (!$value->has('title')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [default.title] be set.', $value->getDefinition()->getType()));
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
        'title' => DRUPAL_REQUIRED,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    if (\Drupal::moduleHandler()->moduleExists('exo_link')) {
      return [
        'type' => 'exo_link',
        'settings' => [
          'icon' => FALSE,
          'target' => TRUE,
          'linkit' => \Drupal::moduleHandler()->moduleExists('linkit_widget'),
          'linkit_profile' => 'exo_component',
        ],
      ];
    }
    if (\Drupal::moduleHandler()->moduleExists('linkit_widget')) {
      return [
        'type' => 'linkit_widget',
        'settings' => [
          'linkit_profile' => 'exo_component',
        ],
      ];
    }
    return [
      'type' => 'link_default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'title' => $this->t('Placeholder for @label title', [
        '@label' => strtolower($this->getFieldDefinition()->getLabel()),
      ]),
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
