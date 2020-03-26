<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Provides a collection of eXo toolbar dialog type plugins.
 */
class ExoToolbarDialogTypeCollection extends DefaultSingleLazyPluginCollection {

  /**
   * The eXo toolbar item ID this plugin collection belongs to.
   *
   * @var string
   */
  protected $exoToolbarDialogTypeId;

  /**
   * Constructs a new ExoToolbarDialogTypeCollection.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The manager to be used for instantiating plugins.
   * @param string $instance_id
   *   The ID of the plugin instance.
   * @param array $configuration
   *   An array of configuration.
   * @param string $exo_toolbar_item_id
   *   The unique ID of the eXo toolbar item entity using this plugin.
   */
  public function __construct(PluginManagerInterface $manager, $instance_id, array $configuration, $exo_toolbar_item_id) {
    parent::__construct($manager, $instance_id, $configuration);
    $this->exoToolbarDialogTypeId = $exo_toolbar_item_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function initializePlugin($instance_id) {
    if (!$instance_id) {
      throw new PluginException("The toolbar dialog type '{$this->exoToolbarDialogTypeId}' did not specify a plugin.");
    }

    try {
      parent::initializePlugin($instance_id);
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
