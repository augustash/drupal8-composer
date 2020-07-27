<?php

namespace Drupal\exo_alchemist;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\Definition\ExoComponentDefinitionEnhancement;

/**
 * Provides the eXo Component Enhancement plugin manager.
 */
class ExoComponentEnhancementManager extends DefaultPluginManager {

  use StringTranslationTrait;

  /**
   * Constructs a new ExoComponentEnhancementManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ExoComponentEnhancement', $namespaces, $module_handler, 'Drupal\exo_alchemist\Plugin\ExoComponentEnhancementInterface', 'Drupal\exo_alchemist\Annotation\ExoComponentEnhancement');
    $this->alterInfo('exo_alchemist_exo_component_enhancement_info');
    $this->setCacheBackend($cache_backend, 'exo_alchemist_exo_component_enhancement_plugins');
  }

  /**
   * Create a plugin instance given a enhancement definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinitionEnhancement $enhancement
   *   A field definition.
   *
   * @return \Drupal\exo_alchemist\Plugin\ExoComponentEnhancementInterface
   *   A enhancement plugin instance.
   */
  public function createEnhancementInstance(ExoComponentDefinitionEnhancement $enhancement) {
    $configuration = [
      'enhancementDefinition' => $enhancement,
    ];
    return $this->createInstance($enhancement->getType(), $configuration);
  }

  /**
   * Process component definition.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function processComponentDefinition(ExoComponentDefinition $definition) {
    if ($enhancements = $definition->getEnhancements()) {
      foreach ($enhancements as $enhancement) {
        if (!$this->hasDefinition($enhancement->getType())) {
          if (!$definition->isInstalled()) {
            throw new PluginException(sprintf('eXo Component Enhancement plugin type (%s) does not exist.', $enhancement->getType()));
          }
        }
      }
    }
  }

  /**
   * Get attribute info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function getAttributeInfo(ExoComponentDefinition $definition) {
    $info = [];
    return $info;
  }

  /**
   * Get property info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function getPropertyInfo(ExoComponentDefinition $definition) {
    $info = [];
    if ($enhancements = $definition->getEnhancements()) {
      foreach ($enhancements as $enhancement) {
        $instance = $this->createEnhancementInstance($enhancement);
        $enhancement_info = $instance->propertyInfo();
        $properties = [];
        foreach ($enhancement_info as $id => $property) {
          $properties['enhancement.' . $enhancement->getName() . '.' . $id] = $property;
        }
        $info['enhancement.' . $enhancement->getName()] = [
          'label' => $this->t('Enhancement: %label', ['%label' => $enhancement->getLabel()]),
          'properties' => $properties,
        ];
      }
    }
    return $info;
  }

  /**
   * View content entity for definition as values.
   *
   * Values are broken out this way so sequence and other nested fields can
   * access the raw values before they are turned into attributes.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param array $values
   *   The values array.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   */
  public function viewEntityValues(ExoComponentDefinition $definition, array &$values, ContentEntityInterface $entity, array $contexts) {
    if ($enhancements = $definition->getEnhancements()) {
      foreach ($enhancements as $enhancement) {
        $instance = $this->createEnhancementInstance($enhancement);
        $enhancement_values = $instance->view($contexts);
        foreach ($enhancement_values as $id => $value) {
          if (Element::property($id)) {
            if (isset($values[$id])) {
              $values[$id] = NestedArray::mergeDeep($values[$id], $value);
            }
          }
          else {
            $values['enhancement'][$enhancement->getName()][$id] = $value;
          }
        }
      }
    }
  }

}
