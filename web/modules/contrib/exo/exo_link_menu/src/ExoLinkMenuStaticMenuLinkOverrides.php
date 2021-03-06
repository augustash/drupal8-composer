<?php

namespace Drupal\exo_link_menu;

use Drupal\Core\Menu\StaticMenuLinkOverrides;

/**
 * Defines an implementation of the menu link override using a config file.
 */
class ExoLinkMenuStaticMenuLinkOverrides extends StaticMenuLinkOverrides {

  /**
   * {@inheritdoc}
   */
  public function saveOverride($id, array $definition) {
    // Only allow to override a specific subset of the keys.
    $expected = [
      'menu_name' => '',
      'parent' => '',
      'weight' => 0,
      'expanded' => FALSE,
      'enabled' => FALSE,
      // eXo Icon: options are allowed.
      'options' => [],
    ];
    // Filter the overrides to only those that are expected.
    $definition = array_intersect_key($definition, $expected);
    // Ensure all values are set.
    $definition = $definition + $expected;
    if ($definition) {
      // Cast keys to avoid config schema during save.
      $definition['menu_name'] = (string) $definition['menu_name'];
      $definition['parent'] = (string) $definition['parent'];
      $definition['weight'] = (int) $definition['weight'];
      $definition['expanded'] = (bool) $definition['expanded'];
      $definition['enabled'] = (bool) $definition['enabled'];
      // eXo Icon: options are allowed.
      $definition['options'] = $definition['options'];

      $id = static::encodeId($id);
      $all_overrides = $this->getConfig()->get('definitions');
      // Combine with any existing data.
      $all_overrides[$id] = $definition + $this->loadOverride($id);
      $this->getConfig()->set('definitions', $all_overrides)->save(TRUE);
    }
    return array_keys($definition);
  }

}
