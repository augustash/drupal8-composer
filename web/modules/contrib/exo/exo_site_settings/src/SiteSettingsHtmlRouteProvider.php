<?php

namespace Drupal\exo_site_settings;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for config pages.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class SiteSettingsHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    // If the entity type does not provide an admin permission, there is no way
    // to control access, so we cannot provide a route in a sensible way.
    if ($entity_type->hasLinkTemplate('collection') && $entity_type->hasListBuilderClass() && ($admin_permission = $entity_type->getAdminPermission())) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $label */
      $label = $entity_type->getCollectionLabel();

      $route = new Route($entity_type->getLinkTemplate('collection'));
      $route
        ->addDefaults([
          '_controller' => '\Drupal\exo_site_settings\Controller\SiteSettingsController::collection',
          '_title' => $label->getUntranslatedString(),
          '_title_arguments' => $label->getArguments(),
          '_title_context' => $label->getOption('context'),
        ]);
      $permissions = ['administer config pages'];
      foreach ($this->entityTypeManager->getStorage('exo_site_settings_type')->loadMultiple() as $site_settings) {
        $permissions[] = 'edit ' . $site_settings->id() . ' config page';
      }
      $route->setRequirement('_permission', implode('+', $permissions));

      return $route;
    }
  }

}
