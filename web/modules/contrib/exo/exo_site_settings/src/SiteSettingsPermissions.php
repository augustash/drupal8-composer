<?php

namespace Drupal\exo_site_settings;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides eXo config page module permissions.
 */
class SiteSettingsPermissions implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * The exo_site_settings config storage.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $storage;

  /**
   * Constructs a new SiteSettingsPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->storage = $entity_type_manager->getStorage('exo_site_settings_type');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of config pages permissions.
   *
   * @return array
   *   An array of permissions.
   */
  public function permissions() {
    $permissions = [];
    $exo_site_settings = $this->storage->loadMultiple();
    foreach ($exo_site_settings as $site_settings) {
      $permissions['edit ' . $site_settings->id() . ' config page'] = [
        'title' => $this->t('Edit the %label config page', ['%label' => $site_settings->label()]),
      ];
    }
    return $permissions;
  }

}
