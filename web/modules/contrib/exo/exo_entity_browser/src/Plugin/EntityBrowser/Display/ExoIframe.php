<?php

namespace Drupal\exo_entity_browser\Plugin\EntityBrowser\Display;

use Drupal\entity_browser\Plugin\EntityBrowser\Display\IFrame;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Form\FormStateInterface;

/**
 * Presents entity browser in an Modal.
 *
 * @EntityBrowserDisplay(
 *   id = "exo_iframe",
 *   label = @Translation("eXo iFrame"),
 *   description = @Translation("Displays the entity browser in an iFrame"),
 *   uses_route = TRUE
 * )
 */
class ExoIframe extends IFrame {

  /**
   * {@inheritdoc}
   */
  public function displayEntityBrowser(array $element, FormStateInterface $form_state, array &$complete_form, array $persistent_data = []) {
    $display = parent::displayEntityBrowser($element, $form_state, $complete_form, $persistent_data);
    $display['link']['#attached']['library'] = ['exo_entity_browser/iframe'];
    $display['link']['#attached']['drupalSettings']['entity_browser']['exo_iframe'] = $display['link']['#attached']['drupalSettings']['entity_browser']['iframe'];
    unset($display['link']['#attached']['drupalSettings']['entity_browser']['iframe']);
    return $display;
  }

  /**
   * {@inheritdoc}
   */
  public function propagateSelection(FilterResponseEvent $event) {
    $render = [
      'labels' => [
        '#markup' => 'Labels: ' . implode(', ', array_map(function (EntityInterface $item) {
          return $item->label();
        }, $this->entities)),
        '#attached' => [
          'library' => ['exo_entity_browser/iframe.selection'],
          'drupalSettings' => [
            'entity_browser' => [
              $this->pluginDefinition['id'] => [
                'entities' => array_map(function (EntityInterface $item) {
                  return [$item->id(), $item->uuid(), $item->getEntityTypeId()];
                }, $this->entities),
                'uuid' => $this->request->query->get('uuid'),
              ],
            ],
          ],
        ],
      ],
    ];

    $event->setResponse(new Response(\Drupal::service('bare_html_page_renderer')->renderBarePage($render, 'Entity browser', 'page')));
  }

}
