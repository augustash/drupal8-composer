<?php

namespace Drupal\exo_toolbar\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;

/**
 * Class ExoToolbarSettingsForm.
 */
class ExoToolbarSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_toolbar.settings')
    );
  }

}
