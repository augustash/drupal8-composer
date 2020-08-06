<?php

namespace Drupal\exo_breadcrumbs\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;

/**
 * Class ExoBreadcrumbsSettingsForm.
 */
class ExoBreadcrumbsSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_breadcrumbs.settings')
    );
  }

}
