<?php

namespace Drupal\aeon\Utility;

use Drupal\aeon\Aeon;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element as DrupalElement;

/**
 * Provides helper methods for Drupal render elements.
 *
 * @ingroup utility
 *
 * @see \Drupal\Core\Render\Element
 */
class Element extends DrupalAttributes {

  /**
   * The current state of the form.
   *
   * @var \Drupal\Core\Form\FormStateInterface
   */
  protected $formState;

  /**
   * The element type.
   *
   * @var string
   */
  protected $type = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $attributePrefix = '#';

  /**
   * Element constructor.
   *
   * @param array|string $element
   *   A render array element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function __construct(&$element = [], FormStateInterface $form_state = NULL) {
    if (!is_array($element)) {
      $element = ['#markup' => $element instanceof MarkupInterface ? $element : new FormattableMarkup($element, [])];
    }
    $this->array = &$element;
    $this->formState = $form_state;
  }

  /**
   * Magic get method.
   *
   * This is only for child elements, not properties.
   *
   * @param string $key
   *   The name of the child element to retrieve.
   *
   * @return \Drupal\aeon\Utility\Element
   *   The child element object.
   *
   * @throws \InvalidArgumentException
   *   Throws this error when the name is a property (key starting with #).
   */
  public function &__get($key) {
    if (DrupalElement::property($key)) {
      throw new \InvalidArgumentException('Cannot dynamically retrieve element property. Please use \Drupal\aeon\Utility\Element::getProperty instead.');
    }
    $instance = new self($this->offsetGet($key, []));
    return $instance;
  }

  /**
   * Magic set method.
   *
   * This is only for child elements, not properties.
   *
   * @param string $key
   *   The name of the child element to set.
   * @param mixed $value
   *   The value of $name to set.
   *
   * @throws \InvalidArgumentException
   *   Throws this error when the name is a property (key starting with #).
   */
  public function __set($key, $value) {
    if (DrupalElement::property($key)) {
      throw new \InvalidArgumentException('Cannot dynamically retrieve element property. Use \Drupal\aeon\Utility\Element::setProperty instead.');
    }
    $this->offsetSet($key, ($value instanceof Element ? $value->getArray() : $value));
  }

  /**
   * Magic isset method.
   *
   * This is only for child elements, not properties.
   *
   * @param string $name
   *   The name of the child element to check.
   *
   * @return bool
   *   TRUE or FALSE
   *
   * @throws \InvalidArgumentException
   *   Throws this error when the name is a property (key starting with #).
   */
  public function __isset($name) {
    if (DrupalElement::property($name)) {
      throw new \InvalidArgumentException('Cannot dynamically check if an element has a property. Use \Drupal\aeon\Utility\Element::unsetProperty instead.');
    }
    return parent::__isset($name);
  }

  /**
   * Magic unset method.
   *
   * This is only for child elements, not properties.
   *
   * @param mixed $name
   *   The name of the child element to unset.
   *
   * @throws \InvalidArgumentException
   *   Throws this error when the name is a property (key starting with #).
   */
  public function __unset($name) {
    if (DrupalElement::property($name)) {
      throw new \InvalidArgumentException('Cannot dynamically unset an element property. Use \Drupal\aeon\Utility\Element::hasProperty instead.');
    }
    parent::__unset($name);
  }

  /**
   * Appends a property with a value.
   *
   * @param string $name
   *   The name of the property to set.
   * @param mixed $value
   *   The value of the property to set.
   *
   * @return $this
   */
  public function appendProperty($name, $value) {
    $property = &$this->getProperty($name);
    $value = $value instanceof Element ? $value->getArray() : $value;

    // If property isn't set, just set it.
    if (!isset($property)) {
      $property = $value;
      return $this;
    }

    if (is_array($property)) {
      $property[] = Element::create($value)->getArray();
    }
    else {
      $property .= (string) $value;
    }

    return $this;
  }

  /**
   * Identifies the children of an element array, optionally sorted by weight.
   *
   * The children of a element array are those key/value pairs whose key does
   * not start with a '#'. See drupal_render() for details.
   *
   * @param bool $sort
   *   Boolean to indicate whether the children should be sorted by weight.
   *
   * @return array
   *   The array keys of the element's children.
   */
  public function childKeys($sort = FALSE) {
    return DrupalElement::children($this->array, $sort);
  }

  /**
   * Retrieves the children of an element array, optionally sorted by weight.
   *
   * The children of a element array are those key/value pairs whose key does
   * not start with a '#'. See drupal_render() for details.
   *
   * @param bool $sort
   *   Boolean to indicate whether the children should be sorted by weight.
   *
   * @return \Drupal\aeon\Utility\Element[]
   *   An array child elements.
   */
  public function children($sort = FALSE) {
    $children = [];
    foreach ($this->childKeys($sort) as $child) {
      $children[$child] = new self($this->array[$child]);
    }
    return $children;
  }

  /**
   * Creates a new \Drupal\aeon\Utility\Element instance.
   *
   * @param array|string $element
   *   A render array element or a string.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   A current FormState instance, if any.
   *
   * @return \Drupal\aeon\Utility\Element
   *   The newly created element instance.
   */
  public static function create(&$element = [], FormStateInterface $form_state = NULL) {
    return $element instanceof self ? $element : new self($element, $form_state);
  }

  /**
   * Creates a new standalone \Drupal\aeon\Utility\Element instance.
   *
   * It does not reference the original element passed. If an Element instance
   * is passed, it will clone it so it doesn't affect the original element.
   *
   * @param array|string|\Drupal\aeon\Utility\Element $element
   *   A render array element, string or Element instance.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   A current FormState instance, if any.
   *
   * @return \Drupal\aeon\Utility\Element
   *   The newly created element instance.
   */
  public static function createStandalone($element = [], FormStateInterface $form_state = NULL) {
    // Immediately return a cloned version if element is already an Element.
    if ($element instanceof self) {
      return clone $element;
    }
    $standalone = is_object($element) ? clone $element : $element;
    return static::create($standalone, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function exchangeArray($data) {
    $old = parent::exchangeArray($data);
    return $old;
  }

  /**
   * Retrieves the render array for the element.
   *
   * @return array
   *   The element render array, passed by reference.
   */
  public function &getArray() {
    return $this->array;
  }

  /**
   * Retrieves a context value from the #context element property, if any.
   *
   * @param string $name
   *   The name of the context key to retrieve.
   * @param mixed $default
   *   Optional. The default value to use if the context $name isn't set.
   *
   * @return mixed|null
   *   The context value or the $default value if not set.
   */
  public function &getContext($name, $default = NULL) {
    $context = &$this->getProperty('context', []);
    if (!isset($context[$name])) {
      $context[$name] = $default;
    }
    return $context[$name];
  }

  /**
   * Returns the error message filed against the given form element.
   *
   * Form errors higher up in the form structure override deeper errors as well
   * as errors on the element itself.
   *
   * @return string|null
   *   Either the error message for this element or NULL if there are no errors.
   *
   * @throws \BadMethodCallException
   *   When the element instance was not constructed with a valid form state
   *   object.
   */
  public function getError() {
    if (!$this->formState) {
      throw new \BadMethodCallException('The element instance must be constructed with a valid form state object to use this method.');
    }
    return $this->formState->getError($this->array);
  }

  /**
   * Retrieves the render array for the element.
   *
   * @param string $name
   *   The name of the element property to retrieve, not including the # prefix.
   * @param mixed $default
   *   The default to set if property does not exist.
   *
   * @return mixed
   *   The property value, NULL if not set.
   */
  public function &getProperty($name, $default = NULL) {
    return $this->offsetGet("#$name", $default);
  }

  /**
   * Returns the visible children of an element.
   *
   * @return array
   *   The array keys of the element's visible children.
   */
  public function getVisibleChildren() {
    return DrupalElement::getVisibleChildren($this->array);
  }

  /**
   * Indicates whether the element has an error set.
   *
   * @throws \BadMethodCallException
   *   When the element instance was not constructed with a valid form state
   *   object.
   */
  public function hasError() {
    $error = $this->getError();
    return isset($error);
  }

  /**
   * Indicates whether the element has a specific property.
   *
   * @param string $name
   *   The property to check.
   */
  public function hasProperty($name) {
    return $this->offsetExists("#$name");
  }

  /**
   * Indicates whether the element is a button.
   *
   * @return bool
   *   TRUE or FALSE.
   */
  public function isButton() {
    return !empty($this->array['#is_button']) || $this->isType([
      'button',
      'submit',
      'reset',
      'image_button',
    ]) || $this->hasClass('btn');
  }

  /**
   * Indicates whether the given element is empty.
   *
   * An element that only has #cache set is considered empty, because it will
   * render to the empty string.
   *
   * @return bool
   *   Whether the given element is empty.
   */
  public function isEmpty() {
    return DrupalElement::isEmpty($this->array);
  }

  /**
   * Indicates whether a property on the element is empty.
   *
   * @param string $name
   *   The property to check.
   *
   * @return bool
   *   Whether the given property on the element is empty.
   */
  public function isPropertyEmpty($name) {
    return $this->hasProperty($name) && empty($this->getProperty($name));
  }

  /**
   * Checks if a value is a render array.
   *
   * @param mixed $value
   *   The value to check.
   *
   * @return bool
   *   TRUE if the given value is a render array, otherwise FALSE.
   */
  public static function isRenderArray($value) {
    return is_array($value) && (isset($value['#type']) ||
      isset($value['#theme']) || isset($value['#theme_wrappers']) ||
      isset($value['#markup']) || isset($value['#attached']) ||
      isset($value['#cache']) || isset($value['#lazy_builder']) ||
      isset($value['#create_placeholder']) || isset($value['#pre_render']) ||
      isset($value['#post_render']) || isset($value['#process']));
  }

  /**
   * Checks if the element is a specific type of element.
   *
   * @param string|array $type
   *   The element type(s) to check.
   *
   * @return bool
   *   TRUE if element is or one of $type.
   */
  public function isType($type) {
    $property = $this->getProperty('type');
    return $property && in_array($property, (is_array($type) ? $type : [$type]));
  }

  /**
   * Determines if an element is visible.
   *
   * @return bool
   *   TRUE if the element is visible, otherwise FALSE.
   */
  public function isVisible() {
    return DrupalElement::isVisibleElement($this->array);
  }

  /**
   * Maps an element's properties to its attributes array.
   *
   * @param array $map
   *   An associative array whose keys are element property names and whose
   *   values are the HTML attribute names to set on the corresponding
   *   property; e.g., array('#propertyname' => 'attributename'). If both names
   *   are identical except for the leading '#', then an attribute name value is
   *   sufficient and no property name needs to be specified.
   *
   * @return $this
   */
  public function map(array $map) {
    DrupalElement::setAttributes($this->array, $map);
    return $this;
  }

  /**
   * Prepends a property with a value.
   *
   * @param string $name
   *   The name of the property to set.
   * @param mixed $value
   *   The value of the property to set.
   *
   * @return $this
   */
  public function prependProperty($name, $value) {
    $property = &$this->getProperty($name);
    $value = $value instanceof Element ? $value->getArray() : $value;

    // If property isn't set, just set it.
    if (!isset($property)) {
      $property = $value;
      return $this;
    }

    if (is_array($property)) {
      array_unshift($property, Element::create($value)->getArray());
    }
    else {
      $property = (string) $value . (string) $property;
    }

    return $this;
  }

  /**
   * Gets properties of a structured array element (keys beginning with '#').
   *
   * @return array
   *   An array of property keys for the element.
   */
  public function properties() {
    return DrupalElement::properties($this->array);
  }

  /**
   * Renders the final element HTML.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The rendered HTML.
   */
  public function render() {
    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = \Drupal::service('renderer');
    return $renderer->render($this->array);
  }

  /**
   * Renders the final element HTML.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The rendered HTML.
   */
  public function renderPlain() {
    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = \Drupal::service('renderer');
    return $renderer->renderPlain($this->array);
  }

  /**
   * Renders the final element HTML.
   *
   * (Cannot be executed within another render context.)
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The rendered HTML.
   */
  public function renderRoot() {
    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = \Drupal::service('renderer');
    return $renderer->renderRoot($this->array);
  }

  /**
   * Flags an element as having an error.
   *
   * @param string $message
   *   (optional) The error message to present to the user.
   *
   * @return $this
   *
   * @throws \BadMethodCallException
   *   When the element instance was not constructed with a valid form state
   *   object.
   */
  public function setError($message = '') {
    if (!$this->formState) {
      throw new \BadMethodCallException('The element instance must be constructed with a valid form state object to use this method.');
    }
    $this->formState->setError($this->array, $message);
    return $this;
  }

  /**
   * Adds an icon to button element based on its text value.
   *
   * @param string $icon
   *   The Micon icon id to set as the icon.
   * @param bool $icon_only
   *   Set as icon only.
   * @param string $icon_position
   *   Set icon position. Either before or after.
   *
   * @return $this
   */
  public function setIcon($icon = '', $icon_only = NULL, $icon_position = 'before') {
    if ($this->isButton() && !Aeon::hasIcons()) {
      return $this;
    }
    if ($value = $this->getProperty('value', $this->getProperty('title'))) {
      $icon_handler = Aeon::iconize($value);
      if ($icon) {
        $icon_handler->setIcon($icon);
      }
      if ($icon_only) {
        $icon_handler->setIconOnly();
      }
      if ($icon_position == 'after') {
        $icon_handler->setIconAfter();
      }
      $this->setProperty('value', $icon_handler);
    }
    return $this;
  }

  /**
   * Sets the value for a property.
   *
   * @param string $name
   *   The name of the property to set.
   * @param mixed $value
   *   The value of the property to set.
   *
   * @return $this
   */
  public function setProperty($name, $value) {
    $this->array["#$name"] = $value instanceof Element ? $value->getArray() : $value;
    return $this;
  }

  /**
   * Converts an element description into a tooltip based on certain criteria.
   *
   * @param array|\Drupal\aeon\Utility\Element|null $target_element
   *   The target element render array the tooltip is to be attached to, passed
   *   by reference or an existing Element object. If not set, it will default
   *   this Element instance.
   * @param bool $input_only
   *   Toggle determining whether or not to only convert input elements.
   * @param int $length
   *   The length of characters to determine if description is "simple".
   *
   * @return $this
   */
  public function smartDescription(&$target_element = NULL, $input_only = TRUE, $length = NULL) {
    static $theme;
    if (!isset($theme)) {
      $theme = Aeon::getTheme();
    }

    // Determine if tooltips are enabled.
    static $enabled;
    if (!isset($enabled)) {
      $enabled = $theme->getSetting('tooltip_enabled') && $theme->getSetting('forms_smart_descriptions');
    }

    // Immediately return if tooltip descriptions are not enabled.
    if (!$enabled) {
      return $this;
    }

    // Allow a different element to attach the tooltip.
    /** @var Element $target */
    if (is_object($target_element) && $target_element instanceof self) {
      $target = $target_element;
    }
    elseif (isset($target_element) && is_array($target_element)) {
      $target = new self($target_element, $this->formState);
    }
    else {
      $target = $this;
    }

    // For "password_confirm" element types, move the target to the first
    // textfield.
    if ($target->isType('password_confirm')) {
      $target = $target->pass1;
    }

    // Retrieve the length limit for smart descriptions.
    if (!isset($length)) {
      // Disable length checking by setting it to FALSE if empty.
      $length = (int) $theme->getSetting('forms_smart_descriptions_limit') ?: FALSE;
    }

    // Retrieve the allowed tags for smart descriptions. This is primarily used
    // for display purposes only (i.e. non-UI/UX related elements that wouldn't
    // require a user to "click", like a link). Disable length checking by
    // setting it to FALSE if empty.
    static $allowed_tags;
    if (!isset($allowed_tags)) {
      $allowed_tags = array_filter(array_unique(array_map('trim', explode(',', $theme->getSetting('forms_smart_descriptions_allowed_tags') . '')))) ?: FALSE;
    }

    // Return if element or target shouldn't have "simple" tooltip descriptions.
    $html = FALSE;
    if (($input_only && !$target->hasProperty('input'))
      // Ignore if the actual element has no #description set.
      || !$this->hasProperty('description')

      // Ignore if the target element already has a "data-toggle" attribute set.
      || $target->hasAttribute('data-toggle')

      // Ignore if the target element is #disabled.
      || $target->hasProperty('disabled')

      // Ignore if either the actual element or target element has an explicit
      // #smart_description property set to FALSE.
      || !$this->getProperty('smart_description', TRUE)
      || !$target->getProperty('smart_description', TRUE)

      // Ignore if the description is not "simple".
      || !Unicode::isSimple($this->getProperty('description'), $length, $allowed_tags, $html)
    ) {
      // Set the both the actual element and the target element
      // #smart_description property to FALSE.
      $this->setProperty('smart_description', FALSE);
      $target->setProperty('smart_description', FALSE);
      return $this;
    }

    // Default attributes type.
    $type = DrupalAttributes::ATTRIBUTES;

    // Use #label_attributes for 'checkbox' and 'radio' elements.
    if ($this->isType(['checkbox', 'radio'])) {
      $type = DrupalAttributes::LABEL;
    }
    // Use #wrapper_attributes for 'checkboxes' and 'radios' elements.
    elseif ($this->isType(['checkboxes', 'radios'])) {
      $type = DrupalAttributes::WRAPPER;
    }

    // Retrieve the proper attributes array.
    $attributes = $target->getAttributes($type);

    // Set the tooltip attributes.
    $attributes['title'] = $allowed_tags !== FALSE ? Xss::filter((string) $this->getProperty('description'), $allowed_tags) : $this->getProperty('description');
    $attributes['data-toggle'] = 'tooltip';
    if ($html || $allowed_tags === FALSE) {
      $attributes['data-html'] = 'true';
    }

    // Remove the element description so it isn't (re-)rendered later.
    $this->unsetProperty('description');

    return $this;
  }

  /**
   * Removes a property from the element.
   *
   * @param string $name
   *   The name of the property to unset.
   *
   * @return $this
   */
  public function unsetProperty($name) {
    unset($this->array["#$name"]);
    return $this;
  }

}
