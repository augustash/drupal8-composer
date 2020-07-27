<?php

namespace Drupal\exo_form\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\NestedArray;

/**
 * Base class for eXo Form plugins.
 */
abstract class ExoFormBase extends PluginBase implements ExoFormInterface {
  use StringTranslationTrait;

  /**
   * Disable the wrapping element for this input field.
   *
   * @var bool
   *   If TRUE the wrapping element will be added to this field.
   */
  protected $wrapperSupported = TRUE;

  /**
   * Disable intersect for this input field.
   *
   * @var bool
   *   If TRUE intersect is allowed for this field.
   */
  protected $intersectSupported = FALSE;

  /**
   * Disable floating for this input field.
   *
   * @var bool
   *   If TRUE floating is allowed for this field.
   */
  protected $floatSupported = FALSE;

  /**
   * Wrapping attributes are sent to template via this attribute name.
   *
   * @var string
   */
  protected $featureAttributeKey = 'exo_form_element_attributes';

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key = '') {
    $settings = $this->getSettings();
    $value = &NestedArray::getValue($settings, (array) $key);
    return $value;
  }

  /**
   * Enable wrapping element support for this field.
   */
  protected function enableWrapper($status = TRUE) {
    $this->wrapperSupported = $status;
  }

  /**
   * Disable wrapping element support for this field.
   */
  protected function disableWrapper() {
    $this->enableWrapper(FALSE);
  }

  /**
   * Enable intersect support for this field.
   */
  protected function enableIntersectSupport() {
    $this->intersectSupported = TRUE;
  }

  /**
   * Disable floating support for this field.
   */
  protected function disableIntersectSupport() {
    $this->intersectSupported = FALSE;
  }

  /**
   * Enable floating support for this field.
   */
  protected function enableFloatSupport() {
    $this->floatSupported = TRUE;
  }

  /**
   * Disable floating support for this field.
   */
  protected function disableFloatSupport() {
    $this->floatSupported = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    // Move exo_form_element_pre_render as last pre_render.
    if (!empty($element['#pre_render'])) {
      foreach ($element['#pre_render'] as $key => $value) {
        if (is_array($value) && $value[0] == 'Drupal\exo_form\ExoFormElementHandler') {
          unset($element['#pre_render'][$key]);
        }
      }
    }
    $element['#pre_render'][] = ['Drupal\exo_form\ExoFormElementHandler', 'preRender'];
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $exo_wrapper_supported = isset($element['#exo_wrapper_supported']) ? $element['#exo_wrapper_supported'] : $this->wrapperSupported;
    if ($exo_wrapper_supported) {
      $element['#theme_wrappers'][] = 'exo_form_element_container';
    }
    if (isset($element['#attributes'])) {
      exo_form_inline_convert($element['#attributes']);
      if (isset($element['#attributes']['class']) && is_array($element['#attributes']['class'])) {
        if (in_array('hidden', $element['#attributes']['class'])) {
          $element['#exo_form_attributes']['class'][] = 'hidden';
        }
      }
    }
    // Support deprecated 'float' option.
    if ($this->getSetting(['float'])) {
      $this->configuration['style'] = 'float';
    }
    if ($this->getSetting('style') === 'float'&& !empty($this->floatSupported)) {
      $element['#' . $this->featureAttributeKey]['class'][] = 'exo-form-element-float';
    }
    if ($this->getSetting('style') === 'intersect' && !empty($this->intersectSupported)) {
      $element['#' . $this->featureAttributeKey]['class'][] = 'exo-form-element-intersect';
    }
    return $element;
  }

  /**
   * Check if element should be processed.
   *
   * @return bool
   *   Return TRUE if element should be processed.
   */
  public function applies($element) {
    // Allow individual form elements to pass #exo_form_default to bypass eXo
    // form modifications.
    if (!empty($element['#exo_form_default'])) {
      return FALSE;
    }
    return TRUE;
  }

}
