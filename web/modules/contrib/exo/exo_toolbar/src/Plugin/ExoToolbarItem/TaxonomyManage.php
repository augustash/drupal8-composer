<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemConfigurableEntityBase;

/**
 * Plugin implementation of the 'taxonomy_manage' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "taxonomy_manage",
 *   admin_label = @Translation("Taxonomy Manage"),
 *   category = @Translation("Entity"),
 * )
 */
class TaxonomyManage extends ExoToolbarItemConfigurableEntityBase {

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType = 'taxonomy_term';

  /**
   * The entity type bundle.
   *
   * @var string
   */
  protected $entityTypeBundle = 'taxonomy_vocabulary';

  /**
   * The admin permission to check for access.
   *
   * @var string
   */
  protected $adminPermission = 'administer taxonomy';

  /**
   * The entity create route name.
   *
   * @var string
   */
  protected $entityCreateRoute = 'entity.taxonomy_vocabulary.overview_form';

  /**
   * {@inheritdoc}
   */
  public function baseConfigurationDefaults() {
    return [
      'title' => $this->t('Taxonomy'),
    ] + parent::baseConfigurationDefaults();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'icon' => 'regular-tags',
    ] + parent::defaultConfiguration();
  }

}
