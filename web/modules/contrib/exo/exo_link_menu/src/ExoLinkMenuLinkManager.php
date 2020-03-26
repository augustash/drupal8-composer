<?php

namespace Drupal\exo_link_menu;

use Drupal\Core\Menu\MenuLinkManager;

/**
 * Modifies the language manager service.
 */
class ExoLinkMenuLinkManager extends MenuLinkManager {

  /**
   * Performs extra processing on plugin definitions.
   *
   * By default we add defaults for the type to the definition. If a type has
   * additional processing logic, the logic can be added by replacing or
   * extending this method.
   *
   * @param array $definition
   *   The definition to be processed and modified by reference.
   * @param string $plugin_id
   *   The ID of the plugin this definition is being used for.
   */
  protected function processDefinition(array &$definition, $plugin_id) {
    // Use the micon link class override.
    $this->defaults['class'] = 'Drupal\exo_link_menu\ExoLinkMenuLinkDefault';
    parent::processDefinition($definition, $plugin_id);
  }

}
