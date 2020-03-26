<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\CategorizingPluginManagerInterface;
use Drupal\Core\Plugin\Context\ContextAwarePluginManagerInterface;

/**
 * Defines the interface for eXo toolbar item plugin managers.
 */
interface ExoToolbarItemManagerInterface extends ContextAwarePluginManagerInterface, CategorizingPluginManagerInterface {

}
