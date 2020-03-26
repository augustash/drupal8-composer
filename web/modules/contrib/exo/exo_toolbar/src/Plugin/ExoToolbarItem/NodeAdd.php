<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemConfigurableEntityBase;

/**
 * Plugin implementation of the 'node_add' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "node_add",
 *   admin_label = @Translation("Node Add"),
 *   category = @Translation("Entity"),
 * )
 */
class NodeAdd extends ExoToolbarItemConfigurableEntityBase {

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType = 'node';

  /**
   * The entity type bundle.
   *
   * @var string
   */
  protected $entityTypeBundle = 'node_type';

  /**
   * The admin permission to check for access.
   *
   * @var string
   */
  protected $adminPermission = 'administer content types';

  /**
   * The entity create route name.
   *
   * @var string
   */
  protected $entityCreateRoute = 'node.add';

  /**
   * {@inheritdoc}
   */
  public function baseConfigurationDefaults() {
    return [
      'title' => $this->t('Content'),
    ] + parent::baseConfigurationDefaults();
  }

}
