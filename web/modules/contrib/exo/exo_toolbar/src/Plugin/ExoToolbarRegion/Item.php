<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarRegion;

use Drupal\exo_toolbar\Plugin\ExoToolbarRegionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Plugin implementation of the 'bottom' eXo toolbar region.
 *
 * @ExoToolbarRegion(
 *   id = "item",
 *   label = @Translation("Item Region"),
 *   deriver = "Drupal\exo_toolbar\Plugin\Derivative\ExoToolbarRegion"
 * )
 */
class Item extends ExoToolbarRegionBase implements ContainerFactoryPluginInterface {

  /**
   * The eXo toolbar item storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $exoToolbarItemStorage;

  /**
   * The parent eXo toolbar item.
   *
   * @var \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface
   */
  protected $exoToolbarItem;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->exoToolbarItemStorage = $entity_type_manager->getStorage('exo_toolbar_item');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    return $this->getItem()->getRegion()->getSections();
  }

  /**
   * {@inheritdoc}
   */
  public function getAlignment() {
    return $this->getItem()->getRegion()->getAlignment();
  }

  /**
   * {@inheritdoc}
   */
  public function getEdge() {
    return $this->getItem()->getRegion()->getEdge();
  }

  /**
   * {@inheritdoc}
   */
  public function isExpandable() {
    return $this->getItem()->getRegion()->isExpandable();
  }

  /**
   * {@inheritdoc}
   */
  public function isRenderedOnInit() {
    return empty($this->getItem()->getSettings()['ajax']);
  }

  /**
   * {@inheritdoc}
   */
  public function isToggleable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isExpanded() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden() {
    return TRUE;
  }

  /**
   * Get the parent entity of this region.
   *
   * @return \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface
   *   The parent eXo toolbar item entity.
   */
  protected function getItem() {
    if (!isset($this->exoToolbarItem)) {
      $parts = explode(':', $this->getPluginId());
      $this->exoToolbarItem = $this->exoToolbarItemStorage->load($parts[1]);
    }
    return $this->exoToolbarItem;
  }

}
