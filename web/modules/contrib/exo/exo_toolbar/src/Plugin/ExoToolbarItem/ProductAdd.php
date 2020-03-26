<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemConfigurableEntityBase;

/**
 * Plugin implementation of the 'media_add' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "product_add",
 *   admin_label = @Translation("Product Add"),
 *   category = @Translation("Entity"),
 *   provider = "commerce_product",
 * )
 */
class ProductAdd extends ExoToolbarItemConfigurableEntityBase {

  /**
   * The admin permission to check for access.
   *
   * @var string
   */
  protected $adminPermission = 'administer commerce_product_type';

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType = 'commerce_product';

  /**
   * The entity type bundle.
   *
   * @var string
   */
  protected $entityTypeBundle = 'commerce_product_type';

  /**
   * The entity create route name.
   *
   * @var string
   */
  protected $entityCreateRoute = 'entity.commerce_product.add_form';

  /**
   * {@inheritdoc}
   */
  public function baseConfigurationDefaults() {
    return [
      'title' => $this->t('Product'),
    ] + parent::baseConfigurationDefaults();
  }

}
