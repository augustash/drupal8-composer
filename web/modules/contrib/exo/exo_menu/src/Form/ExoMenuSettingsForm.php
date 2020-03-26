<?php

namespace Drupal\exo_menu\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;

/**
 * Class ExoMenuSettingsForm.
 */
class ExoMenuSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_menu.settings')
    );
  }

}
