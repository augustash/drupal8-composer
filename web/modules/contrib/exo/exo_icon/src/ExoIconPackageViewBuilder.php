<?php

namespace Drupal\exo_icon;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Base class for entity view builders.
 *
 * @ingroup entity_api
 */
class ExoIconPackageViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = parent::getBuildDefaults($entity, $view_mode);
    return $build;
  }

}
