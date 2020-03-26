<?php

namespace Drupal\exo_icon\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a eXo icon browser.
 *
 * Best used with exo_icon element.
 *
 * Usage example:
 * @code
 * $form['icon_browser'] = array(
 *   '#type' => 'exo_icon_browser',
 *   '#packages' => ['regular'],
 * );
 * @endcode
 *
 * @RenderElement("exo_icon_browser")
 */
class ExoIconBrowser extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#id' => '',
      '#pre_render' => [
        [$class, 'preRenderIconBrowser'],
      ],
      '#packages' => [],
      '#settings' => [],
    ];
  }

  /**
   * Pre-render callback: Renders browser.
   *
   * @return array
   *   A render array.
   */
  public static function preRenderIconBrowser($element) {
    $exo_icon_repository = \Drupal::service('exo_icon.repository');
    $renderer = \Drupal::service('renderer');

    $package_definitions = $exo_icon_repository->getPackages($element['#packages'], TRUE);
    // Expose package icons to js.
    $element['#packages'] = [];
    foreach ($package_definitions as $package_id => $package_definition) {
      $element['#packages'][$package_id] = $package_definition;
      $icons = [];
      $icon_definitions = $exo_icon_repository->getDefinitionsByPackage($package_definition->id());
      $icon_instances = $exo_icon_repository->getInstances($icon_definitions);
      foreach ($icon_instances as $icon_id => $icon_instance) {
        $renderable = $icon_instance->toRenderable();
        $renderable['#attributes']['data-icon-id'] = $icon_instance->getId();
        $icons[] = $renderer->render($renderable);
      }
      $element['#attached']['drupalSettings']['exoIcon']['package'][$package_definition->id()] = $icons;
    }

    $element['#theme'] = 'exo_icon_browser';
    $element['#attached']['library'][] = 'exo_icon/browser';
    $element['#attached']['drupalSettings']['exoIcon']['browser'][$element['#id']] = [
      'packages' => array_keys($package_definitions),
    ] + $element['#settings'];

    $element['package_select'] = [
      '#type' => 'select',
      '#options' => [],
    ];

    return $element;
  }

}
