<?php

namespace Drupal\exo_alchemist\Plugin;

/**
 * Provides methods for rendering forms within forms.
 */
trait ExoComponentFieldDisplayFormTrait {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Render the element.
   *
   * @param array $element
   *   The element.
   */
  protected function getFormAsPlaceholder(array $element) {
    $element['#exo_theme_lock'] = TRUE;
    $raw = $this->renderer()->render($element);
    $raw = str_replace(['<form', 'form>'], ['<div', 'div>'], $raw);
    return [
      '#type' => 'inline_template',
      '#template' => '{{ form|raw }}',
      '#context' => [
        'form' => $raw,
      ],
    ];
  }

  /**
   * Get the renderer.
   *
   * @return \Drupal\Core\Render\RendererInterface
   *   The renderer.
   */
  protected function renderer() {
    if (!isset($this->renderer)) {
      $this->renderer = \Drupal::service('renderer');
    }
    return $this->renderer;
  }

}
