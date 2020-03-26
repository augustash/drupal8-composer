<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;

/**
 * Provides a collection of eXo toolbar item plugins.
 */
class ExoToolbarItemCollection extends DefaultSingleLazyPluginCollection {

  /**
   * The eXo toolbar item this plugin collection belongs to.
   *
   * @var \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface
   */
  protected $exoToolbarItem;

  /**
   * Constructs a new ExoToolbarItemCollection.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The manager to be used for instantiating plugins.
   * @param string $instance_id
   *   The ID of the plugin instance.
   * @param array $configuration
   *   An array of configuration.
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $exo_toolbar_item
   *   The toolbar item.
   */
  public function __construct(PluginManagerInterface $manager, $instance_id, array $configuration, ExoToolbarItemInterface $exo_toolbar_item) {
    $this->exoToolbarItem = $exo_toolbar_item;
    parent::__construct($manager, $instance_id, $configuration);
  }

  /**
   * {@inheritdoc}
   */
  protected function initializePlugin($instance_id) {
    if (!$instance_id) {
      throw new PluginException("The toolbar item '{$this->exoToolbarItem->id()}' did not specify a plugin.");
    }

    try {
      parent::initializePlugin($instance_id);
      $this->set($instance_id, $this->manager->createInstance($instance_id, $this->configuration));
      // $this->pluginInstances[$instance_id]->setItem($this->exoToolbarItem);
    }
    catch (PluginException $e) {
      $module = $this->configuration['provider'];
      // Ignore blocks belonging to uninstalled modules, but re-throw valid
      // exceptions when the module is installed and the plugin is
      // misconfigured.
      if (!$module || \Drupal::moduleHandler()->moduleExists($module)) {
        throw $e;
      }
    }
  }

}
