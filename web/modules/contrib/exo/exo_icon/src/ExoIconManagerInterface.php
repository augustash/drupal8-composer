<?php

namespace Drupal\exo_icon;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for exo_icon managers.
 */
interface ExoIconManagerInterface extends PluginManagerInterface {

  /**
   * Get definitions by by prefix.
   *
   * @param string|string[] $prefixes
   *   Optional prefixes to filter the icon definitions by.
   *
   * @return mixed[]
   *   An array of plugin definitions (empty array if no definitions were
   *   found). Keys are plugin IDs.
   */
  public function getDefinitionsWithPrefix($prefixes = []);

  /**
   * Match a string against the icon definitions.
   *
   * @param string $string
   *   A string to match against icon definitions.
   * @param string|string[] $prefixes
   *   Optional prefixes to filter the icon definitions by.
   *
   * @return string
   *   The icon id as defined within the definition.
   */
  public function getDefinitionMatch($string, $prefixes = []);

}
