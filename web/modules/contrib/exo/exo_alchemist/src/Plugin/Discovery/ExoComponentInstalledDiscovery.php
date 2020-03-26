<?php

namespace Drupal\exo_alchemist\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\exo_alchemist\ExoComponentManager;

/**
 * Allows entities to provide components.
 *
 * If the value of a key (like title) in the definition is translatable then
 * the addTranslatableProperty() method can be used to mark it as such and also
 * to add translation context. Then
 * \Drupal\Core\StringTranslation\TranslatableMarkup will be used to translate
 * the string and also to mark it safe. Only strings written in the YAML files
 * should be marked as safe, strings coming from dynamic plugin definitions
 * potentially containing user input should not.
 */
class ExoComponentInstalledDiscovery implements DiscoveryInterface {

  use DiscoveryTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public $entityTypeManager;

  /**
   * YAML file discovery and parsing handler.
   *
   * @var \Drupal\Core\Discovery\YamlDiscovery
   */
  protected $discovery;

  /**
   * Contains an array of translatable properties passed along to t().
   *
   * @var array
   */
  protected $translatableProperties = [];

  /**
   * Constructs a ExoComponentInstalledDiscovery object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Set one of the YAML values as being translatable.
   *
   * @param string $value_key
   *   The key corresponding to the value in the YAML that contains a
   *   translatable string.
   * @param string $context_key
   *   (Optional) the translation context for the value specified by the
   *   $value_key.
   *
   * @return $this
   */
  public function addTranslatableProperty($value_key, $context_key = '') {
    $this->translatableProperties[$value_key] = $context_key;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = [];
    $entities = $this->entityTypeManager->getStorage(ExoComponentManager::ENTITY_BUNDLE_TYPE)->loadMultiple();
    foreach ($entities as $entity) {
      if ($definition = self::getEntityDefinition($entity)) {
        // Add TranslatableMarkup.
        foreach ($this->translatableProperties as $property => $context_key) {
          if (isset($definition[$property])) {
            $options = [];
            // Move the t() context from the definition to the translation
            // wrapper.
            if ($context_key && isset($definition[$context_key])) {
              $options['context'] = $definition[$context_key];
              unset($definition[$context_key]);
            }
            $definition[$property] = new TranslatableMarkup($definition[$property], [], $options);
          }
        }
        $definitions[$definition['id']] = $definition;
      }
    }
    return $definitions;
  }

  /**
   * Extract component definition from an entity.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The entity bundle to load the definition from.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinition
   *   The component definition.
   */
  public static function getEntityDefinition(ConfigEntityInterface $entity) {
    return $entity->getThirdPartySetting('exo_alchemist', 'exo_component_definition');
  }

}
