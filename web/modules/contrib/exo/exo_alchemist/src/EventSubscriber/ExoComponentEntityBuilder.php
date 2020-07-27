<?php

namespace Drupal\exo_alchemist\EventSubscriber;

use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A subscriber storing in state any components that are being updated.
 */
class ExoComponentEntityBuilder implements EventSubscriberInterface {

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * Store in state any components that are being updated.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onImport(ConfigImporterEvent $event) {
    $changed = array_merge($event->getChangelist('create'), $event->getChangelist('update'));
    foreach ($changed as $config_name) {
      if (substr($config_name, 0, 23) === 'block_content.type.exo_') {
        $rebuild = $this->state->get('exo_alchemist.component_rebuild', []);
        $rebuild[substr($config_name, 19)] = TRUE;
        // Drupal does not fire an event when a config import has completed. We
        // set a flag so we can then use this to know if we need to check if
        // any component default entities need to be built.
        $this->state->set('exo_alchemist.component_rebuild', $rebuild);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT][] = ['onImport'];
    return $events;
  }

}
