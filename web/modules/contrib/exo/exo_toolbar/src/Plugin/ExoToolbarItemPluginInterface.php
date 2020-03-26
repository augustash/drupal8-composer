<?php

namespace Drupal\exo_toolbar\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\Entity\ExoToolbarItemInterface;

/**
 * Defines an interface for eXo Toolbar Item plugins.
 */
interface ExoToolbarItemPluginInterface extends ConfigurableInterface, PluginFormInterface, PluginInspectionInterface, CacheableDependencyInterface, DerivativeInspectionInterface {

  /**
   * Returns the user-facing item label.
   *
   * @todo Provide other specific label-related methods in
   *   https://www.drupal.org/node/2025649.
   *
   * @return string
   *   The item label.
   */
  public function label();

  /**
   * Indicates whether this item is dependent.
   *
   * Dependent items require that there are other visible items within the same
   * section. If there are none, the item will not show.
   *
   * @var bool
   */
  public function isDependent();

  /**
   * Indicates whether the item should be shown.
   *
   * This method allows base implementations to add general access restrictions
   * that should apply to all extending item plugins.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param bool $return_as_object
   *   (optional) Defaults to FALSE.
   *
   * @return bool|\Drupal\Core\Access\AccessResultInterface
   *   The access result. Returns a boolean if $return_as_object is FALSE (this
   *   is the default) and otherwise an AccessResultInterface object.
   *   When a boolean is returned, the result of AccessInterface::isAllowed() is
   *   returned, i.e. TRUE means access is explicitly allowed, FALSE means
   *   access is either explicitly forbidden or "no opinion".
   *
   * @see \Drupal\item\ItemAccessControlHandler
   */
  public function access(AccountInterface $account, $return_as_object = FALSE);

  /**
   * Set the eXo toolbar item.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $item
   *   An eXo toolbar item.
   *
   * @return $this
   */
  public function setItem(ExoToolbarItemInterface $item);

  /**
   * Get the eXo toolbar item this plugin instance belongs to.
   *
   * @return \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface
   *   The eXo toolbar item this plugin instance belongs to.
   */
  public function getItem();

  /**
   * Builds and returns the renderable array for this item plugin.
   *
   * If a item should not be rendered because it has no content, then this
   * method must also ensure to return no content: it must then only return an
   * empty array, or an empty array with #cache set (with cacheability metadata
   * indicating the circumstances for it being empty).
   *
   * @param bool $preview
   *   Display preview instead of full render.
   *
   * @return array
   *   A renderable array representing the content of the item.
   *
   * @see \Drupal\item\ItemViewBuilder
   */
  public function build($preview = FALSE);

  /**
   * Sets a particular value in the item settings.
   *
   * @param string $key
   *   The key of PluginBase::$configuration to set.
   * @param mixed $value
   *   The value to set for the provided key.
   *
   * @todo This doesn't belong here. Move this into a new base class in
   *   https://www.drupal.org/node/1764380.
   * @todo This does not set a value in \Drupal::config(), so the name is confusing.
   *
   * @see \Drupal\Component\Plugin\PluginBase::$configuration
   */
  public function setConfigurationValue($key, $value);

  /**
   * Returns the configuration form elements specific to this item plugin.
   *
   * Items that need to add form elements to the normal item configuration
   * form should implement this method.
   *
   * @param array $form
   *   The form definition array for the item configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The renderable form array representing the entire configuration form.
   */
  public function itemForm(array $form, FormStateInterface $form_state);

  /**
   * Adds item type-specific validation for the item form.
   *
   * Note that this method takes the form structure and form state for the full
   * item configuration form as arguments, not just the elements defined in
   * ExoToolbarItemPluginInterface::itemForm().
   *
   * @param array $form
   *   The form definition array for the full item configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface::itemForm()
   * @see \Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface::itemSubmit()
   */
  public function itemValidate(array $form, FormStateInterface $form_state);

  /**
   * Adds item type-specific submission handling for the item form.
   *
   * Note that this method takes the form structure and form state for the full
   * item configuration form as arguments, not just the elements defined in
   * ExoToolbarItemPluginInterface::itemForm().
   *
   * @param array $form
   *   The form definition array for the full item configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface::itemForm()
   * @see \Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface::itemValidate()
   */
  public function itemSubmit(array $form, FormStateInterface $form_state);

  /**
   * Provides an array of information to build a list of operation links.
   *
   * @param \Drupal\exo_toolbar\Entity\ExoToolbarItemInterface $item
   *   The eXo toolbar item entity.
   *
   * @return array
   *   An associative array of operation link data for this list, keyed by
   *   operation name, containing the following key-value pairs:
   *   - title: The localized title of the operation.
   *   - url: An instance of \Drupal\Core\Url for the operation URL.
   *   - weight: The weight of this operation.
   */
  public function getOperations(ExoToolbarItemInterface $item);

  /**
   * Suggests a machine name to identify an instance of this item.
   *
   * The item plugin need not verify that the machine name is at all unique. It
   * is only responsible for providing a baseline suggestion; calling code is
   * responsible for ensuring whatever uniqueness is required for the use case.
   *
   * @return string
   *   The suggested machine name.
   */
  public function getMachineNameSuggestion();

  /**
   * Sets the transliteration service.
   *
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function setTransliteration(TransliterationInterface $transliteration);

  /**
   * Allows the region render array to be altered.
   *
   * @param array $settings
   *   The settings array passed to drupalSettings.
   * @param \Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface $region
   *   A region plugin.
   */
  public function alterRegionJsSettings(array &$settings, ExoToolbarRegionPluginInterface $region);

  /**
   * Allows the region render array to be altered.
   *
   * @param array $element
   *   A render array.
   * @param \Drupal\exo_toolbar\Plugin\ExoToolbarRegionPluginInterface $region
   *   A region plugin.
   */
  public function alterRegionElement(array &$element, ExoToolbarRegionPluginInterface $region);

  /**
   * Allows the section render array to be altered.
   *
   * @param array $element
   *   A render array.
   * @param array $context
   *   An array of contextual information including items, toolbar and section.
   */
  public function alterSectionElement(array &$element, array $context);

}
