<?php

namespace Drupal\exo_toolbar\Plugin\ExoToolbarItem;

use Drupal\exo_toolbar\Plugin\ExoToolbarItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginLinkTrait;
use Drupal\Core\Session\AccountInterface;

/**
 * Plugin implementation of the 'link' eXo toolbar item.
 *
 * @ExoToolbarItem(
 *   id = "link",
 *   admin_label = @Translation("Link"),
 *   category = @Translation("Common"),
 * )
 */
class Link extends ExoToolbarItemBase {
  use ExoToolbarItemPluginLinkTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'url' => '',
      'target' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['label']['#title'] = $this->t('Link Title');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function itemForm(array $form, FormStateInterface $form_state) {
    $form = parent::itemForm($form, $form_state);
    $form['icon']['#required'] = TRUE;
    $form['url'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Url'),
      '#description' => $this->t('Start typing the title of a piece of content to select it. You can also enter an internal path such as %add-node or an external URL such as %url. Enter %front to link to the front page.',
        [
          '%front' => '<front>',
          '%add-node' => '/node/add',
          '%url' => 'http://ashenrayne.com',
        ]
      ),
      '#default_value' => $this->configuration['url'] ? static::getUriAsDisplayableString($this->configuration['url']) : NULL,
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#target_type' => 'node',
      '#data-autocomplete-first-character-blacklist' => '/#?',
      '#process_default_value' => FALSE,
      '#element_validate' => [[get_class($this), 'validateUriElement']],
    ];
    $form['target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Open link in new window'),
      '#return_value' => '_blank',
      '#default_value' => $this->configuration['target'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function itemSubmit(array $form, FormStateInterface $form_state) {
    parent::itemSubmit($form, $form_state);
    $this->configuration['url'] = $form_state->getValue('url');
    $this->configuration['target'] = $form_state->getValue('target');
  }

  /**
   * {@inheritdoc}
   */
  protected function itemAccess(AccountInterface $account) {
    return $this->uriAccess($this->configuration['url']);
  }

  /**
   * {@inheritdoc}
   */
  protected function elementBuild() {
    $element = parent::elementBuild();
    $element->setUrl($this->configuration['url'])
      ->addClass('exo-toolbar-link')
      ->setAsLink();
    if (!empty($this->configuration['target'])) {
      $element->setAttribute('target', '_blank');
    }
    return $element;
  }

  /**
   * Form element validation handler for the 'uri' element.
   *
   * Disallows saving inaccessible or untrusted URLs.
   */
  public static function validateUriElement($element, FormStateInterface $form_state, $form) {
    $uri = static::getUserEnteredStringAsUri($element['#value']);
    $form_state->setValueForElement($element, $uri);

    // If getUserEnteredStringAsUri() mapped the entered value to a 'internal:'
    // URI , ensure the raw value begins with '/', '?' or '#'.
    // @todo '<front>' is valid input for BC reasons, may be removed by
    //   https://www.drupal.org/node/2421941
    if (
      parse_url($uri, PHP_URL_SCHEME) === 'internal'
      && !in_array($element['#value'][0], ['/', '?', '#'], TRUE)
      && substr($element['#value'], 0, 7) !== '<front>'
    ) {
      $form_state->setError($element, t('Manually entered paths should start with /, ? or #.'));
      return;
    }
  }

  /**
   * Gets the URI without the 'internal:' or 'entity:' scheme.
   *
   * The following two forms of URIs are transformed:
   * - 'entity:' URIs: to entity autocomplete ("label (entity id)") strings;
   * - 'internal:' URIs: the scheme is stripped.
   *
   * This method is the inverse of ::getUserEnteredStringAsUri().
   *
   * @param string $uri
   *   The URI to get the displayable string for.
   *
   * @return string
   *   The URL string.
   *
   * @see static::getUserEnteredStringAsUri()
   */
  protected static function getUriAsDisplayableString($uri) {
    $scheme = parse_url($uri, PHP_URL_SCHEME);

    // By default, the displayable string is the URI.
    $displayable_string = $uri;

    // A different displayable string may be chosen in case of the 'internal:'
    // or 'entity:' built-in schemes.
    if ($scheme === 'internal') {
      $uri_reference = explode(':', $uri, 2)[1];

      // @todo '<front>' is valid input for BC reasons, may be removed by
      //   https://www.drupal.org/node/2421941
      $path = parse_url($uri, PHP_URL_PATH);
      if ($path === '/') {
        $uri_reference = '<front>' . substr($uri_reference, 1);
      }

      $displayable_string = $uri_reference;
    }
    elseif ($scheme === 'entity') {
      list($entity_type, $entity_id) = explode('/', substr($uri, 7), 2);
      // Show the 'entity:' URI as the entity autocomplete would.
      $entity_type_manager = \Drupal::entityTypeManager();
      if ($entity_type_manager->getDefinition($entity_type, FALSE) && ($entity = $entity_type_manager->getStorage($entity_type)->load($entity_id))) {
        $displayable_string = EntityAutocomplete::getEntityLabels([$entity]);
      }
    }

    return $displayable_string;
  }

  /**
   * Gets the user-entered string as a URI.
   *
   * The following two forms of input are mapped to URIs:
   * - entity autocomplete ("label (entity id)") strings: to 'entity:' URIs;
   * - strings without a detectable scheme: to 'internal:' URIs.
   *
   * This method is the inverse of ::getUriAsDisplayableString().
   *
   * @param string $string
   *   The user-entered string.
   *
   * @return string
   *   The URI, if a non-empty $uri was passed.
   *
   * @see static::getUriAsDisplayableString()
   */
  protected static function getUserEnteredStringAsUri($string) {
    // By default, assume the entered string is an URI.
    $uri = $string;

    // Detect entity autocomplete string, map to 'entity:' URI.
    $entity_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($string);
    if ($entity_id !== NULL) {
      // @todo Support entity types other than 'node'. Will be fixed in
      // https://www.drupal.org/node/2423093.
      $uri = 'entity:node/' . $entity_id;
    }
    // Detect a schemeless string, map to 'internal:' URI.
    elseif (!empty($string) && parse_url($string, PHP_URL_SCHEME) === NULL) {
      // @todo '<front>' is valid input for BC reasons, may be removed by
      //   https://www.drupal.org/node/2421941
      // - '<front>' -> '/'
      // - '<front>#foo' -> '/#foo'
      if (strpos($string, '<front>') === 0) {
        $string = '/' . substr($string, strlen('<front>'));
      }
      $uri = 'internal:' . $string;
    }

    return $uri;
  }

}
