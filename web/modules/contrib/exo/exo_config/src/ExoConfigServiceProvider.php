<?php

namespace Drupal\exo_config;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class ExoConfigServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('config_readonly_form_subscriber');
    $definition->setClass('Drupal\exo_config\EventSubscriber\ExoConfigReadOnlyFormSubscriber');

    if ($container->getParameter('kernel.environment') !== 'install') {
      $definition = $container->getDefinition('config.storage');
      $definition->setClass('Drupal\exo_config\Config\ExoConfigReadonlyStorage');
    }
  }

}
