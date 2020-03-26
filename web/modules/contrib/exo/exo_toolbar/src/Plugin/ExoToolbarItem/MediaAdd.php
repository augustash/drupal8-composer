<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemConfigurableEntityBase;

/**
 * Plugin implementation of the 'media_add' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "media_add",
 *   admin_label = @Translation("Media Add"),
 *   category = @Translation("Entity"),
 *   provider = "media",
 * )
 */
class MediaAdd extends ExoToolbarItemConfigurableEntityBase {

  /**
   * The admin permission to check for access.
   *
   * @var string
   */
  protected $adminPermission = 'administer media types';

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType = 'media';

  /**
   * The entity type bundle.
   *
   * @var string
   */
  protected $entityTypeBundle = 'media_type';

  /**
   * The entity create route name.
   *
   * @var string
   */
  protected $entityCreateRoute = 'entity.media.add_form';

  /**
   * {@inheritdoc}
   */
  public function baseConfigurationDefaults() {
    return [
      'title' => $this->t('Media'),
    ] + parent::baseConfigurationDefaults();
  }

}
