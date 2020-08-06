<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A 'node' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "node",
 *   label = @Translation("Node"),
 *   context_definitions = {
 *     "entity" = @ContextDefinition("entity", label = @Translation("Entity"))
 *   }
 * )
 */
class Node extends EntityReferenceBase implements ContainerFactoryPluginInterface {

  use ExoComponentFieldDisplayFormTrait;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected $entityType = 'node';

  /**
   * Creates a Node instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'render' => $this->t('The rendered entity.'),
    ] + parent::propertyInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $entity = $this->getReferencedEntity($item, $contexts);
    $element = $this->entityTypeManager->getViewBuilder($this->getEntityType())->view($entity, $this->getViewMode());
    return [
      'render' => $element,
    ] + parent::viewValue($item, $delta, $contexts);
  }

}
