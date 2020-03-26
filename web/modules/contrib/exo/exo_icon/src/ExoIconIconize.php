<?php

namespace Drupal\exo_icon;

/**
 * Class ExoIconIconize.
 *
 * @package Drupal\exo_icon
 */
class ExoIconIconize {
  use ExoIconTranslationTrait;

  /**
   * Transforms a string into an icon + string.
   *
   * This can be used interchangeably with the
   * \Drupal\Core\StringTranslation\StringTranslationTrait.
   *
   * @param string $string
   *   A string containing the English text to translate.
   * @param array $args
   *   (optional) An associative array of replacements to make after
   *   translation. Based on the first character of the key, the value is
   *   escaped and/or themed. See
   *   \Drupal\Component\Render\FormattableMarkup::placeholderFormat() for
   *   details.
   * @param array $options
   *   (optional) An associative array of additional options, with the following
   *   elements:
   *   - 'langcode' (defaults to the current language): A language code, to
   *     translate to a language other than what is used to display the page.
   *   - 'context' (defaults to the empty context): The context the source
   *     string belongs to.
   *
   * @return \Drupal\Core\Render\Markup
   *   An object that, when cast to a string, returns the icon markup and
   *   translated string.
   *
   * @see \Drupal\Core\StringTranslation\StringTranslationTrait::t()
   *
   * @ingroup sanitization
   */
  public static function iconize($string = '', array $args = [], array $options = []) {
    $instance = new static();
    return $instance->icon($string, $args, $options);
  }

}
