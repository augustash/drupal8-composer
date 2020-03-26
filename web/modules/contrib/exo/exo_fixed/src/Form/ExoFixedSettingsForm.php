<?php

namespace Drupal\exo_fixed\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;

/**
 * Class ExoFixedSettingsForm.
 */
class ExoFixedSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_fixed.settings')
    );
  }

}
