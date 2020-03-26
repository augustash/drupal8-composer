<?php

namespace Drupal\exo_alchemist\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\StorageTransformEvent;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Defines a base event listener implementation for config sync validation.
 */
class ExoComponentConfigTransform implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::STORAGE_TRANSFORM_IMPORT][] = ['onImportTransform'];
    return $events;
  }

  /**
   * The storage is transformed for importing.
   *
   * @param \Drupal\Core\Config\StorageTransformEvent $event
   *   The config storage transform event.
   */
  public function onConfigImporterValidate(StorageTransformEvent $event) {
    /** @var \Drupal\Core\Config\StorageInterface $storage */
    $storage = $event->getStorage();
    $site = $storage->read('core.entity_view_display.node.page.default');
    // foreach ($storage->listAll())
  }

}
