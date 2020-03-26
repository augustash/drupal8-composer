<?php

namespace Drupal\exo_config\EventSubscriber;

use Drupal\config_readonly\ReadOnlyFormEvent;
use Drupal\config_readonly\EventSubscriber\ReadOnlyFormSubscriber;

/**
 * Check if the given form should be read-only.
 */
class ExoConfigReadOnlyFormSubscriber extends ReadOnlyFormSubscriber {

  /**
   * {@inheritdoc}
   */
  public function onFormAlter(ReadOnlyFormEvent $event) {
    if (exo_config_lock()) {
      parent::onFormAlter($event);
    }
  }

}
