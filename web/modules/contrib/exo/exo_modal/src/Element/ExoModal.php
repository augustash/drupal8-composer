<?php

namespace Drupal\exo_modal\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Url;

/**
 * Provides a render element that wraps child elements in an modal.
 *
 * @RenderElement("exo_modal")
 */
class ExoModal extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#process' => [
        [$class, 'processGroup'],
        [$class, 'processModal'],
        [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
        [$class, 'preRenderModal'],
      ],
      '#value' => NULL,
      '#theme_wrappers' => ['exo_modal_container'],
      '#title' => '',
      '#description' => '',
      '#use_close' => TRUE,
      '#trigger_text' => t('Open Modal'),
      '#trigger_icon' => '',
      '#trigger_icon_only' => FALSE,
      '#trigger_attributes' => [],
      '#modal_settings' => [],
      '#modal_attributes' => [],
      '#view' => [
        'name' => NULL,
        'display_id' => NULL,
        'argument1' => NULL,
        'argument2' => NULL,
      ],
      '#entity' => [
        'entity_type' => NULL,
        'id' => NULL,
        'display_id' => NULL,
      ],
    ];
  }

  /**
   * Pre-render a modal element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element.
   *
   * @return array
   *   The modified element with all group members.
   */
  public static function preRenderModal(array $element) {
    // Generate the ID of the element if it's not explicitly given.
    $id = 'exo-modal-element';
    if (isset($element['#id'])) {
      $id = $element['#id'];
    }
    elseif (!empty($element['#parents'])) {
      $id = implode('-', $element['#parents']);
    }
    $element['#id'] = Html::getUniqueId($id . '-wrapper');

    if (!empty($element['#title'])) {
      $element['#trigger_text'] = $element['#title'];
    }
    if (empty($element['#description'])) {
      $element['#description'] = '';
    }

    $element['#modal_settings'] += [
      'trigger' => [],
      'modal' => [],
    ];
    $element['#modal_settings']['modal'] += [
      'title' => $element['#trigger_text'],
      'subtitle' => $element['#description'],
      'padding' => '30px',
    ];

    if (!empty($element['#use_close'])) {
      $element['_actions'] = [
        '#type' => 'actions',
        '#weight' => 1000,
      ];
      $element['_actions']['_close'] = [
        '#type' => 'exo_modal_close',
        '#label' => t('Close and Continue'),
        '#attributes' => ['class' => ['button--primary']],
      ];
    }

    $element['#view'] += [
      'name' => NULL,
      'display_id' => NULL,
      'argument1' => NULL,
      'argument2' => NULL,
    ];
    if (!empty($element['#view']['name'])) {
      $display_id = !empty($element['#view']['display_id']) ? $element['#view']['display_id'] : 'default';
      $url = Url::fromRoute('exo_modal.api.views.view', [
        'view' => $element['#view']['name'],
        'display_id' => $display_id,
        'argument1' => $element['#view']['argument1'],
        'argument2' => $element['#view']['argument2'],
      ])->getInternalPath();
      $element['#modal_settings']['modal']['contentAjax'] = $url;
    }

    $element['#entity'] += [
      'entity_type' => NULL,
      'id' => NULL,
      'display_id' => NULL,
    ];
    if (!empty($element['#entity']['id']) && !empty($element['#entity']['entity_type'])) {
      $entity = \Drupal::entityTypeManager()->getStorage($element['#entity']['entity_type'])->load($element['#entity']['id']);
      if (!$entity->access('view')) {
        return [];
      }
      if ($entity->hasLinkTemplate('canonical')) {
        $element['#trigger_attributes']['href'] = $entity->toUrl()->toString();
      }
      $display_id = !empty($element['#entity']['display_id']) ? $element['#entity']['display_id'] : 'default';
      $url = Url::fromRoute('exo_modal.api.entity.view', [
        'entity_type' => $element['#entity']['entity_type'],
        'entity' => $element['#entity']['id'],
        'display_id' => $display_id,
      ])->getInternalPath();
      $element['#modal_settings']['modal']['contentAjax'] = $url;
    }
    return $element;
  }

  /**
   * Processes a container element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   container.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processModal(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element['#modal_settings'] += [
      'trigger' => [],
      'modal' => [],
    ];
    $element['#modal_settings']['modal'] += [
      'nest' => TRUE,
      'appendTo' => 'form',
      'appendToOverlay' => 'form',
      'appendToNavigate' => 'form',
      'appendToClosest' => TRUE,
    ];
    return $element;
  }

}
