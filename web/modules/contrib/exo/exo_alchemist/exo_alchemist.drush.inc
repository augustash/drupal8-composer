<?php

/**
 * @file
 * Drush commands for Sync.
 */

/**
 * Implements hook_drush_command().
 */
function exo_alchemist_drush_command() {
  $items = [];

  $items['exo-alchemist-install'] = [
    'description' => 'Install an Alchemist component with pending changes.',
    'examples' => [
      'drush exo-alchemist-install component_id' => dt('Install the Alchemist component with the ID @name.', ['@name' => 'component_id']),
    ],
    'arguments' => [
      'resource_id' => dt('The machine name of an Alchemist component.'),
    ],
    'aliases' => ['eai', 'exo:alchemist:install'],
  ];

  $items['exo-alchemist-uninstall'] = [
    'description' => 'Uninstall an Alchemist component with pending changes.',
    'examples' => [
      'drush exo-alchemist-uninstall component_id' => dt('Uninstall the Alchemist component with the ID @name.', ['@name' => 'component_id']),
    ],
    'arguments' => [
      'resource_id' => dt('The machine name of an Alchemist component.'),
    ],
    'aliases' => ['earem', 'exo:alchemist:uninstall'],
  ];

  $items['exo-alchemist-update'] = [
    'description' => 'Update an Alchemist component with pending changes.',
    'examples' => [
      'drush exo-alchemist-update component_id' => dt('Update the Alchemist component with the ID @name.', ['@name' => 'component_id']),
    ],
    'arguments' => [
      'resource_id' => dt('The machine name of an Alchemist component.'),
    ],
    'options' => [
      'force' => [
        'description' => 'Force an update.',
      ],
    ],
    'aliases' => ['eau', 'exo:alchemist:update'],
  ];

  $items['exo-alchemist-update-all'] = [
    'description' => 'Update all Alchemist components with pending changes.',
    'examples' => [
      'drush exo-alchemist-update-all' => dt('Update all Alchemist components with pending changes.'),
    ],
    'aliases' => ['eaua', 'exo:alchemist:update:all'],
  ];

  $items['exo-alchemist-reinstall'] = [
    'description' => 'Reinstall an Alchemist component with pending changes.',
    'examples' => [
      'drush exo-alchemist-reinstall component_id' => dt('Reinstall the Alchemist component with the ID @name.', ['@name' => 'component_id']),
    ],
    'arguments' => [
      'resource_id' => dt('The machine name of an Alchemist component.'),
    ],
    'aliases' => ['ear', 'exo:alchemist:reinstall'],
  ];

  return $items;
}

/**
 * Install a specific alchemist component.
 */
function drush_exo_alchemist_install($component_id = NULL) {
  // If no $name provided, abort.
  if (!$component_id) {
    drush_print(dt('Component id missing. See help using drush exo-alchemist-reinstall --help.'));
    return;
  }

  /** @var \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager */
  $exo_component_manager = \Drupal::service('plugin.manager.exo_component');
  // Make sure we have all current definitions.
  $exo_component_manager->clearCachedDefinitions();

  if (!$definition = $exo_component_manager->getDefinition($component_id, FALSE)) {
    drush_print(dt('Component id (@id) is not valid. Available component ids are @list.', [
      '@id' => $component_id,
      '@list' => implode(', ', array_keys($exo_component_manager->getDefinitions())),
    ]));
    return;
  }

  if ($exo_component_manager->getInstalledDefinition($component_id, FALSE)) {
    drush_print(dt('Component id (@id) is already installed.', [
      '@id' => $component_id,
    ]));
    return;
  }

  if ($exo_component_manager->installEntityType($definition)) {
    drush_print(dt('Component installed successfully. (@label: @id)', [
      '@id' => $component_id,
      '@label' => $definition->getLabel(),
    ]));
  }
  else {
    drush_print(dt('There was an error installing the component. (@label: @id)', [
      '@id' => $component_id,
      '@label' => $definition->getLabel(),
    ]));
  }
  return TRUE;
}

/**
 * Reinstall a specific alchemist component.
 */
function drush_exo_alchemist_uninstall($component_id = NULL) {
  // If no $name provided, abort.
  if (!$component_id) {
    drush_print(dt('Component id missing. See help using drush exo-alchemist-reinstall --help.'));
    return;
  }

  /** @var \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager */
  $exo_component_manager = \Drupal::service('plugin.manager.exo_component');
  // Make sure we have all current definitions.
  $exo_component_manager->clearCachedDefinitions();

  if (!$definition = $exo_component_manager->getInstalledDefinition($component_id, FALSE)) {
    drush_print(dt('Component id (@id) is not installed. Available component ids are @list.', [
      '@id' => $component_id,
      '@list' => implode(', ', array_keys($exo_component_manager->getInstalledDefinitions())),
    ]));
    return;
  }

  if ($exo_component_manager->uninstallEntityType($definition)) {
    drush_print(dt('Component uninstalled successfully. (@label: @id)', [
      '@id' => $component_id,
      '@label' => $definition->getLabel(),
    ]));
  }
  else {
    drush_print(dt('There was an error uninstalling the component. (@label: @id)', [
      '@id' => $component_id,
      '@label' => $definition->getLabel(),
    ]));
  }
  return TRUE;
}

/**
 * Update a specific alchemist component with pending updates.
 */
function drush_exo_alchemist_update($component_id = NULL) {
  // If no $name provided, abort.
  if (!$component_id) {
    drush_print(dt('Component id missing. See help using drush exo-alchemist-update --help.'));
    return;
  }

  $force = drush_get_option('force', FALSE);

  /** @var \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager */
  $exo_component_manager = \Drupal::service('plugin.manager.exo_component');
  // Make sure we have all current definitions.
  $exo_component_manager->clearCachedDefinitions();
  // Only allow installed and non-computed components.
  $installed_definitions = array_filter($exo_component_manager->getInstalledDefinitions(), function ($definition) {
    /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
    return !$definition->isComputed();
  });
  if (!isset($installed_definitions[$component_id])) {
    drush_print(dt('Component id (@id) is not valid. Available component ids are @list.', [
      '@id' => $component_id,
      '@list' => implode(', ', array_keys($installed_definitions)),
    ]));
    return;
  }

  /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
  $definition = $installed_definitions[$component_id];
  if (!$exo_component_manager->installedDefinitionHasChanges($definition) && !$force) {
    drush_print(dt('Component does not have any pending updates or you do not have access to update it. (@label: @id)', [
      '@id' => $component_id,
      '@label' => $definition->getLabel(),
    ]));
    return;
  }

  if ($exo_component_manager->updateInstalledDefinition($definition)) {
    drush_print(dt('Component updated successfully. (@label: @id)', [
      '@id' => $component_id,
      '@label' => $definition->getLabel(),
    ]));
  }
  else {
    drush_print(dt('There was an error updating the component. (@label: @id)', [
      '@id' => $component_id,
      '@label' => $definition->getLabel(),
    ]));
  }
}

/**
 * Update all alchemist components with pending updates.
 */
function drush_exo_alchemist_update_all() {
  /** @var \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager */
  $exo_component_manager = \Drupal::service('plugin.manager.exo_component');
  // Make sure we have all current definitions.
  $exo_component_manager->clearCachedDefinitions();

  // Only allow installed and visible components.
  $updated = [];
  foreach ($exo_component_manager->getInstalledDefinitions() as $definition) {
    /** @var \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition */
    if ($definition->isComputed()) {
      continue;
    }
    if ($exo_component_manager->installedDefinitionHasChanges($definition)) {
      if ($exo_component_manager->updateInstalledDefinition($definition)) {
        $updated[] = $definition->getLabel() . ': ' . $definition->id();
      }
    }
  }

  if (!empty($updated)) {
    drush_print(dt('Components updated successfully. (@components)', [
      '@components' => implode(', ', $updated),
    ]));
  }
  else {
    drush_print(dt('No components were updated as all components are current.'));
  }
}

/**
 * Reinstall a specific alchemist component.
 */
function drush_exo_alchemist_reinstall($component_id = NULL) {
  if (drush_exo_alchemist_uninstall($component_id)) {
    drush_exo_alchemist_install($component_id);
  }
}
