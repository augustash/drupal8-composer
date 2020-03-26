<?php

namespace Drupal\exo_toolbar;

/**
 * Defines an interface for eXo toolbar element.
 */
interface ExoToolbarElementInterface extends ExoToolbarJsSettingsInterface {

  /**
   * Get the element unique id.
   */
  public function id();

  /**
   * Set the element tag.
   *
   * @param string $tag
   *   The element tag.
   *
   * @return $this
   */
  public function setTag($tag);

  /**
   * Get the element tag.
   *
   * @return string
   *   The element tag or NULL if it is not set.
   */
  public function getTag();

  /**
   * Set the element title.
   *
   * @param string $title
   *   The element title.
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Get the element title.
   *
   * @return string|null
   *   The element title or NULL if it is not set.
   */
  public function getTitle();

  /**
   * Add an element library..
   *
   * @param string $library
   *   The element library.
   *
   * @return $this
   */
  public function addLibrary($library);

  /**
   * Get the element libraries.
   *
   * @return string[]
   *   The element libraries.
   */
  public function getLibraries();

  /**
   * Set the element weight.
   *
   * @param string $weight
   *   The element weight.
   *
   * @return $this
   */
  public function setWeight($weight = 0);

  /**
   * Get the element weight.
   *
   * @return string
   *   The element weight.
   */
  public function getWeight();

  /**
   * Set the element access.
   *
   * @param \Drupal\Core\Access\AccessResult|bool $access
   *   The element access.
   *
   * @return $this
   */
  public function setAccess($access);

  /**
   * Get the element access.
   *
   * @return \Drupal\Core\Access\AccessResult|bool
   *   The element access.
   */
  public function getAccess();

  /**
   * Set the element url.
   *
   * @param Drupal\Core\Url|string $url
   *   The element url.
   *
   * @return $this
   */
  public function setUrl($url);

  /**
   * Get the element url.
   *
   * @return Drupal\Core\Url|null
   *   The element url or NULL if it is not set.
   */
  public function getUrl();

  /**
   * Using the url, set element as a link.
   *
   * @param Drupal\Core\Url|string $url
   *   (optional) The element url.
   *
   * @return $this
   */
  public function setAsLink($url = NULL);

  /**
   * Get icon.
   *
   * @return string
   *   The icon.
   */
  public function getIcon();

  /**
   * Set icon.
   *
   * @param string $icon
   *   The icon.
   *
   * @return $this
   */
  public function setIcon($icon);

  /**
   * Set the icon position.
   *
   * @param string $position
   *   The icon position.
   *
   * @return $this
   */
  public function setIconPosition($position);

  /**
   * Get the icon position.
   *
   * @return string
   *   The icon position.
   */
  public function getIconPosition();

  /**
   * Set the icon size.
   *
   * @param string $size
   *   The icon size. Either small, standard or large.
   *
   * @return $this
   */
  public function setIconSize($size = 'standard');

  /**
   * Get the icon size.
   *
   * @return string
   *   The icon size.
   */
  public function getIconSize();

  /**
   * Set image via file uri.
   *
   * @param string $image_uri
   *   The image uri.
   *
   * @return $this
   */
  public function setImage($image_uri);

  /**
   * Get the image.
   *
   * @return \Drupal\Core\Image\ImageInterface
   *   The image.
   */
  public function getImage();

  /**
   * Check if image is external.
   *
   * @return bool
   *   Returns TRUE if image is external.
   */
  public function imageIsExternal();

  /**
   * Set the image style.
   *
   * @param string $style
   *   The image style.
   *
   * @return $this
   */
  public function setImageStyle($style);

  /**
   * Get the image style.
   *
   * @return string
   *   The image style.
   */
  public function getImageStyle();

  /**
   * Set the image position.
   *
   * @param string $position
   *   The image position.
   *
   * @return $this
   */
  public function setImagePosition($position);

  /**
   * Get the image position.
   *
   * @return string
   *   The image position.
   */
  public function getImagePosition();

  /**
   * Set badge value.
   *
   * @param string $value
   *   The value of the badge.
   *
   * @return $this
   */
  public function setBadge($value);

  /**
   * Get badge value.
   *
   * @return string
   *   The value of the badge.
   */
  public function getBadge();

  /**
   * Set if aside label should be used.
   *
   * @return $this
   */
  public function useAsideLabel($use = TRUE);

  /**
   * Check if aside label should be used.
   *
   * @return bool
   *   Returns TRUE if aside label should be used.
   */
  public function shouldUseAsideLabel();

  /**
   * Get the element aside label.
   *
   * @return array
   *   An array suitable for rendering.
   */
  public function getAsideLabel();

  /**
   * Set badge usage.
   *
   * @param bool $use
   *   If TRUE, badge will be used.
   *
   * @return bool
   *   Return TRUE if badge should be used.
   */
  public function useBadge($use = TRUE);

  /**
   * Set as icon only for both vertical and horizontal positions.
   *
   * @param bool $set
   *   Will set as mark-only if TRUE.
   *
   * @return $this
   */
  public function setMarkOnly($set = TRUE);

  /**
   * Set as icon only for vertical position.
   *
   * @param bool $set
   *   Will set as mark-only if TRUE.
   *
   * @return $this
   */
  public function setVerticalMarkOnly($set = TRUE);

  /**
   * Set as icon only for horizontal position..
   *
   * @param bool $set
   *   Will set as mark-only if TRUE.
   *
   * @return $this
   */
  public function setHorizontalMarkOnly($set = TRUE);

  /**
   * Add a sub element.
   *
   * @param array $values
   *   Optional properties of the element.
   *
   * @return $this
   */
  public function addSubElement(array $values = []);

  /**
   * Get sub elements.
   *
   * @return \Drupal\exo_toolbar\ExoToolbarElement[]
   *   An array of element objects.
   */
  public function getSubElements(array $values = []);

  /**
   * Get the element subset #theme.
   *
   * @return string
   *   The #theme to use when rendering the sub elements.
   */
  public function getSubElementTheme();

  /**
   * Set the element subset #theme.
   *
   * @param string $theme
   *   The #theme to use when rendering the sub elements.
   *
   * @return $this
   */
  public function setSubElementTheme($theme);

  /**
   * Get the element attributes.
   *
   * @return \Drupal\Core\Template\Attribute
   *   The element attributes
   */
  public function getAttributes();

  /**
   * Adds classes or merges them on to array of existing CSS classes.
   *
   * @param string|array ...
   *   CSS classes to add to the class attribute array.
   *
   * @return $this
   */
  public function addClass();

  /**
   * Removes argument values from array of existing CSS classes.
   *
   * @param string|array ...
   *   CSS classes to remove from the class attribute array.
   *
   * @return $this
   */
  public function removeClass();

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
  public function setAttribute($attribute, $value);

  /**
   * Removes an attribute from an Attribute object.
   *
   * @param string|array ...
   *   Attributes to remove from the attribute array.
   *
   * @return $this
   */
  public function removeAttribute();

  /**
   * Get the item attributes.
   *
   * These attributes are used on the parent eXo item.
   *
   * @return \Drupal\Core\Template\Attribute
   *   The element attributes
   */
  public function getItemAttributes();

  /**
   * Get the element cachable metadata.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The element cachable metadata
   */
  public function getCacheableMetadata();

  /**
   * Returns a render array representation of the object.
   *
   * @return mixed[]
   *   A render array.
   */
  public function toRenderable();

}
