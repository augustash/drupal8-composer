<?php

namespace Drupal\exo_modal\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;

/**
 * Class ExoModalSettingsForm.
 */
class ExoModalSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_modal.settings')
    );
  }

}
