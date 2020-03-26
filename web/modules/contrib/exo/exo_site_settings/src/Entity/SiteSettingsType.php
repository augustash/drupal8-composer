<?php

namespace Drupal\exo_site_settings\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines the config page type entity.
 *
 * @ConfigEntityType(
 *   id = "exo_site_settings_type",
 *   label = @Translation("Site Setting Type"),
 *   label_plural = @Translation("Site Setting Types"),
 *   label_collection = @Translation("Site Setting Types"),
 *   handlers = {
 *     "storage" = "Drupal\exo_site_settings\SiteSettingsTypeStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\exo_site_settings\SiteSettingsTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\exo_site_settings\Form\SiteSettingsTypeForm",
 *       "edit" = "Drupal\exo_site_settings\Form\SiteSettingsTypeForm",
 *       "delete" = "Drupal\exo_site_settings\Form\SiteSettingsTypeDeleteForm"
 *     },
 *     "access" = "Drupal\exo_site_settings\SiteSettingsTypeAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\exo_site_settings\SiteSettingsTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "exo_site_settings_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "exo_site_settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "add-form" = "/admin/settings/manage/add",
 *     "edit-form" = "/admin/settings/manage/{exo_site_settings_type}",
 *     "delete-form" = "/admin/settings/manage/{exo_site_settings_type}/delete",
 *     "collection" = "/admin/settings/manage",
 *     "page-form" = "/admin/settings/{exo_site_settings_type}",
 *     "aggregate-form" = "/admin/settings/general",
 *   }
 * )
 */
class SiteSettingsType extends ConfigEntityBundleBase implements SiteSettingsTypeInterface {

  /**
   * The config page type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The config page type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The weight of this toolbar in relation to other toolbars.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * Aggregate this form into a unified form.
   *
   * @var int
   */
  protected $aggregate = FALSE;

  /**
   * Provides the list of site settings types.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   Storage interface.
   * @param array $entities
   *   Array of entities.
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    foreach ($entities as $entity) {
      $exo_site_settings = \Drupal::service('entity_type.manager')->getStorage('exo_site_settings')->loadByType($entity->id());
      if ($exo_site_settings) {
        $exo_site_settings->delete();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setAggregate($aggregate = TRUE) {
    $this->aggregate = $aggregate;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isAggregate() {
    return !empty($this->aggregate);
  }

  /**
   * Sorts by weight.
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    return $a->getWeight() - $b->getWeight();
  }

}
