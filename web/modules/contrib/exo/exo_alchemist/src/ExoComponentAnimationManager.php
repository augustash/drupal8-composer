<?php

namespace Drupal\exo_alchemist;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;

/**
 * Provides the Component Property plugin manager.
 */
class ExoComponentAnimationManager {

  use StringTranslationTrait;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The animation generator.
   *
   * @var \Drupal\exo_aos\ExoAosGenerator
   */
  protected $animationGenerator;

  /**
   * Creates the ExoComponentAnimationManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Get attribute info.
   *
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   */
  public function getAttributeInfo(ExoComponentDefinition $definition) {
    $info = [];
    if ($this->moduleHandler->moduleExists('exo_aos')) {
      $info['general'] = [
        'label' => 'General',
        'properties' => [
          'offset: 120' => $this->t('Offset (number)'),
          'delay: 0' => $this->t('Delay (number)'),
          'duration: 400' => $this->t('Duration (number)'),
          'once: false' => $this->t('Once (boolean)'),
          'mirror: false' => $this->t('Mirror (boolean)'),
        ],
      ];
      $info['animations'] = [
        'label' => 'Animation',
      ];
      foreach ($this->animationGenerator()->getElementAnimations() as $key => $label) {
        $info['animations']['properties']['animation: ' . $key] = $label;
      }
      $info['anchors'] = [
        'label' => 'Anchor',
      ];
      foreach ($this->animationGenerator()->getElementAnchorPlacements() as $key => $label) {
        $info['anchors']['properties']['anchorPlacement: ' . $key] = $label;
      }
      $info['easing'] = [
        'label' => 'Easing',
      ];
      foreach ($this->animationGenerator()->getElementEasings() as $key => $label) {
        $info['easing']['properties']['easing: ' . $key] = $label;
      }
    }
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
    if ($this->moduleHandler->moduleExists('exo_aos')) {
      if ($animations = $definition->getAnimations()) {
        foreach ($animations as $animation) {
          if ($animation->getName() !== '_global') {
            $info[self::animationNameToKey($animation->getName())] = [
              'label' => $this->t('Animation: %label', ['%label' => $animation->getLabel()]),
              'properties' => [
                self::animationNameToKey($animation->getName()) => $this->t('Animation attributes.'),
              ],
            ];
          }
        }
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
    if ($this->moduleHandler->moduleExists('exo_aos')) {
      foreach ($definition->getAnimations() as $animation_name => $animation) {
        $aos = $this->animationGenerator()->generate($animation->toAnimationSettings());
        // Global animations.
        $values['#attached'] = NestedArray::mergeDeep($values['#attached'], $aos->getAttachments());
        if ($animation_name == '_global') {
          $values['#wrapper_attributes'] = NestedArray::mergeDeep($values['#wrapper_attributes'], $aos->getAttributes()->toArray());
          continue;
        }
        $key = self::animationNameToKey($animation_name);
        $values += [
          $key => new ExoComponentAttribute(),
        ];
        $values[$key] = new ExoComponentAttribute($aos->getAttributes()->toArray());
      }
    }
  }

  /**
   * Convert a animation name to its render array key.
   *
   * @param string $animation_name
   *   The animation name.
   */
  public static function animationNameToKey($animation_name) {
    return 'animation_' . $animation_name;
  }

  /**
   * Returns the module handler.
   *
   * @return \Drupal\exo_aos\ExoAosGenerator
   *   The animation generator.
   */
  protected function animationGenerator() {
    if (!$this->animationGenerator) {
      $this->animationGenerator = \Drupal::service('exo_aos.generator');
    }
    return $this->animationGenerator;
  }

}
