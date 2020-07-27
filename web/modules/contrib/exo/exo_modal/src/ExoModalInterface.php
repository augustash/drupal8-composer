<?php

namespace Drupal\exo_modal;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\RenderableInterface;

/**
 * Defines an object which can be rendered by the Render API.
 */
interface ExoModalInterface extends AttachmentsInterface, RenderableInterface, RefinableCacheableDependencyInterface {

  /**
   * The unique id of this instance.
   *
   * @return string
   *   A unique id.
   */
  public function getId();

  /**
   * Set the trigger text.
   *
   * @param string $text
   *   The text of the trigger.
   * @param string $icon_id
   *   The icon of the trigger.
   * @param bool $icon_only
   *   If TRUE will show the icon only.
   *
   * @return $this
   */
  public function setTrigger($text, $icon_id = NULL, $icon_only = NULL);

  /**
   * Set the trigger text.
   *
   * @param string $text
   *   The trigger text.
   *
   * @return $this
   */
  public function setTriggerText($text);

  /**
   * Set the trigger icon.
   *
   * @param string $icon_id
   *   The trigger icon id.
   *
   * @return $this
   */
  public function setTriggerIcon($icon_id);

  /**
   * Set the trigger icon.
   *
   * @param bool $icon_only
   *   If TRUE will show the icon only.
   *
   * @return $this
   */
  public function setTriggerIconOnly($icon_only = TRUE);

  /**
   * Get the trigger.
   *
   * @return \Drupal\exo_icon\ExoIcon
   *   The trigger object.
   */
  public function getTrigger();

  /**
   * Get the trigger for rendering.
   *
   * @return array
   *   An associative array suitable for a render array.
   */
  public function getTriggerAsRenderable();

  /**
   * Set modal.
   *
   * @param mixed $modal
   *   An array suitable for a render array.
   *
   * @return $this
   */
  public function setContent($modal);

  /**
   * Get the modal.
   *
   * @return mixed
   *   An associative array suitable for a render array.
   */
  public function getContent();

  /**
   * Gets the settings.
   *
   * @return array
   *   The array of settings.
   */
  public function getSettings();

  /**
   * Gets data from this settings object.
   *
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *   If no key is specified, then the entire data array is returned.
   *
   * @return mixed
   *   The data that was requested.
   */
  public function getSetting($key = '');

  /**
   * Sets the settings.
   *
   * @return $this
   */
  public function setSetting($key, $value);

  /**
   * Sets a modal settings.
   *
   * @return $this
   */
  public function setModalSetting($key, $value);

  /**
   * Sets the settings.
   *
   * @return $this
   */
  public function setSettings($values);

  /**
   * Get the modal for rendering.
   *
   * @return array
   *   An associative array suitable for a render array.
   */
  public function getContentAsRenderable();

  /**
   * Set the trigger fallback URL.
   *
   * @param string $url
   *   The valid URL of the trigger. This will be used if javascript is
   *   disabled.
   *
   * @return $this
   */
  public function setTriggerUrl($url);

  /**
   * Adds classes or merges them on to array of existing CSS classes.
   *
   * @param string|array ...
   *   CSS classes to add to the class attribute array.
   *
   * @return $this
   */
  public function addTriggerClass($classes);

  /**
   * Removes argument values from array of existing CSS classes.
   *
   * @param string|array ...
   *   CSS classes to remove from the class attribute array.
   *
   * @return $this
   */
  public function removeTriggerClass();

  /**
   * Sets the values for all attributes.
   *
   * @param array $attributes
   *   An array of attributes, keyed by attribute name.
   */
  public function setTriggerAttributes(array $attributes);

  /**
   * Sets values for an attribute key.
   *
   * @param string $attribute
   *   Name of the attribute.
   * @param string|array $value
   *   Value(s) to set for the given attribute key.
   *
   * @return $this
   */
  public function setTriggerAttribute($attribute, $value);

  /**
   * Gets the values for all attributes.
   *
   * @return array
   *   An array of set attribute values, keyed by attribute name.
   */
  public function getTriggerAttributes();

  /**
   * Adds classes or merges them on to array of existing CSS classes.
   *
   * @param string|array ...
   *   CSS classes to add to the class attribute array.
   *
   * @return $this
   */
  public function addModalClass($classes);

  /**
   * Removes argument values from array of existing CSS classes.
   *
   * @param string|array ...
   *   CSS classes to remove from the class attribute array.
   *
   * @return $this
   */
  public function removeModalClass();

  /**
   * Sets the values for all attributes.
   *
   * @param array $attributes
   *   An array of attributes, keyed by attribute name.
   */
  public function setModalAttributes(array $attributes);

  /**
   * Sets values for an attribute key.
   *
   * @param string $attribute
   *   Name of the attribute.
   * @param string|array $value
   *   Value(s) to set for the given attribute key.
   *
   * @return $this
   */
  public function setModalAttribute($attribute, $value);

  /**
   * Gets the values for all attributes.
   *
   * @return array
   *   An array of set attribute values, keyed by attribute name.
   */
  public function getModalAttributes();

  /**
   * Return trigger as render array.
   *
   * @return array
   *   The trigger render array.
   */
  public function toRenderableTrigger();

  /**
   * Return modal as render array.
   *
   * @return array
   *   The trigger render array.
   */
  public function toRenderableModal();

  /**
   * Returns trigger and modal as render array.
   *
   * @return array
   *   The trigger and modal render array.
   */
  public function toRenderable();

  /**
   * Set modal section content.
   *
   * @param string $group
   *   The group used to seperate section content.
   * @param string $id
   *   Unique ID identifying this panel.
   * @param mixed $render
   *   An array suitable for a render array.
   *
   * @return $this
   */
  public function addSectionContent($group, $id, $render);

  /**
   * Add modal panel content.
   *
   * @param string $group
   *   The section group to add the panel content to.
   * @param string $id
   *   Unique ID identifying this panel.
   * @param mixed $render
   *   An array suitable for a render array.
   * @param array $settings
   *   The settings for the modal. See exo_modal.settings.yml -> panel.
   *
   * @return $this
   */
  public function addPanel($group, $id, $render, array $settings = []);

}
